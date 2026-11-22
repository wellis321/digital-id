<?php
/**
 * Demo-mode helpers. DEMO_MODE=true marks a public sample-data instance: it shows a banner and the demo
 * sign-in details, never sends email, and refuses the actions that could reach outside the demo (sign-up
 * forms, integrations, imports). scripts/demo-reset.php rebuilds its data every night.
 */

const DEMO_PASSWORD = 'DemoPass2026!';

/** Is this demo fed by the Care Core demo? (its single organisation is then Loch View Care) */
function demo_linked(): bool
{
    try { return (string) getDbConnection()->query('SELECT domain FROM organisations WHERE id = 1')->fetchColumn() === 'demo.lochview.test'; }
    catch (Throwable $e) { return false; }
}

/** The accounts the seed creates, for the sign-in page. [role label, email] */
function demo_accounts(): array
{
    $d = demo_linked() ? 'demo.lochview.test' : 'demo.glenross.test';
    return [
        ['Organisation administrator', 'admin@' . $d],
        ['Member of staff', 'staff@' . $d],
    ];
}

/** Stop a state-changing request on a demo instance, with a friendly page. */
function demo_block_posts(): void
{
    if (!DEMO_MODE || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }
    http_response_code(403);
    $pageTitle = 'Not available in the demo';
    include INCLUDES_PATH . '/header.php';
    ?>
    <div class="card">
        <h1>Not available in the demo</h1>
        <p>This is a demonstration with sample data, so it does not send email, create new accounts or connect to other systems.</p>
        <p>To use Digital ID with your own organisation, <a href="https://findcarescotland.com/software/enquire?product=digital-id">get in touch</a>.</p>
        <p><a href="<?php echo htmlspecialchars(url('index.php')); ?>">Back to the home page</a></p>
    </div>
    <?php
    include INCLUDES_PATH . '/footer.php';
    exit;
}

function demo_banner_html(): string
{
    if (!DEMO_MODE) {
        return '';
    }
    $sw = 'https://findcarescotland.com/software';
    $switch = '';
    if (class_exists('Auth') && Auth::isLoggedIn()) {
        $isAdmin = class_exists('RBAC') && RBAC::isAdmin();
        $other = $isAdmin ? 'staff' : 'admin';
        $switch = ' <form method="post" action="' . htmlspecialchars(url('demo-enter.php')) . '" class="demo-switch">' . CSRF::tokenField() . demo_entry_fields()
            . '<span>You are viewing as ' . ($isAdmin ? 'an administrator' : 'a member of staff') . '.</span> <button class="demo-linkbtn" type="submit" name="role" value="' . $other . '">See it as ' . ($isAdmin ? 'a member of staff' : 'an administrator') . '</button></form>';
    }
    $html = '<div class="demo-banner" role="region" aria-label="Demo notice"><strong>Demo</strong> '
        . '<span class="demo-banner__text">All names and records here are made up, and the data resets every night. Please do not enter real personal information.</span> <strong><a class="demo-banner__back" href="https://findcarescotland.com/software">&larr; Back to Find Care Scotland software</a></strong> &middot; <a href="https://findcarescotland.com/software">All software</a></div>' ;
    return substr($html, 0, -strlen('</div>')) . $switch . '</div>';
}

/** "Other products" line for the demo footer: links to the showcase pages on Find Care Scotland. */
function demo_other_products_html(string $current): string
{
    if (!DEMO_MODE) {
        return '';
    }
    $products = [
        'digital-id' => 'Digital ID',
        'contracts' => 'Contracts',
        'care-core' => 'Care Core',
        'strategic-plan' => 'Strategic plans',
        'simple-data-cleaner' => 'Simple Data Cleaner',
    ];
    $links = [];
    foreach ($products as $slug => $label) {
        if ($slug !== $current) {
            $links[] = '<a href="https://findcarescotland.com/software/' . $slug . '">' . $label . '</a>';
        }
    }
    return '<p class="demo-other-products">Other products: ' . implode(' · ', $links) . '</p>';
}

// ── One-click entry to the demonstration, and the guard that keeps robots out of it ───────────────────────────────────────────────────
// The demo has shared accounts, so a button that signs anyone in must not be something a script can press in a loop: a form-shown token that
// must be at least two seconds old, a hidden field a person never fills in, a refusal of obvious automation by user agent, and limits per
// address and for the whole demo. Only exists in demo mode (public/demo-enter.php).
const DEMO_ENTRY_MIN_AGE = 2;
const DEMO_ENTRY_MAX_AGE = 7200;
const DEMO_ENTRY_ROLES = ['admin' => 'an administrator', 'staff' => 'a member of staff'];

function demo_entry_key(): string
{
    return (string) (getenv('APP_KEY') ?: (defined('DB_PASS') ? DB_PASS : '') ?: 'digital-id-demo');
}

function demo_entry_token(): string
{
    $t = (string) time();
    return $t . '.' . hash_hmac('sha256', 'demo-entry|' . $t, demo_entry_key());
}

function demo_entry_token_ok(string $token): bool
{
    if (!preg_match('/^(\d{9,11})\.([a-f0-9]{64})$/', $token, $m)) return false;
    if (!hash_equals(hash_hmac('sha256', 'demo-entry|' . $m[1], demo_entry_key()), $m[2])) return false;
    $age = time() - (int) $m[1];
    return $age >= DEMO_ENTRY_MIN_AGE && $age <= DEMO_ENTRY_MAX_AGE;
}

function demo_entry_looks_automated(): bool
{
    $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
    if (strlen($ua) < 20) return true;
    return (bool) preg_match('/bot|crawl|spider|slurp|preview|monitor|curl|wget|python|headless|lighthouse|go-http|java\/|scrapy|httpclient|okhttp|axios|node-fetch|libwww|GPT|Claude|Perplexity/i', $ua);
}

/** The hidden fields every demo-entry form carries (add CSRF::tokenField() beside them). */
function demo_entry_fields(): string
{
    return '<input type="hidden" name="ft" value="' . htmlspecialchars(demo_entry_token()) . '">'
        . '<div class="hp" aria-hidden="true"><label>Leave this empty <input type="text" name="website" value="" tabindex="-1" autocomplete="off"></label></div>';
}

/** The shared demo account for a role ('admin' or 'staff'), as a users.id, or null. */
function demo_entry_user_id(string $role): ?int
{
    if (!isset(DEMO_ENTRY_ROLES[$role])) return null;
    foreach (demo_accounts() as [$label, $email]) {
        if (str_starts_with($email, $role . '@')) {
            $st = getDbConnection()->prepare('SELECT id FROM users WHERE email = ? AND is_active = 1 LIMIT 1');
            $st->execute([$email]);
            $id = $st->fetchColumn();
            return $id ? (int) $id : null;
        }
    }
    return null;
}

/** "Three things to try", for the signed-in demo user. */
function demo_tour_html(bool $isAdmin): string
{
    if (!DEMO_MODE) return '';
    if ($isAdmin) {
        $steps = [
            '<strong>See who is on the card list.</strong> Open <a href="' . url('admin/employees.php') . '">Employees</a>: every member of staff, with their reference and whether their card is current.',
            '<strong>Run a check-in.</strong> Open <a href="' . url('admin/check-in-sessions.php') . '">Check-in sessions</a> to start one for a fire drill or a training day. Staff check in with a QR code and attendance is recorded automatically.',
            '<strong>Look at the audit trail.</strong> Every time a card is checked, the time and result are logged. See them in <a href="' . url('admin/verification-logs.php') . '">Verification logs</a>.',
        ];
    } else {
        $steps = [
            '<strong>Look at your card.</strong> This page is your Digital ID card, with your photo, organisation and a QR code.',
            '<strong>Notice the QR code.</strong> It expires after five minutes, so a screenshot is no use to anyone later.',
            '<strong>Check it as a visitor would.</strong> Open <a href="' . url('verify.php') . '">the verification page</a>. Families, service users and partner organisations use it, with no account.',
        ];
    }
    return '<details class="demo-tour" open><summary><strong>New here? Three things to try</strong> <span class="demo-muted">(this is a demonstration; it resets every night)</span></summary><ol><li>' . implode('</li><li>', $steps) . '</li></ol></details>';
}

