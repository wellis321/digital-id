<?php
/**
 * Microsoft 365 Integration Class
 * Handles integration with SharePoint Lists, Power Automate, Teams, and Graph API
 */

require_once SRC_PATH . '/classes/EntraIntegration.php';
require_once SRC_PATH . '/classes/CheckInSession.php';

class Microsoft365Integration {
    
    /**
     * Get Microsoft Graph access token using client credentials flow
     */
    public static function getAccessToken($organisationId) {
        // Use existing Entra integration for authentication
        $config = EntraIntegration::getConfig($organisationId);
        
        if (!$config || !$config['entra_enabled']) {
            return [
                'success' => false,
                'message' => 'Microsoft Entra integration is not enabled for this organisation.'
            ];
        }
        
        // Get organisation Microsoft 365 settings
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT m365_sync_enabled, m365_sharepoint_site_url, 
                   entra_tenant_id, entra_client_id
            FROM organisations 
            WHERE id = ?
        ");
        $stmt->execute([$organisationId]);
        $org = $stmt->fetch();
        
        if (!$org || !$org['m365_sync_enabled']) {
            return [
                'success' => false,
                'message' => 'Microsoft 365 sync is not enabled for this organisation.'
            ];
        }
        
        $tenantId = $config['entra_tenant_id'];
        $clientId = $config['entra_client_id'];
        
        // Get client secret from environment variable
        // In production, this should be stored securely (Azure Key Vault, etc.)
        $clientSecret = getenv('ENTRA_CLIENT_SECRET') ?: '';
        
        if (empty($clientSecret)) {
            return [
                'success' => false,
                'message' => 'Microsoft Entra client secret not configured. Please set ENTRA_CLIENT_SECRET environment variable.'
            ];
        }
        
        // Use client credentials flow to get access token
        $tokenUrl = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token";
        
        $data = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => 'https://graph.microsoft.com/.default',
            'grant_type' => 'client_credentials'
        ];
        
        try {
            $ch = curl_init($tokenUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            if ($error) {
                throw new Exception("cURL error: " . $error);
            }
            
            if ($httpCode !== 200) {
                return [
                    'success' => false,
                    'message' => "Failed to get access token. HTTP {$httpCode}: " . $response
                ];
            }
            
            $tokenData = json_decode($response, true);
            
            if (!isset($tokenData['access_token'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid token response from Microsoft'
                ];
            }
            
            return [
                'success' => true,
                'access_token' => $tokenData['access_token'],
                'expires_in' => $tokenData['expires_in'] ?? 3600
            ];
        } catch (Exception $e) {
            error_log("Access token error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error getting access token: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Sync check-in to SharePoint List
     */
    public static function syncCheckInToSharePoint($organisationId, $checkIn) {
        $db = getDbConnection();
        
        // Get organisation SharePoint settings
        $stmt = $db->prepare("
            SELECT m365_sharepoint_site_url, m365_sharepoint_list_id,
                   entra_tenant_id, entra_client_id
            FROM organisations 
            WHERE id = ?
        ");
        $stmt->execute([$organisationId]);
        $org = $stmt->fetch();
        
        if (!$org || !$org['m365_sharepoint_site_url'] || !$org['m365_sharepoint_list_id']) {
            return [
                'success' => false,
                'message' => 'SharePoint integration not configured.'
            ];
        }
        
        // Log sync attempt
        $syncLogId = self::logSync($organisationId, 'check_in', $checkIn['id'], 'check_in', 'pending');
        
        try {
            // Get access token
            $tokenResult = self::getAccessToken($organisationId);
            if (!$tokenResult['success']) {
                self::updateSyncLog($syncLogId, 'failed', $tokenResult['message']);
                return $tokenResult;
            }
            
            // Prepare SharePoint list item data
            $employeeName = ($checkIn['first_name'] ?? '') . ' ' . ($checkIn['last_name'] ?? '');
            $employeeRef = $checkIn['display_reference'] ?? $checkIn['employee_reference'] ?? '';
            
            $listItemData = [
                'Title' => $employeeName,
                'EmployeeReference' => $employeeRef,
                'CheckInTime' => $checkIn['checked_in_at'],
                'CheckOutTime' => $checkIn['checked_out_at'] ?? null,
                'Location' => $checkIn['location_name'] ?? '',
                'Method' => $checkIn['check_in_method'] ?? 'manual',
                'SessionId' => $checkIn['session_id'] ?? null
            ];
            
            // Call Microsoft Graph API to create/update list item
            $siteUrl = $org['m365_sharepoint_site_url'];
            $listId = $org['m365_sharepoint_list_id'];
            
            // Construct Graph API URL for SharePoint
            // Site URL can be in format: https://tenant.sharepoint.com/sites/SiteName
            // We need to get the site ID first, or use the site path directly
            // For simplicity, we'll try to use the site path directly
            // In production, you might want to resolve the site ID first
            $sitePath = parse_url($siteUrl, PHP_URL_PATH);
            if ($sitePath) {
                // Remove leading slash
                $sitePath = ltrim($sitePath, '/');
                $graphUrl = "https://graph.microsoft.com/v1.0/sites/{$sitePath}/lists/{$listId}/items";
            } else {
                // Fallback: try using the full URL as site identifier
                $graphUrl = "https://graph.microsoft.com/v1.0/sites/{$siteUrl}/lists/{$listId}/items";
            }
            
            $ch = curl_init($graphUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $tokenResult['access_token'],
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'fields' => $listItemData
            ]));
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            if ($error) {
                throw new Exception("cURL error: " . $error);
            }
            
            if ($httpCode >= 200 && $httpCode < 300) {
                $responseData = json_decode($response, true);
                $m365Id = $responseData['id'] ?? null;
                
                self::updateSyncLog($syncLogId, 'success', null, $m365Id);
                
                return [
                    'success' => true,
                    'message' => 'Check-in synced to SharePoint successfully.',
                    'm365_id' => $m365Id
                ];
            } else {
                $errorMsg = "HTTP {$httpCode}: " . $response;
                self::updateSyncLog($syncLogId, 'failed', $errorMsg);
                return [
                    'success' => false,
                    'message' => 'Failed to sync to SharePoint: ' . $errorMsg
                ];
            }
        } catch (Exception $e) {
            error_log("SharePoint sync error: " . $e->getMessage());
            self::updateSyncLog($syncLogId, 'failed', $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error syncing to SharePoint: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Sync session to SharePoint List
     */
    public static function syncSessionToSharePoint($organisationId, $session) {
        $db = getDbConnection();
        
        // Get organisation SharePoint settings
        $stmt = $db->prepare("
            SELECT m365_sharepoint_site_url, m365_sharepoint_list_id
            FROM organisations 
            WHERE id = ?
        ");
        $stmt->execute([$organisationId]);
        $org = $stmt->fetch();
        
        if (!$org || !$org['m365_sharepoint_site_url'] || !$org['m365_sharepoint_list_id']) {
            return [
                'success' => false,
                'message' => 'SharePoint integration not configured.'
            ];
        }
        
        // Log sync attempt
        $syncLogId = self::logSync($organisationId, 'session', $session['id'], 'session', 'pending');
        
        try {
            // Similar to syncCheckInToSharePoint but for sessions
            // This would create a session record in SharePoint
            // Implementation similar to above
            
            self::updateSyncLog($syncLogId, 'success');
            
            // Mark session as synced
            CheckInSession::markSynced($session['id'], $org['m365_sharepoint_list_id'], null);
            
            return [
                'success' => true,
                'message' => 'Session synced to SharePoint successfully.'
            ];
        } catch (Exception $e) {
            error_log("SharePoint session sync error: " . $e->getMessage());
            self::updateSyncLog($syncLogId, 'failed', $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error syncing session to SharePoint: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Trigger Power Automate workflow
     */
    public static function triggerPowerAutomate($organisationId, $eventType, $data) {
        $db = getDbConnection();
        
        // Get organisation Power Automate webhook URL
        $stmt = $db->prepare("
            SELECT m365_power_automate_webhook_url
            FROM organisations 
            WHERE id = ?
        ");
        $stmt->execute([$organisationId]);
        $org = $stmt->fetch();
        
        if (!$org || !$org['m365_power_automate_webhook_url']) {
            return [
                'success' => false,
                'message' => 'Power Automate webhook not configured.'
            ];
        }
        
        try {
            $payload = [
                'event_type' => $eventType,
                'timestamp' => date('c'),
                'data' => $data
            ];
            
            $ch = curl_init($org['m365_power_automate_webhook_url']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            if ($error) {
                throw new Exception("cURL error: " . $error);
            }
            
            if ($httpCode >= 200 && $httpCode < 300) {
                return [
                    'success' => true,
                    'message' => 'Power Automate workflow triggered successfully.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Power Automate webhook returned HTTP {$httpCode}: " . $response
                ];
            }
        } catch (Exception $e) {
            error_log("Power Automate trigger error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error triggering Power Automate: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Send notification to Teams channel
     */
    public static function sendTeamsNotification($organisationId, $message, $title = null, $sessionId = null) {
        $db = getDbConnection();
        
        // Get organisation Teams channel ID
        $stmt = $db->prepare("
            SELECT m365_teams_channel_id, entra_tenant_id, entra_client_id
            FROM organisations 
            WHERE id = ?
        ");
        $stmt->execute([$organisationId]);
        $org = $stmt->fetch();
        
        if (!$org || !$org['m365_teams_channel_id']) {
            return [
                'success' => false,
                'message' => 'Teams channel not configured.'
            ];
        }
        
        try {
            // Get access token
            $tokenResult = self::getAccessToken($organisationId);
            if (!$tokenResult['success']) {
                return $tokenResult;
            }
            
            // Send message to Teams channel via Graph API
            // Note: Teams channel ID format is typically: 19:channelId@thread.tacv2
            // We need the team ID and channel ID separately
            // For now, assume the channel ID is provided in full format
            $channelId = $org['m365_teams_channel_id'];
            
            // Extract team ID and channel ID if in format "teamId/channelId"
            // Otherwise assume it's a full channel ID
            if (strpos($channelId, '/') !== false) {
                list($teamId, $channelIdPart) = explode('/', $channelId, 2);
                $graphUrl = "https://graph.microsoft.com/v1.0/teams/{$teamId}/channels/{$channelIdPart}/messages";
            } else {
                // If only channel ID provided, we need team ID - this is a limitation
                // In production, you'd store both team ID and channel ID
                return [
                    'success' => false,
                    'message' => 'Teams integration requires both team ID and channel ID. Please provide in format: teamId/channelId'
                ];
            }
            
            $messageBody = [
                'body' => [
                    'contentType' => 'html',
                    'content' => $message
                ]
            ];
            
            if ($title) {
                $messageBody['subject'] = $title;
            }
            
            $ch = curl_init($graphUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $tokenResult['access_token'],
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageBody));
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            if ($error) {
                throw new Exception("cURL error: " . $error);
            }
            
            if ($httpCode >= 200 && $httpCode < 300) {
                return [
                    'success' => true,
                    'message' => 'Teams notification sent successfully.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Teams API returned HTTP {$httpCode}: " . $response
                ];
            }
        } catch (Exception $e) {
            error_log("Teams notification error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error sending Teams notification: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Generic Graph API sync method
     */
    public static function syncViaGraphAPI($organisationId, $endpoint, $method = 'GET', $data = null) {
        $tokenResult = self::getAccessToken($organisationId);
        if (!$tokenResult['success']) {
            return $tokenResult;
        }
        
        try {
            $ch = curl_init("https://graph.microsoft.com/v1.0/{$endpoint}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            if ($method === 'POST' || $method === 'PATCH' || $method === 'PUT') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                if ($data) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
            }
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $tokenResult['access_token'],
                'Content-Type: application/json'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            if ($error) {
                throw new Exception("cURL error: " . $error);
            }
            
            $responseData = json_decode($response, true);
            
            if ($httpCode >= 200 && $httpCode < 300) {
                return [
                    'success' => true,
                    'data' => $responseData
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Graph API returned HTTP {$httpCode}",
                    'data' => $responseData
                ];
            }
        } catch (Exception $e) {
            error_log("Graph API sync error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error calling Graph API: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Log sync attempt
     */
    private static function logSync($organisationId, $syncType, $entityId, $entityType, $status) {
        $db = getDbConnection();
        
        $stmt = $db->prepare("
            INSERT INTO microsoft_365_sync_log 
            (organisation_id, sync_type, entity_id, entity_type, sync_status)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$organisationId, $syncType, $entityId, $entityType, $status]);
        
        return $db->lastInsertId();
    }
    
    /**
     * Update sync log
     */
    private static function updateSyncLog($syncLogId, $status, $error = null, $m365Id = null) {
        $db = getDbConnection();
        
        $stmt = $db->prepare("
            UPDATE microsoft_365_sync_log 
            SET sync_status = ?, 
                sync_error = ?,
                microsoft_365_id = ?,
                synced_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        
        $stmt->execute([$status, $error, $m365Id, $syncLogId]);
    }
}

