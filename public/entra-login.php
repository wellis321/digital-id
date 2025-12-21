<?php
/**
 * Microsoft Entra/365 Login Handler
 * Handles OAuth flow for Entra ID authentication
 */

require_once dirname(__DIR__) . '/config/config.php';

$organisationId = $_GET['org_id'] ?? null;
$code = $_GET['code'] ?? null;
$state = $_GET['state'] ?? null;
$error = $_GET['error'] ?? null;

if ($error) {
    header('Location: ' . url('login.php?error=entra_auth_failed'));
    exit;
}

if (!$organisationId) {
    header('Location: ' . url('login.php?error=invalid_org'));
    exit;
}

// Check if Entra is enabled for this organisation
if (!EntraIntegration::isEnabled($organisationId)) {
    header('Location: ' . url('login.php?error=entra_not_enabled'));
    exit;
}

// Handle OAuth callback
if ($code) {
    $redirectUri = APP_URL . url('entra-login.php?org_id=' . $organisationId);
    $tokenResult = EntraIntegration::exchangeCodeForToken($organisationId, $code, $redirectUri);
    
    if (!$tokenResult['success']) {
        header('Location: ' . url('login.php?error=entra_token_failed'));
        exit;
    }
    
    // Get user info from Microsoft Graph
    $userInfo = EntraIntegration::getUserInfo($tokenResult['token']['access_token']);
    
    if (!$userInfo) {
        header('Location: ' . url('login.php?error=entra_user_info_failed'));
        exit;
    }
    
    // Find or create user by email
    $db = getDbConnection();
    $stmt = $db->prepare("
        SELECT u.*, o.id as organisation_id 
        FROM users u 
        LEFT JOIN organisations o ON u.organisation_id = o.id
        WHERE u.email = ?
    ");
    $stmt->execute([$userInfo['mail'] ?? $userInfo['userPrincipalName']]);
    $user = $stmt->fetch();
    
    if ($user && $user['organisation_id'] == $organisationId) {
        // Login user
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['organisation_id'] = $user['organisation_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['user_data'] = $user;
        
        // Sync with Entra
        $employee = Employee::findByUserId($user['id']);
        if ($employee) {
            EntraIntegration::syncEmployee($organisationId, $userInfo['id'], $employee['id']);
        }
        
        header('Location: ' . url('id-card.php'));
        exit;
    } else {
        header('Location: ' . url('login.php?error=entra_user_not_found'));
        exit;
    }
}

// Initiate OAuth flow
$redirectUri = APP_URL . url('entra-login.php?org_id=' . $organisationId);
$authUrl = EntraIntegration::getAuthorizationUrl($organisationId, $redirectUri);

if ($authUrl) {
    header('Location: ' . $authUrl);
    exit;
} else {
    header('Location: ' . url('login.php?error=entra_config_failed'));
    exit;
}

