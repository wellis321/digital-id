<?php
/**
 * Signed-out front page. Every claim here is true of the product today (see docs-*.php); the screenshots are
 * the live demo with invented people. No inline styles: everything is in assets/css/home.css.
 */
$img = fn(string $f) => url('assets/product/' . $f) . '?v=1';
$demo = defined('DEMO_MODE') && DEMO_MODE;
?>
<!-- Hero -->
<section class="dh-hero" aria-labelledby="dh-h1">
    <div class="dh-wrap dh-hero__grid">
        <div class="dh-hero__text">
            <p class="dh-eyebrow">Digital staff ID cards</p>
            <h1 id="dh-h1">Know who is at the door.<br><span>Show who you are.</span></h1>
            <p class="dh-lede">A digital ID card for every member of your team. A family member, a visitor or a colleague can check who is in front of them in seconds, you keep a record that the check happened, and when someone leaves their card stops checking out.</p>
            <div class="dh-actions">
                <a class="dh-btn dh-btn--gold" href="#demo">Try the demonstration</a>
                <a class="dh-btn dh-btn--ghost" href="#how">See how a check works</a>
            </div>
            <p class="dh-fine"><i class="fas fa-circle-info" aria-hidden="true"></i> A demonstration with invented people. Nothing to sign up for, and it resets every night.</p>
        </div>

        <div class="dh-stage">
            <div class="dh-pass">
                <span class="dh-pass__slot" aria-hidden="true"></span>
                <div class="dh-pass__screen">
                    <img src="<?php echo $img('card.webp'); ?>" width="400" height="780" alt="A Digital ID card on a phone: organisation name, member of staff, reference number and a QR code to scan.">
                    <span class="dh-scan" aria-hidden="true"></span>
                </div>
            </div>
            <ul class="dh-chips" aria-hidden="true">
                <li class="dh-chip dh-chip--1"><i class="fas fa-qrcode"></i> QR code read</li>
                <li class="dh-chip dh-chip--2"><i class="fas fa-user-check"></i> Employment current</li>
                <li class="dh-chip dh-chip--3"><i class="fas fa-clipboard-list"></i> Check logged</li>
            </ul>
        </div>
    </div>
    <svg class="dh-guilloche" aria-hidden="true" focusable="false" viewBox="0 0 1200 400" preserveAspectRatio="xMidYMid slice">
        <defs>
            <pattern id="dh-g" width="48" height="48" patternUnits="userSpaceOnUse">
                <circle cx="24" cy="24" r="22" fill="none" stroke="currentColor" stroke-width=".6"/>
                <circle cx="24" cy="24" r="15" fill="none" stroke="currentColor" stroke-width=".6"/>
                <circle cx="24" cy="24" r="8" fill="none" stroke="currentColor" stroke-width=".6"/>
                <circle cx="0" cy="0" r="22" fill="none" stroke="currentColor" stroke-width=".6"/>
                <circle cx="48" cy="0" r="22" fill="none" stroke="currentColor" stroke-width=".6"/>
                <circle cx="0" cy="48" r="22" fill="none" stroke="currentColor" stroke-width=".6"/>
                <circle cx="48" cy="48" r="22" fill="none" stroke="currentColor" stroke-width=".6"/>
            </pattern>
        </defs>
        <rect width="1200" height="400" fill="url(#dh-g)"/>
    </svg>
</section>

<!-- The suite -->
<section class="dh-suite" id="suite" aria-labelledby="dh-suite-h">
    <div class="dh-wrap">
        <div class="dh-suitehead">
            <p class="dh-eyebrow">One suite, joining up</p>
            <h2 id="dh-suite-h">Everything listens to one record.</h2>
            <p class="dh-suitelead">Change a person once in Care Core and the products that depend on them find out. Digital ID is part of that suite, and we are working towards every product being fully connected.</p>
        </div>
        <figure class="dh-diagram"><svg viewBox="0 -100 960 740" role="img" aria-labelledby="dh-dia-t dh-dia-d"><title id="dh-dia-t">How the suite joins up around Care Core</title><desc id="dh-dia-d">Care Core sits in the centre. Care Plans, Digital ID, Policy Core, Referrals, Contracts, Strategic Plans, Incident Core, EngageTrack, Improvement Core, Learning Core and Rota Core are each connected to it today. Digital ID is the product you are looking at.</desc><g class="dh-rings"><circle cx="480" cy="280" r="110"/><circle cx="480" cy="280" r="170"/><circle cx="480" cy="280" r="234"/></g><g><path class="dh-live" d="M216 106 C340 106 380 220 424 250"/><path class="dh-live" d="M216 280 L406 280"/><path class="dh-live" d="M216 454 C340 454 380 340 424 310"/><path class="dh-live" d="M744 106 C620 106 580 220 536 250"/><path class="dh-live" d="M744 280 L554 280"/><path class="dh-live" d="M744 454 C620 454 580 340 536 310"/><path class="dh-live" d="M452 350 C420 440 360 480 326 540"/><path class="dh-live" d="M508 350 C540 440 600 480 634 540"/><path class="dh-live" d="M414 576 L546 576"/><path class="dh-live" d="M480 206 L480 -8"/><path class="dh-live" d="M744 -44 C640 -44 580 150 534 240"/><path class="dh-live" d="M216 -44 C320 -44 380 150 426 240"/></g><a class="dh-link" href="https://core.findcarescotland.com/" aria-label="Care Core: the record"><g class="dh-hub"><circle cx="480" cy="280" r="74"/><text x="480" y="276" class="dh-ht">Care Core</text><text x="480" y="300" class="dh-hs">the record</text></g></a><a class="dh-link" href="https://care.findcarescotland.com/" aria-label="Care Plans: care records"><g class="dh-node"><rect x="40" y="70" width="176" height="72" rx="8"/><text x="128" y="101" class="dh-nt">Care Plans</text><text x="128" y="124" class="dh-ns">care records</text></g></a><a class="dh-link" href="https://id.findcarescotland.com/" aria-label="Digital ID: staff cards and training (you are here)"><g class="dh-node dh-here"><rect x="40" y="244" width="176" height="72" rx="8"/><text x="128" y="275" class="dh-nt">Digital ID</text><text x="128" y="298" class="dh-ns">you are here</text></g></a><a class="dh-link" href="https://policies.findcarescotland.com/" aria-label="Policy Core: policies and reading"><g class="dh-node"><rect x="40" y="418" width="176" height="72" rx="8"/><text x="128" y="449" class="dh-nt">Policy Core</text><text x="128" y="472" class="dh-ns">policies and reading</text></g></a><a class="dh-link" href="https://core.findcarescotland.com/" aria-label="Referrals: held in Care Core"><g class="dh-node"><rect x="744" y="70" width="176" height="72" rx="8"/><text x="832" y="101" class="dh-nt">Referrals</text><text x="832" y="124" class="dh-ns">held in Care Core</text></g></a><a class="dh-link" href="https://contracts.findcarescotland.com/public/index.php" aria-label="Contracts: reads the people"><g class="dh-node"><rect x="744" y="244" width="176" height="72" rx="8"/><text x="832" y="275" class="dh-nt">Contracts</text><text x="832" y="298" class="dh-ns">reads the people</text></g></a><a class="dh-link" href="https://strategic.findcarescotland.com/" aria-label="Strategic Plans: teams own projects"><g class="dh-node"><rect x="744" y="418" width="176" height="72" rx="8"/><text x="832" y="449" class="dh-nt">Strategic Plans</text><text x="832" y="472" class="dh-ns">teams own projects</text></g></a><a class="dh-link" href="https://incidents.findcarescotland.com/" aria-label="Incident Core: incidents and restraint"><g class="dh-node"><rect x="238" y="540" width="176" height="72" rx="8"/><text x="326" y="571" class="dh-nt">Incident Core</text><text x="326" y="594" class="dh-ns">incidents and restraint</text></g></a><a class="dh-link" href="https://engage.findcarescotland.com/" aria-label="EngageTrack: behaviour support"><g class="dh-node"><rect x="546" y="540" width="176" height="72" rx="8"/><text x="634" y="571" class="dh-nt">EngageTrack</text><text x="634" y="594" class="dh-ns">behaviour support</text></g></a><a class="dh-link" href="https://improve.findcarescotland.com/" aria-label="Improvement Core: complaints and improvement"><g class="dh-node"><rect x="360" y="-80" width="240" height="72" rx="8"/><text x="480" y="-49" class="dh-nt">Improvement Core</text><text x="480" y="-26" class="dh-ns">complaints and improvement</text></g></a><a class="dh-link" href="https://learn.findcarescotland.com/" aria-label="Learning Core: training and certificates"><g class="dh-node"><rect x="744" y="-80" width="176" height="72" rx="8"/><text x="832" y="-49" class="dh-nt">Learning Core</text><text x="832" y="-26" class="dh-ns">training and certificates</text></g></a><a class="dh-link" href="https://rota.findcarescotland.com/" aria-label="Rota Core: rotas and shifts"><g class="dh-node"><rect x="40" y="-80" width="176" height="72" rx="8"/><text x="128" y="-49" class="dh-nt">Rota Core</text><text x="128" y="-26" class="dh-ns">rotas and shifts</text></g></a></svg><figcaption>Every line is connected today. Click any product to visit it. <a href="https://findcarescotland.com/software/suite">See the whole suite, from referral to care delivered</a>.</figcaption></figure>
        <div class="dh-suitecols">
            <div>
                <h3>Connected today</h3>
                <ul>
                    <li><strong>Care Core</strong> keeps one record of each person and member of staff.</li>
                    <li><strong>Care Plans, Digital ID and Policy Core</strong> read from it, so a joiner or leaver is entered once.</li>
                    <li><strong>Digital ID</strong> shows a member of staff's current training and skills from Care Core on their own card.</li>
                    <li><strong>Care Plans</strong> turns Care Core training into specialist skills with their expiry dates, so rotas can be checked against them.</li>
                    <li><strong>Referrals</strong> are held in Care Core and flow into Care Plans, with the specialist skills they call for.</li>
                    <li><strong>Contracts</strong> reads the people you support from Care Core, so each person is entered once.</li>
                    <li><strong>Strategic Plans</strong> projects are owned by a Care Core team and show who is in it today.</li>
                    <li><strong>Incident Core</strong> reads people, staff and teams from Care Core, so each incident, injury and restraint is recorded against the right person and service.</li>
                    <li><strong>Improvement Core</strong> reads people, staff and teams from Care Core, so what the public, families and staff raise is recorded against the right service.</li>
                </ul>
            </div>
            <div>
                <h3>Coming next</h3>
                <ul>
                    <li><strong>Deeper links</strong>: contracts beside each person's referral, and plan goals tied to services.</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- How a check works -->
<section class="dh-section" id="how" aria-labelledby="dh-how">
    <div class="dh-wrap">
        <div class="dh-head2">
            <p class="dh-eyebrow dh-eyebrow--dark">How a check works</p>
            <h2 id="dh-how">Three ways to check, so no one is stuck.</h2>
            <p>A card is only useful if the person checking can actually use it. Each method works on its own, and every check is recorded.</p>
        </div>

        <ol class="dh-ladder">
            <li class="dh-step dh-step--wide">
                <span class="dh-step__n" aria-hidden="true">1</span>
                <div>
                    <h3><i class="fas fa-eye" aria-hidden="true"></i> Look at it</h3>
                    <p>The card shows the organisation, the person&rsquo;s name, their photo once it has been approved, and a reference number. Anyone can compare the face and name, with nothing to install.</p>
                    <p class="dh-step__why"><strong>Why it matters:</strong> an older relative or a person you support can check without a phone or a scanner.</p>
                </div>
            </li>
            <li class="dh-step">
                <span class="dh-step__n" aria-hidden="true">2</span>
                <div>
                    <h3><i class="fas fa-qrcode" aria-hidden="true"></i> Scan the QR code</h3>
                    <p>The code leads to a live check of the person&rsquo;s status. It refreshes on its own and each one lasts five minutes, so a screenshot is no use to anyone later.</p>
                </div>
            </li>
            <li class="dh-step">
                <span class="dh-step__n" aria-hidden="true">3</span>
                <div>
                    <h3><i class="fas fa-tower-broadcast" aria-hidden="true"></i> Tap or connect</h3>
                    <p>NFC and Bluetooth are there as extra options on supported phones, with the same five-minute tokens.</p>
                </div>
            </li>
        </ol>

        <div class="dh-leave">
            <i class="fas fa-user-slash" aria-hidden="true"></i>
            <p><strong>When someone leaves,</strong> mark them as no longer active. From then on, a check on their card fails and the attempt is logged. You do not have to collect anything back.</p>
        </div>
    </div>
</section>

<!-- Product stories -->
<section class="dh-section dh-section--tint" id="stories" aria-labelledby="dh-stories">
    <div class="dh-wrap">
        <div class="dh-head2">
            <p class="dh-eyebrow dh-eyebrow--dark">In use</p>
            <h2 id="dh-stories">Built around the moments that matter.</h2>
            <p>These are screens from the live demonstration, with invented staff at an invented care provider.</p>
        </div>

        <article class="dh-story">
            <div class="dh-story__text">
                <h3>One list of everyone with a card</h3>
                <p>Every member of staff, their HR reference and the reference shown on their card, and whether they are active. Add people one at a time or bring a whole team in from a CSV or JSON file.</p>
                <ul class="dh-ticks">
                    <li>Photos are approved before they appear on a card</li>
                    <li>Organisation structure (units and members) can be imported too</li>
                    <li>Each organisation&rsquo;s data is kept separate from every other&rsquo;s</li>
                </ul>
                <p class="dh-benefit"><strong>The benefit:</strong> the card list is the single place that says who is staff today.</p>
            </div>
            <div class="dh-shot">
                <div class="dh-shot__bar" aria-hidden="true"><i></i><i></i><i></i></div>
                <img src="<?php echo $img('people.webp'); ?>" width="1080" height="602" loading="lazy" alt="The employee list in the demonstration: names, email addresses, HR references, card references and a status of Active, with Edit and View ID card buttons.">
            </div>
        </article>

        <article class="dh-story dh-story--flip">
            <div class="dh-story__text">
                <h3>A fire drill, with a record of who was there</h3>
                <p>Start a session for a fire drill or a training day. Staff check in with their QR code, the attendance builds up as they do, and you export it when the session ends.</p>
                <ul class="dh-ticks">
                    <li>Session type, place, start and end time, and who ran it</li>
                    <li>Each person&rsquo;s time and method of check-in</li>
                    <li>Attendance exports for your own records</li>
                </ul>
                <p class="dh-benefit"><strong>The benefit:</strong> you can show who took part, without chasing a paper sign-in sheet.</p>
            </div>
            <div class="dh-shot">
                <div class="dh-shot__bar" aria-hidden="true"><i></i><i></i><i></i></div>
                <img src="<?php echo $img('drill.webp'); ?>" width="1080" height="345" loading="lazy" alt="A completed fire drill session in the demonstration with nine staff checked in by QR scan, each with a name, reference and time.">
            </div>
        </article>

        <article class="dh-story">
            <div class="dh-story__text">
                <h3>An audit trail you can filter and export</h3>
                <p>Every check, successful or not, is logged with the time, the person, the method and the result. Filter by date, employee, type, result, location or device, and export to CSV.</p>
                <ul class="dh-ticks">
                    <li>Successes, expired cards and refusals are all recorded</li>
                    <li>Results use text and an icon, never colour alone</li>
                    <li>Kept in your own system, ready when someone asks</li>
                </ul>
                <p class="dh-benefit"><strong>The benefit:</strong> when you are asked how you know who was on site, you have an answer.</p>
            </div>
            <div class="dh-shot">
                <div class="dh-shot__bar" aria-hidden="true"><i></i><i></i><i></i></div>
                <img src="<?php echo $img('audit.webp'); ?>" width="1080" height="620" loading="lazy" alt="The verification log in the demonstration: date and time, employee, check type (visual, QR, NFC), result (success or expired), who checked, and notes.">
            </div>
        </article>
    </div>
</section>

<!-- Joined up -->
<section class="dh-section dh-section--ink" id="joined" aria-labelledby="dh-joined">
    <div class="dh-wrap">
        <div class="dh-head2 dh-head2--light">
            <p class="dh-eyebrow">Joined up</p>
            <h2 id="dh-joined">Fed from the systems you already keep.</h2>
            <p>Nobody wants to type the same person in twice. Here is what is connected today, and what is not.</p>
        </div>

        <div class="dh-joins">
            <div class="dh-join dh-join--on">
                <h3><i class="fas fa-people-group" aria-hidden="true"></i> Care Core <span class="dh-tag">Connected</span></h3>
                <p>Joiners, role changes and leavers come from Care Core as they happen, so a new starter gets a card and a leaver&rsquo;s card stops checking out without a second round of data entry. Their current training and skills come across too, and show on their own card.</p>
            </div>
            <div class="dh-join dh-join--on">
                <h3><i class="fab fa-microsoft" aria-hidden="true"></i> Microsoft 365 <span class="dh-tag">Connected</span></h3>
                <p>Staff can sign in with the Microsoft account they already have (Entra ID single sign-on), and Digital ID can work with SharePoint, Power Automate and Teams.</p>
            </div>
            <div class="dh-join dh-join--on">
                <h3><i class="fas fa-door-open" aria-hidden="true"></i> Doors and turnstiles <span class="dh-tag">Via QR</span></h3>
                <p>Access points that can read a QR code can use the same card, and every attempt is logged with its location and device.</p>
            </div>
            <div class="dh-join dh-join--on">
                <h3><i class="fas fa-file-export" aria-hidden="true"></i> Your own files <span class="dh-tag">Import and export</span></h3>
                <p>Bring people and teams in from CSV or JSON, and export attendance sessions and the verification log to CSV for your own records.</p>
            </div>
        </div>
    </div>
</section>

<!-- Accessibility and personalisation -->
<section class="dh-section" id="access" aria-labelledby="dh-access">
    <div class="dh-wrap dh-two">
        <div class="dh-head2">
            <p class="dh-eyebrow dh-eyebrow--dark">Everyone can use it</p>
            <h2 id="dh-access">Designed for the person checking, not just the person carrying.</h2>
            <p>The people who check a card are often anxious, elderly or in a hurry. The page has to work for them first.</p>
        </div>
        <ul class="dh-list">
            <li><i class="fas fa-universal-access" aria-hidden="true"></i><div><strong>Built to WCAG 2.2 AA targets.</strong> Text contrast of at least 4.5:1, a clear focus ring, controls large enough to tap, and results that never rely on colour alone.</div></li>
            <li><i class="fas fa-eye" aria-hidden="true"></i><div><strong>A check that needs no technology.</strong> The visual check works with no scanner, no app and no account.</div></li>
            <li><i class="fas fa-mobile-screen" aria-hidden="true"></i><div><strong>Installs like an app, with no app store.</strong> It can be added to a phone&rsquo;s home screen, and a card that has been opened once can still be shown without a signal.</div></li>
            <li><i class="fas fa-palette" aria-hidden="true"></i><div><strong>Your own organisation on the card.</strong> The card carries your organisation&rsquo;s name and branding, and the reference format is yours to set.</div></li>
        </ul>
    </div>
</section>

<!-- Demo -->
<section class="dh-section dh-demo" id="demo" aria-labelledby="dh-demo-h">
    <div class="dh-wrap">
        <div class="dh-head2">
            <p class="dh-eyebrow dh-eyebrow--dark">Try it</p>
            <h2 id="dh-demo-h">Be an administrator, or a member of staff.</h2>
            <p>One press, no typing. You get a full set of invented people at an invented care provider.</p>
        </div>
        <?php if ($demo): ?>
        <form method="POST" action="<?php echo htmlspecialchars(url('demo-enter.php')); ?>" class="dh-demo__form" aria-label="Enter the demonstration">
            <?php echo CSRF::tokenField(); ?><?php echo demo_entry_fields(); ?>
            <button class="dh-role dh-role--a" type="submit" name="role" value="admin">
                <i class="fas fa-user-shield" aria-hidden="true"></i>
                <strong>Explore as an administrator</strong>
                <span>Manage staff and cards, run a check-in, read the audit trail.</span>
            </button>
            <button class="dh-role dh-role--b" type="submit" name="role" value="staff">
                <i class="fas fa-id-card" aria-hidden="true"></i>
                <strong>Explore as a member of staff</strong>
                <span>See your own ID card and its QR code.</span>
            </button>
        </form>
        <p class="dh-fine dh-fine--dark">Also try the <a href="<?php echo url('demo-id-card.php'); ?>">public example card</a> and the <a href="<?php echo url('login.php'); ?>">sign-in page</a>.</p>
        <?php else: ?>
        <p><a class="dh-btn dh-btn--gold" href="<?php echo url('request-access.php'); ?>">Request access</a> <a class="dh-btn dh-btn--outline" href="<?php echo url('login.php'); ?>">Log in</a></p>
        <?php endif; ?>
    </div>
</section>

<!-- FAQ -->
<section class="dh-section" id="faq" aria-labelledby="dh-faq">
    <div class="dh-wrap dh-faq">
        <h2 id="dh-faq">Questions people ask</h2>
        <details>
            <summary>Does a person need to install anything to check a card?</summary>
            <p>No. They look at the card, or scan the QR code with an ordinary phone camera. The answer appears in the browser.</p>
        </details>
        <details>
            <summary>What does a check tell the person?</summary>
            <p>Who the card belongs to, which organisation, and whether they are currently active. A person who has left, or whose card has expired, fails the check.</p>
        </details>
        <details>
            <summary>What do you keep about each check?</summary>
            <p>The time, the card, the type of check, the result and where it came from, so you can answer questions later. See the <a href="<?php echo url('privacy-policy.php'); ?>">privacy policy</a> for the detail.</p>
        </details>
        <details>
            <summary>Can it work with our Microsoft 365 setup?</summary>
            <p>Yes. People can sign in with their Microsoft account (Entra ID), and it can work with SharePoint, Power Automate and Teams. <a href="<?php echo url('docs-entra-integration.php'); ?>">Read how it is set up.</a></p>
        </details>
        <details>
            <summary>Can we try it with our own team?</summary>
            <p>Yes. We would start with a small team, agree what you want to measure, and take your feedback seriously. <a href="<?php echo url('request-access.php'); ?>">Ask for access.</a></p>
        </details>
        <details>
            <summary>Does it work with our door entry system?</summary>
            <p>It gives every member of staff a card that those systems can read through a QR code, and every attempt is logged.</p>
        </details>
    </div>
</section>

<!-- Close -->
<section class="dh-close" aria-labelledby="dh-close-h">
    <div class="dh-wrap">
        <h2 id="dh-close-h">See it in two minutes.</h2>
        <p>The demonstration needs no sign-up, and nothing you do in it affects anyone real.</p>
        <p><a class="dh-btn dh-btn--gold" href="#demo">Try the demonstration</a> <a class="dh-btn dh-btn--ghost" href="<?php echo url('request-access.php'); ?>">Ask about a pilot</a></p>
    </div>
</section>
