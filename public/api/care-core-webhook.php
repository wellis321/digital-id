<?php
/**
 * Receives events from Care Core (someone joined, changed role or left) and applies them at once.
 * POST /api/care-core-webhook.php?t=<token>   header X-CareCore-Signature: sha256=<hmac of the raw body>
 * The token (in the address) says which organisation; the signature proves it came from Care Core. Both must be right.
 * A 5xx makes Care Core retry; events carry ids, so a retry never applies twice.
 */
require_once dirname(__DIR__, 2) . '/config/config.php';
header('Content-Type: application/json');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); echo '{"error":"method"}'; exit; }

try {
    CareCore::ensureSchema();
    $orgId = CareCore::orgForToken((string) ($_GET['t'] ?? ''));
    $body = file_get_contents('php://input', false, null, 0, 1048577);
    if ($orgId === null || $body === false || strlen($body) > 1048576) { http_response_code(404); echo '{"error":"unknown"}'; exit; }
    $secret = CareCore::get($orgId, 'care_core_webhook_secret');
    $sig = (string) ($_SERVER['HTTP_X_CARECORE_SIGNATURE'] ?? '');
    if ($secret === '' || !str_starts_with($sig, 'sha256=') || !hash_equals('sha256=' . hash_hmac('sha256', $body, $secret), $sig)) { http_response_code(401); echo '{"error":"signature"}'; exit; }
    $event = json_decode($body, true);
    if (!is_array($event)) { http_response_code(400); echo '{"error":"json"}'; exit; }
    [$outcome, $detail] = CareCoreSync::applyEvent($orgId, $event);
    echo json_encode(['ok' => true, 'outcome' => $outcome]);
} catch (InvalidArgumentException $e) {
    http_response_code(400); echo '{"error":"not an event"}';
} catch (Throwable $e) {
    error_log('care-core-webhook: ' . $e->getMessage());
    http_response_code(500); echo '{"error":"retry"}';
}
