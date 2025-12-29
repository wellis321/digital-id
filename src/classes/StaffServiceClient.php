<?php
/**
 * Staff Service API Client
 * Handles communication with the Staff Service API
 */

class StaffServiceClient {
    private static $baseUrl = null;
    private static $apiKey = null;
    private static $isEnabled = false;
    
    /**
     * Initialize the client
     * Reads settings directly from database to avoid caching issues
     */
    public static function init() {
        // Read settings directly from database (bypass static cache)
        $useStaffService = false;
        $baseUrl = '';
        $apiKey = '';
        
        try {
            if (function_exists('getDbConnection') && class_exists('Auth') && Auth::isLoggedIn()) {
                $db = getDbConnection();
                $organisationId = Auth::getOrganisationId();
                if ($organisationId) {
                    $stmt = $db->prepare("SELECT setting_key, setting_value FROM organisation_settings WHERE organisation_id = ? AND setting_key IN ('use_staff_service', 'staff_service_url', 'staff_service_api_key')");
                    $stmt->execute([$organisationId]);
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($results as $row) {
                        if ($row['setting_key'] === 'use_staff_service') {
                            $useStaffService = ($row['setting_value'] === '1');
                        } elseif ($row['setting_key'] === 'staff_service_url') {
                            $baseUrl = $row['setting_value'];
                        } elseif ($row['setting_key'] === 'staff_service_api_key') {
                            $apiKey = $row['setting_value'];
                        }
                    }
                }
            }
        } catch (Exception $e) {
            // Fall back to constants if database read fails
            error_log('Error reading Staff Service settings from database: ' . $e->getMessage());
        }
        
        // Fall back to constants if database values are empty
        if (empty($baseUrl) && defined('STAFF_SERVICE_URL')) {
            $baseUrl = STAFF_SERVICE_URL;
        }
        if (empty($apiKey) && defined('STAFF_SERVICE_API_KEY')) {
            $apiKey = STAFF_SERVICE_API_KEY;
        }
        if (!$useStaffService && defined('USE_STAFF_SERVICE')) {
            $useStaffService = USE_STAFF_SERVICE;
        }
        
        self::$isEnabled = $useStaffService;
        self::$baseUrl = rtrim($baseUrl, '/');
        self::$apiKey = $apiKey;
        
        // Validate configuration
        if (self::$isEnabled && (empty(self::$baseUrl) || empty(self::$apiKey))) {
            error_log('Staff Service integration enabled but URL or API key not configured');
            self::$isEnabled = false;
        }
    }
    
    /**
     * Check if Staff Service is available and enabled
     */
    public static function isAvailable() {
        if (!self::$isEnabled) {
            return false;
        }
        
        // Check if we can reach the API
        try {
            $url = self::$baseUrl . '/api/staff-data.php';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . self::$apiKey,
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_HTTPGET, true); // Use GET request (API only accepts GET)
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            // curl_close() is deprecated in PHP 8.5+ - resources are automatically closed
            
            // Accept 200, 401 (auth required but service is up), 400 (bad request but service is up)
            return in_array($httpCode, [200, 400, 401]);
        } catch (Exception $e) {
            error_log('Staff Service availability check failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get staff member by person ID
     */
    public static function getStaffMember($personId) {
        if (!self::$isEnabled) {
            return null;
        }
        
        self::init();
        
        try {
            $url = self::$baseUrl . '/api/staff-data.php?id=' . (int)$personId;
            $response = self::makeRequest($url);
            
            if ($response && isset($response['success']) && $response['success']) {
                return $response['data'] ?? null;
            }
            
            return null;
        } catch (Exception $e) {
            error_log('Error fetching staff member from Staff Service: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get staff member by user ID
     */
    public static function getStaffByUserId($userId) {
        if (!self::$isEnabled) {
            return null;
        }
        
        self::init();
        
        try {
            // Search by user_id - we'll need to search and filter
            // Since the API doesn't have a direct user_id parameter, we search and match
            $url = self::$baseUrl . '/api/staff-data.php?search=' . urlencode((string)$userId);
            $response = self::makeRequest($url);
            
            if ($response && isset($response['success']) && $response['success']) {
                $staff = $response['data'] ?? [];
                if (is_array($staff)) {
                    // Find staff member with matching user_id
                    foreach ($staff as $member) {
                        if (isset($member['user_id']) && $member['user_id'] == $userId) {
                            return $member;
                        }
                    }
                }
            }
            
            return null;
        } catch (Exception $e) {
            error_log('Error fetching staff by user ID from Staff Service: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Search staff members
     */
    public static function searchStaff($query) {
        
        if (!self::$isEnabled) {
            return [];
        }
        
        self::init();
        
        try {
            $url = self::$baseUrl . '/api/staff-data.php?search=' . urlencode($query);
            $response = self::makeRequest($url);
            
            if ($response && isset($response['success']) && $response['success']) {
                return $response['data'] ?? [];
            }
            
            return [];
        } catch (Exception $e) {
            error_log('Error searching staff in Staff Service: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get staff signature
     */
    public static function getStaffSignature($personId) {
        if (!self::$isEnabled) {
            return null;
        }
        
        self::init();
        
        try {
            $url = self::$baseUrl . '/api/signature.php?person_id=' . (int)$personId;
            $response = self::makeRequest($url);
            
            if ($response && isset($response['success']) && $response['success'] && isset($response['data'])) {
                return $response['data'];
            }
            
            return null;
        } catch (Exception $e) {
            error_log('Error fetching signature from Staff Service: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Make HTTP request to Staff Service API
     */
    private static function makeRequest($url) {
        if (empty(self::$baseUrl) || empty(self::$apiKey)) {
            return null;
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . self::$apiKey,
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        // curl_close() is deprecated in PHP 8.5+ - resources are automatically closed
        
        if ($error) {
            error_log('Staff Service API curl error: ' . $error);
            return null;
        }
        
        if ($httpCode !== 200) {
            error_log('Staff Service API returned HTTP ' . $httpCode . ' for URL: ' . $url);
            return null;
        }
        
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Staff Service API returned invalid JSON: ' . json_last_error_msg());
            return null;
        }
        
        return $data;
    }
}

// Auto-initialize on load
StaffServiceClient::init();

