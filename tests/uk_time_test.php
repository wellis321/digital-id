<?php
/**
 * Saved times are UTC; everything shown, and every "today", is UK:  php tests/uk_time_test.php
 * Needs a MySQL server (credentials from .env or the environment) and changes nothing. It also reads the code for the usual mistakes.
 */
date_default_timezone_set('Europe/London');
$env = [];
foreach (is_file(dirname(__DIR__) . '/.env') ? file(dirname(__DIR__) . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [] as $l) { $l = trim($l); if ($l === '' || $l[0] === '#' || !str_contains($l, '=')) continue; [$k, $v] = explode('=', $l, 2); $env[trim($k)] = trim($v, " \t\"'"); }
$g = fn(string $k, string $d = '') => (string) (getenv($k) !== false && getenv($k) !== '' ? getenv($k) : ($env[$k] ?? $d));
define('DB_HOST', $g('DB_HOST', '127.0.0.1') . ($g('DB_PORT') !== '' ? ';port=' . $g('DB_PORT') : '')); define('DB_NAME', $g('DB_NAME')); define('DB_USER', $g('DB_USER', 'root')); define('DB_PASS', $g('DB_PASS')); define('DB_CHARSET', 'utf8mb4');
require dirname(__DIR__) . '/shared-auth/src/Database.php';
$bad = [];
$check = static function (bool $ok, string $what) use (&$bad): void { if (!$ok) $bad[] = $what; };
$db = getDbConnection();
$row = $db->query('SELECT NOW() AS n, @@session.time_zone AS z')->fetch(PDO::FETCH_ASSOC);
$check($row['z'] === '+00:00' && abs(db_ts($row['n']) - time()) < 5, 'the database session is not saving UTC');
$check(uk_time('2026-06-14 23:30:00') === '15/06/2026 00:30' && uk_time('2026-12-14 23:30:00') === '14/12/2026 23:30' && uk_time(null) === '', 'a saved time is not shown in UK time');
$check(uk_day_start('2026-06-15') === '2026-06-14 23:00:00' && uk_day_start('2026-12-15') === '2026-12-15 00:00:00', 'the start of a UK day is wrong');
$check(saved_from_uk('2026-06-15T00:30') === '2026-06-14 23:30:00' && saved_from_uk('not a date') === null, 'a typed UK time is not saved as UTC');
$check(sql_today() === "'" . date('Y-m-d') . "'", 'today is wrong');
$db->exec('CREATE TEMPORARY TABLE clock_probe (at TIMESTAMP NULL)');
$db->exec("INSERT INTO clock_probe VALUES ('2026-06-14 23:30:00'), ('2026-12-14 23:30:00')");
$check($db->query('SELECT at FROM clock_probe ORDER BY at')->fetchAll(PDO::FETCH_COLUMN) === ['2026-06-14 23:30:00', '2026-12-14 23:30:00'], 'a saved time does not read back as written, whatever the season');
$cols = 'assigned_at|checked_in_at|checked_out_at|created_at|ended_at|expires_at|issued_at|joined_at|last_login|last_synced_at|last_synced_from_care_core|nfc_token_expires_at|photo_approved_at|qr_token_expires_at|received_at|revoked_at|started_at|synced_at|updated_at|used_at|verification_token_expires_at|verified_at';
$root = dirname(__DIR__);
foreach (explode(',', 'src,public,shared-auth,includes,scripts,config') as $dir) {
    if (!is_dir("$root/$dir")) continue;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$root/$dir", FilesystemIterator::SKIP_DOTS)) as $f) {
        if ($f->getExtension() !== 'php' || $f->getFilename() === 'time.php' || preg_match('~/(vendor|node_modules)/~', $f->getPathname())) continue;
        foreach (file($f->getPathname()) as $n => $line) {
            $why = null;
            if (preg_match('/CURDATE\s*\(|CURRENT_DATE\b(?!\s*\))/i', $line) && !preg_match('/DEFAULT \(?CURRENT_DATE/i', $line)) $why = 'asks the database what today is: use sql_today()';
            elseif (preg_match("/strtotime\([^;]{0,60}(?:\['|->)(?:$cols)\b(?![^;]{0,24}UTC)/", $line)) $why = 'reads a saved time as UK time: use uk_time() or db_ts()';
            elseif (preg_match("/\bDATE(?:_FORMAT)?\(\s*(?:\w+\.)?(?:$cols)\b/", $line)) $why = 'takes a date from a saved time in the database: use uk_time(), or compare with uk_day_start()';
            if ($why) $bad[] = substr($f->getPathname(), strlen($root) + 1) . ':' . ($n + 1) . ' ' . $why;
        }
    }
}
echo $bad ? "FAIL:\n  " . implode("\n  ", $bad) . "\n" : "PASS: saved times UTC, shown in UK time, no date mistakes found in the code\n";
exit($bad ? 1 : 0);
