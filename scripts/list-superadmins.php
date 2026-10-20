<?php
/**
 * List every user holding the superadmin role (read-only, command line only), with the last time the
 * account row changed (a password reset updates it). Run after any security incident, or periodically,
 * to confirm each account is expected.
 *
 *   php scripts/list-superadmins.php
 */
if (php_sapi_name() !== 'cli') {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__) . '/config/config.php';

$rows = getDbConnection()->query("
    SELECT u.id, u.email, u.is_active, u.created_at, u.updated_at, u.last_login
    FROM users u
    JOIN user_roles ur ON ur.user_id = u.id
    JOIN roles r ON r.id = ur.role_id
    WHERE r.name = 'superadmin'
    ORDER BY u.created_at
")->fetchAll(PDO::FETCH_ASSOC);

echo count($rows) . " superadmin(s)\n";
foreach ($rows as $r) {
    echo "  id {$r['id']}  {$r['email']}  active=" . (int) $r['is_active']
        . "  created {$r['created_at']}  row last changed {$r['updated_at']}  last login " . ($r['last_login'] ?? 'never') . "\n";
}
