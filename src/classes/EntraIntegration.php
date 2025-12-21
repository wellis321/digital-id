<?php
/**
 * Microsoft Entra/365 Integration Class
 * Handles optional Microsoft Entra ID integration for SSO and employee sync
 */

class EntraIntegration {
    
    /**
     * Check if Entra integration is enabled for an organisation
     */
    public static function isEnabled($organisationId) {
        $db = getDbConnection();
        
        // Check if Entra columns exist
        try {
            $stmt = $db->prepare("
                SELECT entra_enabled, entra_tenant_id, entra_client_id 
                FROM organisations 
                WHERE id = ?
            ");
            $stmt->execute([$organisationId]);
            $org = $stmt->fetch();
            
            // If columns don't exist, return false
            if (!$org) {
                return false;
            }
            
            // Check if entra_enabled column exists (might not if migration hasn't run)
            if (!isset($org['entra_enabled'])) {
                return false;
            }
            
            return $org['entra_enabled'] && !empty($org['entra_tenant_id']) && !empty($org['entra_client_id']);
        } catch (PDOException $e) {
            // If column doesn't exist, Entra is not enabled
            if (strpos($e->getMessage(), "Unknown column 'entra_enabled'") !== false) {
                return false;
            }
            throw $e;
        }
    }
    
    /**
     * Get Entra configuration for organisation
     */
    public static function getConfig($organisationId) {
        $db = getDbConnection();
        
        try {
            $stmt = $db->prepare("
                SELECT entra_enabled, entra_tenant_id, entra_client_id 
                FROM organisations 
                WHERE id = ?
            ");
            $stmt->execute([$organisationId]);
            $config = $stmt->fetch();
            
            // If columns don't exist, return default config
            if (!$config || !isset($config['entra_enabled'])) {
                return [
                    'entra_enabled' => false,
                    'entra_tenant_id' => null,
                    'entra_client_id' => null
                ];
            }
            
            return $config;
        } catch (PDOException $e) {
            // If column doesn't exist, return default config
            if (strpos($e->getMessage(), "Unknown column 'entra_enabled'") !== false) {
                return [
                    'entra_enabled' => false,
                    'entra_tenant_id' => null,
                    'entra_client_id' => null
                ];
            }
            throw $e;
        }
    }
    
    /**
     * Enable Entra integration for organisation
     */
    public static function enable($organisationId, $tenantId, $clientId, $clientSecret = null) {
        $db = getDbConnection();
        
        // Store client secret securely (in production, use environment variables or secure storage)
        // For now, we'll just store the tenant and client IDs
        $stmt = $db->prepare("
            UPDATE organisations 
            SET entra_enabled = TRUE, 
                entra_tenant_id = ?, 
                entra_client_id = ?
            WHERE id = ?
        ");
        $stmt->execute([$tenantId, $clientId, $organisationId]);
        
        return ['success' => true];
    }
    
    /**
     * Disable Entra integration for organisation
     */
    public static function disable($organisationId) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            UPDATE organisations 
            SET entra_enabled = FALSE 
            WHERE id = ?
        ");
        $stmt->execute([$organisationId]);
        
        return ['success' => true];
    }
    
    /**
     * Get OAuth authorization URL
     */
    public static function getAuthorizationUrl($organisationId, $redirectUri) {
        $config = self::getConfig($organisationId);
        
        if (!$config || !$config['entra_enabled']) {
            return null;
        }
        
        $tenantId = $config['entra_tenant_id'];
        $clientId = $config['entra_client_id'];
        
        $params = [
            'client_id' => $clientId,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'response_mode' => 'query',
            'scope' => 'openid profile email User.Read',
            'state' => bin2hex(random_bytes(16)) // CSRF protection
        ];
        
        return "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/authorize?" . http_build_query($params);
    }
    
    /**
     * Exchange authorization code for access token
     */
    public static function exchangeCodeForToken($organisationId, $code, $redirectUri) {
        $config = self::getConfig($organisationId);
        
        if (!$config || !$config['entra_enabled']) {
            return ['success' => false, 'message' => 'Entra integration not enabled'];
        }
        
        $tenantId = $config['entra_tenant_id'];
        $clientId = $config['entra_client_id'];
        $clientSecret = getenv('ENTRA_CLIENT_SECRET'); // Should be stored securely
        
        $tokenUrl = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token";
        
        $data = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code'
        ];
        
        $ch = curl_init($tokenUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return ['success' => false, 'message' => 'Failed to exchange code for token'];
        }
        
        $tokenData = json_decode($response, true);
        
        if (!isset($tokenData['access_token'])) {
            return ['success' => false, 'message' => 'Invalid token response'];
        }
        
        return ['success' => true, 'token' => $tokenData];
    }
    
    /**
     * Get user info from Microsoft Graph API
     */
    public static function getUserInfo($accessToken) {
        $graphUrl = 'https://graph.microsoft.com/v1.0/me';
        
        $ch = curl_init($graphUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return null;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Sync employee from Entra ID
     */
    public static function syncEmployee($organisationId, $entraUserId, $employeeId = null) {
        $db = getDbConnection();
        
        // Check if sync record exists
        $stmt = $db->prepare("
            SELECT * FROM entra_sync 
            WHERE organisation_id = ? AND entra_user_id = ?
        ");
        $stmt->execute([$organisationId, $entraUserId]);
        $syncRecord = $stmt->fetch();
        
        if ($syncRecord) {
            // Update existing sync
            $stmt = $db->prepare("
                UPDATE entra_sync 
                SET last_synced_at = NOW(), 
                    sync_status = 'active',
                    sync_error = NULL
                WHERE id = ?
            ");
            $stmt->execute([$syncRecord['id']]);
        } else {
            // Create new sync record
            if (!$employeeId) {
                return ['success' => false, 'message' => 'Employee ID required for new sync'];
            }
            
            $stmt = $db->prepare("
                INSERT INTO entra_sync 
                (employee_id, organisation_id, entra_user_id, last_synced_at, sync_status)
                VALUES (?, ?, ?, NOW(), 'active')
            ");
            $stmt->execute([$employeeId, $organisationId, $entraUserId]);
        }
        
        return ['success' => true];
    }
    
    /**
     * Get sync status for employee
     */
    public static function getSyncStatus($employeeId) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT * FROM entra_sync 
            WHERE employee_id = ?
        ");
        $stmt->execute([$employeeId]);
        return $stmt->fetch();
    }
}

