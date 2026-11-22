<?php /** Signed-out front page footer. Closes the <main> opened by marketing-header.php. */ ?>
</main>
<footer class="dh-foot">
    <div class="dh-wrap dh-foot__in">
        <div>
            <p class="dh-foot__brand"><?php echo htmlspecialchars(APP_NAME); ?></p>
            <p class="dh-foot__small">Part of a suite of tools for care providers, from <a href="https://findcarescotland.com/software">Find Care Scotland</a>. Not endorsed by the Care Inspectorate or the SSSC.</p>
        </div>
        <nav aria-label="Footer">
            <a href="<?php echo url('features.php'); ?>">Features</a>
            <a href="<?php echo url('security.php'); ?>">Security</a>
            <a href="<?php echo url('housing-social-care.php'); ?>">Housing and social care</a>
            <a href="<?php echo url('docs-getting-started.php'); ?>">Documentation</a>
            <a href="<?php echo url('privacy-policy.php'); ?>">Privacy</a>
            <a href="<?php echo url('terms-of-service.php'); ?>">Terms</a>
            <a href="<?php echo url('contact.php'); ?>">Contact</a>
        </nav>
    </div>
</footer>
<script src="<?php echo url('assets/js/home.js'); ?>?v=<?php echo @filemtime(dirname(__DIR__) . '/public/assets/js/home.js'); ?>" defer></script>
