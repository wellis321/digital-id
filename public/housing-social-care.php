<?php
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = 'Housing & Social Care';
include INCLUDES_PATH . '/header.php';

?>

<link rel="stylesheet" href="<?php echo url('assets/css/story.css'); ?>?v=<?php echo @filemtime(__DIR__ . '/assets/css/story.css'); ?>">
<div class="story">
<div class="story-wrap">
<section class="story-hero">
 <div>
  <span class="story-kicker">Housing &amp; social care</span>
  <h1>One ID for every place your team goes</h1>
  <p class="lead">From the front door in the morning to the partner till at the end of the day, Digital ID lets your staff prove who they are, and lets everyone they meet check it.</p>
  <div class="story-actions">
  <?php if (!Auth::isLoggedIn()): ?>
   <a href="<?php echo url('request-access.php'); ?>" class="btn btn-primary">Request access</a>
   <a href="<?php echo url('features.php'); ?>" class="btn btn-secondary">View features</a>
  <?php else: ?>
   <a href="<?php echo url('id-card.php'); ?>" class="btn btn-primary">View your ID card</a>
  <?php endif; ?>
  </div>
  <p class="story-note">The pictures on this page are drawn illustrations with invented names and data.</p>
 </div>
 <div class="idstage" aria-hidden="true">
  <div class="idchip c1"><i class="fas fa-door-open"></i> Access granted</div>
  <div class="idchip c2"><i class="fas fa-briefcase"></i> Employment current</div>
  <div class="idchip c3"><i class="fas fa-clock"></i> Time-limited code</div>
  <div class="idcard"><div class="top"><span>HARBOUR CARE</span><span>STAFF ID</span></div>
   <div class="body"><div class="face">AM</div><div><div class="nm">Alex Morgan</div><div class="rl">Support worker</div></div></div>
   <div class="qr"></div><div class="ok"><i class="fas fa-check"></i> Verified</div></div>
 </div>
</section>

<div class="day-intro"><h2>A day in the life</h2><p>Digital ID follows your staff through the day. Each moment below shows what the person at the other end sees.</p></div>
<section class="moment">
 <div class="moment-text"><span class="when"><i class="fas fa-clock"></i> 08:50</span><h3>Starting the day: building access</h3><p>Your team arrives at the office or service location. Instead of fumbling for keys or access cards, they scan their Digital ID QR code at the turnstile or door access panel.</p><ul><li>Instant building access verification</li><li>Automatic attendance logging</li><li>Secure, time-limited QR codes</li><li>Works with any turnstile or access system</li></ul></div>
 <div class="scene" aria-hidden="true"><div class="scene-bar"><span class="dot"></span>Front entrance<span class="tag">Reader 1</span></div>
<div class="reader"><div class="panel"><div class="scan"></div><div class="msg"><i class="fas fa-check-circle"></i> Access granted &middot; 08:50</div></div>
<div class="phone"><strong>Digital ID</strong><div class="mini"></div>Time-limited code</div></div><div class="scene-foot">Illustration with invented data</div></div>
</section>
<section class="moment flip">
 <div class="moment-text"><span class="when"><i class="fas fa-clock"></i> 10:15</span><h3>Visiting clients: care home access</h3><p>When visiting care homes or supported living, staff can verify their identity quickly and securely. Families and care home staff can check your team’s credentials on the spot.</p><ul><li>Instant identity verification for families</li><li>QR code scanning for quick access</li><li>Verified employment status</li><li>Peace of mind for vulnerable clients</li></ul></div>
 <div class="scene" aria-hidden="true"><div class="scene-bar"><span class="dot"></span><i class="fas fa-qrcode"></i> Verification<span class="tag">Digital ID</span></div><div class="scene-body">
<div class="srow"><i class="fas fa-user-check lead"></i><div><div class="t">Name matches the photo</div><div class="s">Alex Morgan</div></div><span class="st">Matches</span></div>
<div class="srow"><i class="fas fa-briefcase lead"></i><div><div class="t">Employed by Harbour Care</div><div class="s">Support worker</div></div><span class="st">Active</span></div>
<div class="srow"><i class="fas fa-clock lead"></i><div><div class="t">Card checked</div><div class="s">Just now</div></div><span class="st">Valid</span></div>
</div><div class="scene-foot">Illustration with invented data</div></div>
</section>
<section class="moment">
 <div class="moment-text"><span class="when"><i class="fas fa-clock"></i> 11:40</span><h3>Banking on behalf of clients</h3><p>When acting for vulnerable clients at banks and financial institutions, your staff can prove their identity and employment status. Bank staff can verify credentials in seconds.</p><ul><li>Real-time employment status confirmation</li><li>Secure, logged verification process</li></ul></div>
 <div class="scene" aria-hidden="true"><div class="scene-bar"><span class="dot"></span><i class="fas fa-building-columns"></i> Branch desk<span class="tag">Digital ID</span></div><div class="scene-body">
<div class="srow"><i class="fas fa-id-card lead"></i><div><div class="t">Digital ID scanned</div><div class="s">Alex Morgan</div></div><span class="st">Read</span></div>
<div class="srow"><i class="fas fa-briefcase lead"></i><div><div class="t">Employment status</div><div class="s">Harbour Care</div></div><span class="st">Current</span></div>
<div class="srow"><i class="fas fa-file-lines lead"></i><div><div class="t">Check recorded</div><div class="s">For your records</div></div><span class="st thistle">Logged</span></div>
</div><div class="scene-foot">Illustration with invented data</div></div>
</section>
<section class="moment flip">
 <div class="moment-text"><span class="when"><i class="fas fa-clock"></i> 13:05</span><h3>Housing provider visits</h3><p>When visiting housing provider offices or properties, your team can verify who they are. Housing staff can confirm who they are dealing with, so interactions are secure and trusted.</p><ul><li>Quick verification at housing offices</li><li>Property access verification</li><li>Professional appearance and credibility</li><li>Streamlined check-in</li></ul></div>
 <div class="scene" aria-hidden="true"><div class="scene-bar"><span class="dot"></span><i class="fas fa-clipboard-list"></i> Visit log<span class="tag">Digital ID</span></div><div class="scene-body">
<div class="srow"><i class="fas fa-door-open lead"></i><div><div class="t">Arrived at Dunmore Court</div><div class="s">13:05</div></div><span class="st">Checked in</span></div>
<div class="srow"><i class="fas fa-user lead"></i><div><div class="t">Visiting: Housing officer</div><div class="s">Annual review</div></div><span class="st">Confirmed</span></div>
<div class="srow"><i class="fas fa-right-from-bracket lead"></i><div><div class="t">Left the property</div><div class="s">14:00</div></div><span class="st gold">Checked out</span></div>
</div><div class="scene-foot">Illustration with invented data</div></div>
</section>
<section class="moment">
 <div class="moment-text"><span class="when"><i class="fas fa-clock"></i> 14:30</span><h3>Hospital and medical visits</h3><p>When accompanying clients to appointments or medical facilities, staff can verify their identity quickly. Medical staff can confirm your team’s credentials for patient support.</p><ul><li>Fast verification at medical facilities</li><li>Patient support authorisation</li><li>Secure access to restricted areas</li><li>Professional setting verification</li></ul></div>
 <div class="scene" aria-hidden="true"><div class="scene-bar"><span class="dot"></span><i class="fas fa-hospital"></i> Reception<span class="tag">Digital ID</span></div><div class="scene-body">
<div class="srow"><i class="fas fa-id-badge lead"></i><div><div class="t">Escort registered</div><div class="s">Alex Morgan</div></div><span class="st">Verified</span></div>
<div class="srow"><i class="fas fa-user-group lead"></i><div><div class="t">Accompanying a patient</div><div class="s">Outpatients</div></div><span class="st thistle">Noted</span></div>
<div class="srow"><i class="fas fa-shield-halved lead"></i><div><div class="t">Restricted area access</div><div class="s">Granted for this visit</div></div><span class="st gold">Allowed</span></div>
</div><div class="scene-foot">Illustration with invented data</div></div>
</section>
<section class="moment flip">
 <div class="moment-text"><span class="when"><i class="fas fa-clock"></i> 16:00</span><h3>Training sessions: check in and out</h3><p>During mandatory training, staff check in and out with their Digital ID. Attendance is tracked automatically for compliance and CPD.</p><ul><li>Quick QR code check-in</li><li>Automatic attendance tracking</li><li>Training session logging</li><li>Compliance reporting</li></ul></div>
 <div class="scene" aria-hidden="true"><div class="scene-bar"><span class="dot"></span><i class="fas fa-chalkboard-user"></i> Moving and handling<span class="tag">Digital ID</span></div><div class="scene-body">
<div class="srow"><i class="fas fa-user-check lead"></i><div><div class="t">Alex Morgan</div><div class="s">09:02</div></div><span class="st">In</span></div>
<div class="srow"><i class="fas fa-user-check lead"></i><div><div class="t">Priya Shah</div><div class="s">09:04</div></div><span class="st">In</span></div>
<div class="srow"><i class="fas fa-user-clock lead"></i><div><div class="t">Sam Reid</div><div class="s"></div></div><span class="st gold">Expected</span></div>
<div class="srow"><i class="fas fa-file-export lead"></i><div><div class="t">Attendance report</div><div class="s">Ready to export</div></div><span class="st thistle">Ready</span></div>
</div><div class="scene-foot">Illustration with invented data</div></div>
</section>
<section class="moment">
 <div class="moment-text"><span class="when"><i class="fas fa-clock"></i> 17:30</span><h3>Employee rewards and discounts</h3><p>Register for employee benefits and discounts using your Digital ID. Retail partners can verify employment status instantly.</p><ul><li>Instant employment verification</li><li>Access to exclusive discounts</li><li>Simple registration</li><li>Multiple retail partners supported</li></ul></div>
 <div class="scene" aria-hidden="true"><div class="scene-bar"><span class="dot"></span><i class="fas fa-tag"></i> Partner till<span class="tag">Digital ID</span></div><div class="scene-body">
<div class="srow"><i class="fas fa-id-card lead"></i><div><div class="t">Digital ID shown</div><div class="s">Alex Morgan</div></div><span class="st">Read</span></div>
<div class="srow"><i class="fas fa-briefcase lead"></i><div><div class="t">Employment checked</div><div class="s">Harbour Care</div></div><span class="st">Current</span></div>
<div class="srow"><i class="fas fa-percent lead"></i><div><div class="t">Staff discount applied</div><div class="s">Partner offer</div></div><span class="st thistle">Applied</span></div>
</div><div class="scene-foot">Illustration with invented data</div></div>
</section>

<div class="day-intro"><h2>Why housing and social care providers use it</h2></div>
<div class="bento">
 <div class="b wide dark"><span class="pulse"></span><i class="fas fa-clock big"></i><h3>Save time</h3><p>Quick verification means more time with clients and less time on paperwork.</p></div>
 <div class="b wide"><i class="fas fa-shield-alt big"></i><h3>Enhanced security</h3><p>Verifiable identity protection for your team and the people they support.</p></div>
 <div class="b"><i class="fas fa-clipboard-check big"></i><h3>Compliance ready</h3><p>Automatic logging and audit trails.</p></div>
 <div class="b"><i class="fas fa-mobile-alt big"></i><h3>Always accessible</h3><p>Open your Digital ID from any device.</p></div>
 <div class="b"><i class="fas fa-building big"></i><h3>Building access</h3><p>Turnstile and door access with attendance logged.</p></div>
 <div class="b wide"><i class="fas fa-user-check big"></i><h3>Client and family trust</h3><p>Build confidence with families through instant identity verification.</p></div>
 <div class="b wide dark"><i class="fas fa-qrcode big"></i><h3>Secure, time-limited codes</h3><p>A QR code that does not last is harder to copy or pass on.</p></div>
</div>
</div>
<div class="story-cta">
 <h2>See it working with invented data</h2>
 <p>Try the live demo in about a minute, with no sign-up. Or tell us about your team and we will show you how it would work for you.</p>
 <?php if (!Auth::isLoggedIn()): ?>
  <a href="<?php echo url('request-access.php'); ?>" class="btn-light">Request access</a>
  <a href="<?php echo url('features.php'); ?>" class="btn-ghost">Learn more</a>
 <?php else: ?>
  <a href="<?php echo url('id-card.php'); ?>" class="btn-light">View your Digital ID</a>
 <?php endif; ?>
</div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
