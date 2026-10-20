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

// One fictional organisation. The ".test" domain can never be a real email address.
$db->prepare("INSERT INTO organisations (id, name, domain, seats_allocated, seats_used, reference_prefix, main_contact_name, service_types, geographic_coverage)
              VALUES (1, 'Glen Ross Care (Demo)', 'demo.glenross.test', 50, 0, 'GRC', 'Demo Administrator', 'Care at home, Supported living', 'Highland')")->execute();

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
$adminUserId = (int) $db->query("SELECT id FROM users WHERE email = 'admin@demo.glenross.test'")->fetchColumn();
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
echo "Sign in as admin@demo.glenross.test or staff@demo.glenross.test.\n";
