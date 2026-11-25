<?php
/**
 * Rebuild the public demo instance: drop every table, recreate the schema from sql/demo_schema.sql and
 * seed a fictional organisation. Run nightly from cron:
 *
 *   php scripts/demo-reset.php
 *
 * Refuses to run unless DEMO_MODE=true AND the database name contains "demo", so it can never wipe a
 * real customer's system by mistake.
 */
if (php_sapi_name() !== 'cli') {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__) . '/config/config.php';

if (!DEMO_MODE || stripos(DB_NAME, 'demo') === false) {
    fwrite(STDERR, "Refusing to reset: DEMO_MODE must be true and the database name must contain 'demo' (database: " . DB_NAME . ").\n");
    exit(1);
}

$db = getDbConnection();
$db->exec(file_get_contents(ROOT_PATH . '/sql/demo_schema.sql'));
echo "Schema rebuilt.\n";

foreach ([[1, 'superadmin'], [2, 'organisation_admin'], [3, 'staff'], [6, 'admin']] as [$id, $name]) {
    $db->prepare("INSERT INTO roles (id, name, description) VALUES (?, ?, ?)")->execute([$id, $name, ucfirst(str_replace('_', ' ', $name))]);
}
$roleId = array_column($db->query("SELECT id, name FROM roles")->fetchAll(), 'id', 'name');

// Linked mode: this demo is fed by the Care Core demo (core.findcarescotland.com), so its staff ARE Care Core's staff. Needs DEMO_CARE_CORE_URL and
// DEMO_LINK_TOKEN (the same token the Care Core demo seeds as an API key) in .env. Without them the standalone Glen Ross demo is built as before.
$linkUrl = rtrim((string) getenv('DEMO_CARE_CORE_URL'), '/');
$linkToken = (string) getenv('DEMO_LINK_TOKEN');
$linked = $linkUrl !== '' && preg_match('/^cc_[0-9a-f]{8}_[0-9a-f]{48}$/', $linkToken) === 1;

// One fictional organisation. The ".test" domain can never be a real email address.
$orgName = $linked ? 'Loch View Care (Demo)' : 'Glen Ross Care (Demo)';
$orgDomain = $linked ? 'demo.lochview.test' : 'demo.glenross.test';
$db->prepare("INSERT INTO organisations (id, name, domain, seats_allocated, seats_used, reference_prefix, main_contact_name, service_types, geographic_coverage)
              VALUES (1, ?, ?, 50, 0, 'LVC', 'Demo Administrator', 'Care at home, Supported living', 'Highland')")->execute([$orgName, $orgDomain]);

$hash = password_hash(DEMO_PASSWORD, PASSWORD_DEFAULT);
// [first, last, email-local, title, role]
$people = [
    ['Fiona', 'Maclean', 'admin', 'Registered Manager', 'organisation_admin'],
    ['Callum', 'Reid', 'staff', 'Senior Support Worker', 'staff'],
    ['Isla', 'Grant', 'isla.grant', 'Support Worker', 'staff'],
    ['Rory', 'Fraser', 'rory.fraser', 'Support Worker', 'staff'],
    ['Morven', 'Stewart', 'morven.stewart', 'Care Assistant', 'staff'],
    ['Ewan', 'Mackay', 'ewan.mackay', 'Care Assistant', 'staff'],
    ['Kirsty', 'Munro', 'kirsty.munro', 'Team Leader', 'staff'],
    ['Andrew', 'Sinclair', 'andrew.sinclair', 'Support Worker', 'staff'],
    ['Catriona', 'Rose', 'catriona.rose', 'Care Assistant', 'staff'],
    ['Hamish', 'Cameron', 'hamish.cameron', 'Night Support Worker', 'staff'],
    ['Eilidh', 'Ross', 'eilidh.ross', 'Support Worker', 'staff'],
    ['Struan', 'Gunn', 'struan.gunn', 'Care Assistant', 'staff'],
];

$employeeIds = [];
if ($linked) {
    // Staff come from Care Core, exactly as for a real customer: connect, register for events, then import everyone.
    CareCore::ensureSchema();
    CareCore::set(1, 'care_core_url', $linkUrl); CareCore::set(1, 'care_core_api_key', $linkToken);
    $client = new CareCoreClient($linkUrl, $linkToken);
    $token = bin2hex(random_bytes(20));
    $appUrl = rtrim((string) APP_URL, '/');
    if (!str_starts_with($appUrl, 'https://') && !preg_match('~^http://localhost[:/]~', $appUrl . '/')) { fwrite(STDERR, "Linked demo needs APP_URL to be the public https address.\n"); exit(1); }
    $hook = $client->createWebhook(CareCore::webhookUrl($token), ['person.created', 'person.updated', 'employment.started', 'employment.changed', 'employment.ended', 'learning.recorded']);
    CareCore::set(1, 'care_core_webhook_token', $token); CareCore::set(1, 'care_core_webhook_id', $hook['id']); CareCore::set(1, 'care_core_webhook_secret', $hook['secret']);
    $r = CareCoreSync::syncOrganisation(1, true, $client);
    echo "Synced from Care Core: {$r['created']} people, {$r['photos']} photos, " . count($r['errors']) . " problems.\n";
    // The demo logins are the same two people as in Care Core: the administrator and one member of staff.
    foreach ([['admin', 'organisation_admin'], ['staff', 'staff']] as [$local, $role]) {
        $u = $db->prepare("SELECT id FROM users WHERE email = ?"); $u->execute([$local . '@demo.lochview.test']); $uid = (int) $u->fetchColumn();
        if (!$uid) { fwrite(STDERR, "Care Core has no $local@demo.lochview.test.\n"); exit(1); }
        $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, $uid]);
        $db->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$uid, $roleId[$role]]);
    }
    // Sign in with Care Core on the demonstration: the Care Core demo registers this demo as a client every night, with credentials worked out from DEMO_SSO_SECRET (the same value is in
    // each demo's .env). The demo logins above are the same people as in Care Core, so they match by email.
    $ssoMaster = trim((string) getenv('DEMO_SSO_SECRET'));
    if (strlen($ssoMaster) >= 32) { $sr = CareCoreSignIn::save(1, 'cc_demo_id', hash_hmac('sha256', 'id', $ssoMaster), true, false); echo $sr === true ? "Sign in with Care Core is on for the demonstration.\n" : "Sign in with Care Core was not set up: $sr\n"; }
    foreach ($db->query("SELECT id FROM employees WHERE organisation_id = 1 AND is_active = 1 ORDER BY id")->fetchAll(PDO::FETCH_COLUMN) as $eid) { $employeeIds[] = (int) $eid; DigitalID::createIdCard((int) $eid); }
    $people = $employeeIds;
} else {
foreach ($people as $i => [$first, $last, $local, $title, $role]) {
    $email = $local . '@demo.glenross.test';
    $db->prepare("INSERT INTO users (organisation_id, email, password_hash, first_name, last_name, is_active, email_verified)
                  VALUES (1, ?, ?, ?, ?, 1, 1)")->execute([$email, $hash, $first, $last]);
    $userId = (int) $db->lastInsertId();
    $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$userId, $roleId[$role]]);
    $ref = sprintf('GRC%03d', $i + 1);
    $db->prepare("INSERT INTO employees (user_id, organisation_id, employee_reference, display_reference, employee_number, is_active, photo_approval_status)
                  VALUES (?, 1, ?, ?, ?, 1, 'none')")->execute([$userId, $ref, $ref, $ref]);
    $employeeIds[$i] = (int) $db->lastInsertId();
    DigitalID::createIdCard($employeeIds[$i]);
}
}
$db->exec("UPDATE organisations SET seats_used = " . count($people) . " WHERE id = 1");

// Structure
$db->exec("INSERT INTO organisational_units (organisation_id, name, unit_type, description, display_order, is_active) VALUES
    (1, 'Care at Home', 'Service', 'Visits to people in their own homes', 1, 1),
    (1, 'Supported Living', 'Service', 'Shared and individual tenancies', 2, 1)");

// A month of verification history: mostly successful, a few that show how failures are recorded.
$cards = $db->query("SELECT id, employee_id FROM digital_id_cards")->fetchAll(PDO::FETCH_ASSOC);
$devices = ['iPhone, Safari', 'Android, Chrome', 'Windows, Edge', 'iPad, Safari'];
$stmt = $db->prepare("INSERT INTO verification_logs (id_card_id, employee_id, verification_type, verified_by_ip, verified_by_device, verification_result, verified_at, notes)
                      VALUES (?, ?, ?, '203.0.113.10', ?, ?, ?, ?)");
mt_srand(2026);
for ($n = 0; $n < 60; $n++) {
    $card = $cards[mt_rand(0, count($cards) - 1)];
    $r = mt_rand(1, 20);
    $result = $r === 1 ? 'expired' : ($r === 2 ? 'failed' : 'success');
    $stmt->execute([$card['id'], $card['employee_id'], ['qr', 'qr', 'qr', 'visual', 'nfc'][mt_rand(0, 4)], $devices[mt_rand(0, 3)], $result,
        date('Y-m-d H:i:s', time() - mt_rand(3600, 30 * 86400)), $result === 'success' ? null : 'Demo record']);
}

// A completed fire drill, to show check-in sessions and attendance.
$adminUserId = (int) $db->query("SELECT id FROM users WHERE email = 'admin@" . $orgDomain . "'")->fetchColumn();
$started = date('Y-m-d H:i:s', strtotime('-2 days 10:30'));
$db->prepare("INSERT INTO check_in_sessions (organisation_id, session_name, session_type, started_by, started_at, ended_at, location_name)
              VALUES (1, 'Fire drill: Inverness office', 'fire_drill', ?, ?, ?, 'Inverness office')")
   ->execute([$adminUserId, $started, date('Y-m-d H:i:s', strtotime($started . ' +12 minutes'))]);
$sessionId = (int) $db->lastInsertId();
foreach (array_slice($employeeIds, 0, 9) as $k => $employeeId) {
    $db->prepare("INSERT INTO check_ins (employee_id, check_in_type, location_name, checked_in_at, session_id, check_in_method)
                  VALUES (?, 'fire_drill', 'Inverness office', ?, ?, 'qr_scan')")
       ->execute([$employeeId, date('Y-m-d H:i:s', strtotime($started . ' +' . (2 + $k) . ' minutes')), $sessionId]);
}

echo "Seeded " . count($people) . " staff, " . count($cards) . " ID cards, 60 verifications and 1 fire drill.\n";
echo "Sign in as admin@$orgDomain or staff@$orgDomain.\n";
