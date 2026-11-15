<?php
// Demo only: sign in as the shared administrator or staff account with one press. See includes/demo.php (demo_entry_*) for how robots are kept out.
require_once dirname(__DIR__) . '/config/config.php';
require_once SRC_PATH . '/classes/RateLimiter.php';

if (!DEMO_MODE) {
    http_response_code(404);
    exit('Not found');
}
$fail = static function (string $message): void {
    $_SESSION['demo_entry_error'] = $message;
    header('Location: ' . url('login.php'));
    exit;
};
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('login.php'));
    exit;
}
// counted first, so a flood is slowed whatever it sends
$clientId = RateLimiter::getClientIdentifier();
if (RateLimiter::isRateLimited('demo_enter_' . $clientId, 20, 3600) || RateLimiter::isRateLimited('demo_enter_all', 600, 3600)) {
    $fail('That is a lot of attempts. Please wait a little and try again.');
}
$signedIn = Auth::isLoggedIn();
$human = CSRF::validatePost()
    && ($_POST['website'] ?? '') === ''
    && !demo_entry_looks_automated()
    && ($signedIn || demo_entry_token_ok((string) ($_POST['ft'] ?? '')));
$userId = $human ? demo_entry_user_id((string) ($_POST['role'] ?? '')) : null;
if (!$userId || !Auth::loginUserById($userId)) {
    $fail('That did not work. Please wait a moment after the page loads and press the button again, or use one of the demo accounts below.');
}
header('Location: ' . url('id-card.php'));
exit;
