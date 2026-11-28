<?php
/**
 * The database connection keeps UK time, as the app does:  php tests/uk_time_test.php
 * Needs a MySQL server (credentials from .env or the environment). It reads nothing and changes nothing.
 */
$env = [];
foreach (is_file(dirname(__DIR__) . '/.env') ? file(dirname(__DIR__) . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [] as $l) { $l = trim($l); if ($l === '' || $l[0] === '#' || !str_contains($l, '=')) continue; [$k, $v] = explode('=', $l, 2); $env[trim($k)] = trim($v, " \t\"'"); }
$g = fn(string $k, string $d = '') => (string) (getenv($k) !== false && getenv($k) !== '' ? getenv($k) : ($env[$k] ?? $d));
date_default_timezone_set('Europe/London');
define('DB_HOST', $g('DB_HOST', '127.0.0.1') . ($g('DB_PORT') !== '' ? ';port=' . $g('DB_PORT') : '')); define('DB_NAME', $g('DB_NAME')); define('DB_USER', $g('DB_USER', 'root')); define('DB_PASS', $g('DB_PASS')); define('DB_CHARSET', 'utf8mb4');
require dirname(__DIR__) . '/shared-auth/src/Database.php';
$db = getDbConnection();
$row = $db->query('SELECT NOW() AS n, CURDATE() AS d')->fetch(PDO::FETCH_ASSOC);
$good = abs(strtotime($row['n']) - time()) < 5 && $row['d'] === date('Y-m-d');
// A summer instant reads as British Summer Time (or, where the server has no named zones, at today's offset).
$summer = $db->query('SELECT FROM_UNIXTIME(' . strtotime('2026-06-15 00:30:00 Europe/London') . ')')->fetchColumn();
$named = $db->query('SELECT @@session.time_zone')->fetchColumn() === 'Europe/London';
$good = $good && (!$named || str_starts_with((string) $summer, '2026-06-15 00:30'));
echo ($good ? 'PASS' : 'FAIL') . ": database says {$row['n']}, app says " . date('Y-m-d H:i:s') . ($named ? '' : ' (this server has no named time zones: today\'s offset is used)') . "\n";
exit($good ? 0 : 1);
