<?php
/** Signed-out front page header (sticky). Used when a page sets $marketing = true. */
?>
<header class="dh-head">
    <div class="dh-head__in">
        <a href="<?php echo url('index.php'); ?>" class="dh-logo">
            <img src="<?php echo url('assets/images/300-high-digitalID-logo.png'); ?>" alt="" width="36" height="36">
            <span><?php echo htmlspecialchars(APP_NAME); ?></span>
        </a>
        <nav class="dh-nav" aria-label="Main navigation">
            <a href="#how">How it works</a>
            <a href="#stories">In use</a>
            <a href="#joined">Joined up</a>
            <a href="#access">Accessibility</a>
            <a href="#faq">Questions</a>
            <a href="<?php echo url('docs-getting-started.php'); ?>">Documentation</a>
        </nav>
        <div class="dh-head__cta">
            <a class="dh-link" href="<?php echo url('login.php'); ?>">Log in</a>
            <a class="dh-btn dh-btn--gold" href="#demo">Try the demo</a>
        </div>
    </div>
</header>
<main id="main-content" class="dh">
