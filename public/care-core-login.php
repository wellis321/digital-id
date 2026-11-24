<?php
/**
 * Sign in through Care Core. ?start=1&org=ID sends the person to Care Core; Care Core sends them back here with a code, which is checked in full
 * (see CareCoreSignIn). Nobody is created and no role is taken from Care Core: the person must already have an account here with the same email address.
 */
require_once dirname(__DIR__) . '/config/config.php';

function cc_back(string $message): void
{
    $_SESSION['cc_flash'] = $message;
    header('Location: ' . url('login.php'));
    exit;
}

if (isset($_GET['start'])) {
    $org = (int) ($_GET['org'] ?? 0);
    [$to, $err] = CareCoreSignIn::begin($org, '');
    if ($to) { header('Location: ' . $to); exit; }
    cc_back((string) $err);
}

$r = CareCoreSignIn::complete($_GET);
if (is_string($r)) cc_back($r);
[$u, , $strong] = $r;
$res = Auth::loginUserById((int) $u['id'], !$strong);   // a second step done in Care Core counts; otherwise this product asks for its own if the person has one
if ($res === 'second_step_required') { header('Location: ' . url('login-2fa.php')); exit; }
if ($res !== true) cc_back('Your account here is not ready to sign in. Check that your email is verified, or ask your administrator.');
if ($strong) $_SESSION['sso_strong'] = 1;
header('Location: ' . url('id-card.php'));
exit;
