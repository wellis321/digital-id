<?php
/**
 * One-off, idempotent: link the two public demos. Creates a random API key value and writes it as DEMO_LINK_TOKEN into BOTH servers' .env files
 * (Care Core's demo seeds it as an API key; this demo uses it to read Care Core), and DEMO_CARE_CORE_URL here. Never prints the value.
 *   php scripts/link-demo-env.php /path/to/care-core/.env [https://core.example.org]
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$mine = dirname(__DIR__) . '/.env';
$theirs = $argv[1] ?? '';
$url = rtrim($argv[2] ?? 'https://core.findcarescotland.com', '/');
if (!is_file($mine) || !is_file($theirs)) { fwrite(STDERR, "Both .env files must exist.\n"); exit(1); }
$has = static fn(string $f, string $k): bool => (bool) preg_match('/^' . preg_quote($k, '/') . '=\S+/m', (string) file_get_contents($f));
if ($has($mine, 'DEMO_LINK_TOKEN') && $has($theirs, 'DEMO_LINK_TOKEN')) { echo "Already linked.\n"; exit(0); }
$token = 'cc_' . bin2hex(random_bytes(4)) . '_' . bin2hex(random_bytes(24));
foreach ([$mine, $theirs] as $f) {
    $text = preg_replace('/^DEMO_LINK_TOKEN=.*\R?/m', '', (string) file_get_contents($f));
    file_put_contents($f, rtrim($text) . "\nDEMO_LINK_TOKEN=$token\n", LOCK_EX);
}
$text = preg_replace('/^DEMO_CARE_CORE_URL=.*\R?/m', '', (string) file_get_contents($mine));
file_put_contents($mine, rtrim($text) . "\nDEMO_CARE_CORE_URL=$url\n", LOCK_EX);
echo "Linked: DEMO_LINK_TOKEN written to both .env files; DEMO_CARE_CORE_URL=$url set here.\n";
