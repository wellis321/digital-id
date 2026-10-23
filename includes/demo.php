<?php
/**
 * Demo-mode helpers. DEMO_MODE=true marks a public sample-data instance: it shows a banner and the demo
 * sign-in details, never sends email, and refuses the actions that could reach outside the demo (sign-up
 * forms, integrations, imports). scripts/demo-reset.php rebuilds its data every night.
 */

const DEMO_PASSWORD = 'DemoPass2026!';

/** The accounts the seed creates, for the sign-in page. [role label, email] */
function demo_accounts(): array
{
    return [
        ['Organisation administrator', 'admin@demo.glenross.test'],
        ['Member of staff', 'staff@demo.glenross.test'],
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
        <p>To use Digital ID with your own organisation, <a href="mailto:<?php echo htmlspecialchars(CONTACT_EMAIL); ?>?subject=Digital%20ID%20for%20my%20organisation">get in touch</a>.</p>
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
    return '<div class="demo-banner" role="region" aria-label="Demo notice"><strong>Demo</strong> '
        . '<span class="demo-banner__text">All names and records here are made up, and the data resets every night. Please do not enter real personal information.</span> <a href="'.$sw.'">Part of Find Care Scotland\'s software for providers</a></div>';
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
