<?php
/**
 * Digital ID Card Class
 * Handles ID card generation, token management, and validation
 */

class DigitalID {
    
    /**
     * Generate or get active ID card for employee
     */
    public static function getOrCreateIdCard($employeeId) {
        $db = getDbConnection();
        
        // Check for active, non-revoked ID card
        $stmt = $db->prepare("
            SELECT * FROM digital_id_cards 
            WHERE employee_id = ? 
            AND is_revoked = FALSE 
            AND (expires_at IS NULL OR expires_at > NOW())
            ORDER BY issued_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$employeeId]);
        $idCard = $stmt->fetch();
        
        if ($idCard) {
            // Refresh tokens if expired
            if (self::needsTokenRefresh($idCard)) {
                return self::refreshTokens($idCard['id']);
            }
            return $idCard;
        }
        
        // Create new ID card
        return self::createIdCard($employeeId);
    }
    
    /**
     * Create a new ID card
     */
    public static function createIdCard($employeeId) {
        $db = getDbConnection();
        
        try {
            $db->beginTransaction();
            
            // Revoke any existing active cards
            $stmt = $db->prepare("
                UPDATE digital_id_cards 
                SET is_revoked = TRUE, revoked_at = NOW() 
                WHERE employee_id = ? AND is_revoked = FALSE
            ");
            $stmt->execute([$employeeId]);
            
            // Generate tokens
            $qrToken = self::generateToken();
            $nfcToken = self::generateToken();
            
            $qrExpires = date('Y-m-d H:i:s', strtotime('+' . QR_TOKEN_EXPIRY_MINUTES . ' minutes'));
            $nfcExpires = date('Y-m-d H:i:s', strtotime('+' . NFC_TOKEN_EXPIRY_MINUTES . ' minutes'));
            $cardExpires = date('Y-m-d H:i:s', strtotime('+' . ID_CARD_EXPIRY_DAYS . ' days'));
            
            // Create new ID card
            $stmt = $db->prepare("
                INSERT INTO digital_id_cards 
                (employee_id, qr_token, nfc_token, qr_token_expires_at, nfc_token_expires_at, expires_at)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $employeeId,
                $qrToken,
                $nfcToken,
                $qrExpires,
                $nfcExpires,
                $cardExpires
            ]);
            
            $idCardId = $db->lastInsertId();
            
            $db->commit();
            
            return self::findById($idCardId);
        } catch (Exception $e) {
            $db->rollBack();
            return null;
        }
    }
    
    /**
     * Find ID card by ID
     */
    public static function findById($id) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT dic.*, e.employee_reference, e.photo_path, 
                   u.first_name, u.last_name, o.name as organisation_name
            FROM digital_id_cards dic
            JOIN employees e ON dic.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            JOIN organisations o ON e.organisation_id = o.id
            WHERE dic.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Find ID card by QR token
     */
    public static function findByQrToken($token) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT dic.*, e.employee_reference, e.photo_path, 
                   u.first_name, u.last_name, o.name as organisation_name
            FROM digital_id_cards dic
            JOIN employees e ON dic.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            JOIN organisations o ON e.organisation_id = o.id
            WHERE dic.qr_token = ?
        ");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }
    
    /**
     * Find ID card by NFC token
     */
    public static function findByNfcToken($token) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            SELECT dic.*, e.employee_reference, e.photo_path, 
                   u.first_name, u.last_name, o.name as organisation_name
            FROM digital_id_cards dic
            JOIN employees e ON dic.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            JOIN organisations o ON e.organisation_id = o.id
            WHERE dic.nfc_token = ?
        ");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }
    
    /**
     * Refresh tokens for an ID card
     */
    public static function refreshTokens($idCardId) {
        $db = getDbConnection();
        
        $qrToken = self::generateToken();
        $nfcToken = self::generateToken();
        
        $qrExpires = date('Y-m-d H:i:s', strtotime('+' . QR_TOKEN_EXPIRY_MINUTES . ' minutes'));
        $nfcExpires = date('Y-m-d H:i:s', strtotime('+' . NFC_TOKEN_EXPIRY_MINUTES . ' minutes'));
        
        $stmt = $db->prepare("
            UPDATE digital_id_cards 
            SET qr_token = ?, nfc_token = ?, 
                qr_token_expires_at = ?, nfc_token_expires_at = ?
            WHERE id = ?
        ");
        $stmt->execute([$qrToken, $nfcToken, $qrExpires, $nfcExpires, $idCardId]);
        
        return self::findById($idCardId);
    }
    
    /**
     * Check if tokens need refresh
     */
    private static function needsTokenRefresh($idCard) {
        $now = time();
        $qrExpires = strtotime($idCard['qr_token_expires_at']);
        $nfcExpires = strtotime($idCard['nfc_token_expires_at']);
        
        // Refresh if tokens expire in less than 1 minute
        return ($qrExpires - $now < 60) || ($nfcExpires - $now < 60);
    }
    
    /**
     * Revoke ID card
     */
    public static function revoke($idCardId, $revokedBy = null) {
        $db = getDbConnection();
        $stmt = $db->prepare("
            UPDATE digital_id_cards 
            SET is_revoked = TRUE, revoked_at = NOW(), revoked_by = ?
            WHERE id = ?
        ");
        $stmt->execute([$revokedBy, $idCardId]);
        return ['success' => true];
    }
    
    /**
     * Validate token (QR, NFC, or BLE)
     */
    public static function validateToken($token, $type = 'qr') {
        // BLE uses the same token system as NFC (both are proximity-based supplementary methods)
        $isProximityType = ($type === 'nfc' || $type === 'ble');
        $idCard = $isProximityType ? self::findByNfcToken($token) : self::findByQrToken($token);
        
        if (!$idCard) {
            return ['valid' => false, 'reason' => 'token_not_found'];
        }
        
        if ($idCard['is_revoked']) {
            return ['valid' => false, 'reason' => 'revoked'];
        }
        
        $expiresField = $isProximityType ? 'nfc_token_expires_at' : 'qr_token_expires_at';
        if (strtotime($idCard[$expiresField]) < time()) {
            return ['valid' => false, 'reason' => 'expired'];
        }
        
        if (strtotime($idCard['expires_at']) < time()) {
            return ['valid' => false, 'reason' => 'card_expired'];
        }
        
        return ['valid' => true, 'id_card' => $idCard];
    }
    
    /**
     * Generate secure random token
     */
    private static function generateToken() {
        return bin2hex(random_bytes(32)); // 64 character hex string
    }
}

