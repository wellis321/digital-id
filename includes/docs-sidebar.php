<?php
require_once dirname(__DIR__) . '/includes/docs-sections.php';
$currentDocsPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="docs-sidebar">
    <h3>Documentation</h3>
    <ul>
        <?php foreach ($docsSections as $file => $meta): ?>
            <li><a href="<?php echo url($file); ?>" class="<?php echo $currentDocsPage === $file ? 'active' : ''; ?>">
                <i class="<?php echo $meta['icon']; ?>"></i> <?php echo $meta['label']; ?>
            </a></li>
        <?php endforeach; ?>
    </ul>
</aside>
