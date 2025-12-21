<?php
/**
 * Reference Generator
 * Generates display references for employees based on organisation configuration
 */

class ReferenceGenerator {
    
    /**
     * Generate a display reference for an employee
     * @param int $organisationId The organisation ID
     * @param string|null $employeeNumber The internal employee number (optional, for logging)
     * @return string|null Generated display reference or null if generation fails
     */
    public static function generateDisplayReference($organisationId, $employeeNumber = null) {
        $db = getDbConnection();
        
        // Get organisation reference configuration
        $stmt = $db->prepare("
            SELECT reference_prefix, reference_pattern, reference_start_number, reference_digits
            FROM organisations
            WHERE id = ?
        ");
        $stmt->execute([$organisationId]);
        $org = $stmt->fetch();
        
        if (!$org) {
            return null;
        }
        
        $prefix = $org['reference_prefix'] ?: '';
        $pattern = $org['reference_pattern'] ?: 'incremental';
        $startNumber = $org['reference_start_number'] ?: 1;
        $digits = $org['reference_digits'] ?: 6;
        
        // Generate reference based on pattern
        switch ($pattern) {
            case 'incremental':
                return self::generateIncremental($organisationId, $prefix, $startNumber, $digits);
            
            case 'random_alphanumeric':
                return self::generateRandomAlphanumeric($organisationId, $prefix, $digits);
            
            case 'custom':
                // For custom patterns, return null - admin must set manually
                return null;
            
            default:
                // Default to incremental
                return self::generateIncremental($organisationId, $prefix, $startNumber, $digits);
        }
    }
    
    /**
     * Generate incremental reference (e.g., SAMH-000001, SAMH-000002)
     */
    private static function generateIncremental($organisationId, $prefix, $startNumber, $digits) {
        $db = getDbConnection();
        
        // Get the highest existing display reference number for this organisation
        $stmt = $db->prepare("
            SELECT display_reference 
            FROM employees 
            WHERE organisation_id = ? 
            AND display_reference IS NOT NULL
            AND display_reference LIKE ?
            ORDER BY display_reference DESC 
            LIMIT 1
        ");
        
        $likePattern = $prefix ? $prefix . '-%' : '%';
        $stmt->execute([$organisationId, $likePattern]);
        $lastRef = $stmt->fetch();
        
        $nextNumber = $startNumber;
        
        if ($lastRef && $lastRef['display_reference']) {
            // Extract number from last reference
            $lastRefStr = $lastRef['display_reference'];
            if ($prefix && strpos($lastRefStr, $prefix . '-') === 0) {
                $numberPart = substr($lastRefStr, strlen($prefix) + 1);
            } else {
                $numberPart = $lastRefStr;
            }
            
            // Try to extract numeric part
            if (preg_match('/\d+/', $numberPart, $matches)) {
                $lastNumber = intval($matches[0]);
                $nextNumber = max($startNumber, $lastNumber + 1);
            }
        }
        
        // Format with leading zeros
        $formattedNumber = str_pad($nextNumber, $digits, '0', STR_PAD_LEFT);
        
        // Check if this reference already exists (safety check)
        $maxAttempts = 100;
        $attempts = 0;
        while ($attempts < $maxAttempts) {
            $displayRef = $prefix ? $prefix . '-' . $formattedNumber : $formattedNumber;
            
            $stmt = $db->prepare("
                SELECT id FROM employees 
                WHERE organisation_id = ? AND display_reference = ?
            ");
            $stmt->execute([$organisationId, $displayRef]);
            
            if (!$stmt->fetch()) {
                return $displayRef;
            }
            
            // If exists, try next number
            $nextNumber++;
            $formattedNumber = str_pad($nextNumber, $digits, '0', STR_PAD_LEFT);
            $attempts++;
        }
        
        // If we've exhausted attempts, return null
        return null;
    }
    
    /**
     * Generate random alphanumeric reference (e.g., SAMH-A1B2C3)
     */
    private static function generateRandomAlphanumeric($organisationId, $prefix, $length) {
        $db = getDbConnection();
        
        $chars = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ'; // Exclude similar-looking chars like I, O
        
        $maxAttempts = 100;
        $attempts = 0;
        
        while ($attempts < $maxAttempts) {
            // Generate random alphanumeric string
            $randomPart = '';
            for ($i = 0; $i < $length; $i++) {
                $randomPart .= $chars[random_int(0, strlen($chars) - 1)];
            }
            
            $displayRef = $prefix ? $prefix . '-' . $randomPart : $randomPart;
            
            // Check if it already exists
            $stmt = $db->prepare("
                SELECT id FROM employees 
                WHERE organisation_id = ? AND display_reference = ?
            ");
            $stmt->execute([$organisationId, $displayRef]);
            
            if (!$stmt->fetch()) {
                return $displayRef;
            }
            
            $attempts++;
        }
        
        // If we've exhausted attempts, return null
        return null;
    }
    
    /**
     * Validate display reference format
     */
    public static function validateDisplayReference($organisationId, $displayReference) {
        if (empty($displayReference)) {
            return ['valid' => false, 'message' => 'Display reference cannot be empty.'];
        }
        
        $db = getDbConnection();
        
        // Check if reference already exists in this organisation
        $stmt = $db->prepare("
            SELECT id FROM employees 
            WHERE organisation_id = ? AND display_reference = ?
        ");
        $stmt->execute([$organisationId, $displayReference]);
        if ($stmt->fetch()) {
            return ['valid' => false, 'message' => 'Display reference already exists in this organisation.'];
        }
        
        return ['valid' => true];
    }
}

