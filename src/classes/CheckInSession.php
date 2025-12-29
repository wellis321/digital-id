<?php
/**
 * Check-In Session Model
 * Handles check-in session data and operations
 */

class CheckInSession {
    
    /**
     * Create a new check-in session
     */
    public static function create($organisationId, $sessionName, $sessionType, $startedBy, $locationName = null, $locationId = null, $metadata = null) {
        $db = getDbConnection();
        
        $stmt = $db->prepare("
            INSERT INTO check_in_sessions 
            (organisation_id, session_name, session_type, started_by, location_name, location_id, metadata)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $metadataJson = $metadata ? json_encode($metadata) : null;
        
        $stmt->execute([
            $organisationId,
            $sessionName,
            $sessionType,
            $startedBy,
            $locationName,
            $locationId,
            $metadataJson
        ]);
        
        $sessionId = $db->lastInsertId();
        
        return self::findById($sessionId);
    }
    
    /**
     * Find session by ID
     */
    public static function findById($sessionId) {
        $db = getDbConnection();
        
        $stmt = $db->prepare("
            SELECT s.*, 
                   u.first_name as started_by_first_name, 
                   u.last_name as started_by_last_name,
                   o.name as organisation_name
            FROM check_in_sessions s
            LEFT JOIN users u ON s.started_by = u.id
            LEFT JOIN organisations o ON s.organisation_id = o.id
            WHERE s.id = ?
        ");
        
        $stmt->execute([$sessionId]);
        $session = $stmt->fetch();
        
        if ($session && $session['metadata']) {
            $session['metadata'] = json_decode($session['metadata'], true);
        }
        
        return $session;
    }
    
    /**
     * Find active sessions for an organisation
     */
    public static function findActive($organisationId) {
        $db = getDbConnection();
        
        $stmt = $db->prepare("
            SELECT s.*, 
                   u.first_name as started_by_first_name, 
                   u.last_name as started_by_last_name,
                   o.name as organisation_name
            FROM check_in_sessions s
            LEFT JOIN users u ON s.started_by = u.id
            LEFT JOIN organisations o ON s.organisation_id = o.id
            WHERE s.organisation_id = ? AND s.ended_at IS NULL
            ORDER BY s.started_at DESC
        ");
        
        $stmt->execute([$organisationId]);
        $sessions = $stmt->fetchAll();
        
        foreach ($sessions as &$session) {
            if ($session['metadata']) {
                $session['metadata'] = json_decode($session['metadata'], true);
            }
        }
        
        return $sessions;
    }
    
    /**
     * Find all sessions for an organisation
     */
    public static function findAll($organisationId, $limit = 50, $offset = 0) {
        $db = getDbConnection();
        
        $stmt = $db->prepare("
            SELECT s.*, 
                   u.first_name as started_by_first_name, 
                   u.last_name as started_by_last_name,
                   o.name as organisation_name
            FROM check_in_sessions s
            LEFT JOIN users u ON s.started_by = u.id
            LEFT JOIN organisations o ON s.organisation_id = o.id
            WHERE s.organisation_id = ?
            ORDER BY s.started_at DESC
            LIMIT ? OFFSET ?
        ");
        
        $stmt->execute([$organisationId, $limit, $offset]);
        $sessions = $stmt->fetchAll();
        
        foreach ($sessions as &$session) {
            if ($session['metadata']) {
                $session['metadata'] = json_decode($session['metadata'], true);
            }
        }
        
        return $sessions;
    }
    
    /**
     * End a check-in session
     */
    public static function end($sessionId) {
        $db = getDbConnection();
        
        $stmt = $db->prepare("
            UPDATE check_in_sessions 
            SET ended_at = CURRENT_TIMESTAMP
            WHERE id = ? AND ended_at IS NULL
        ");
        
        $stmt->execute([$sessionId]);
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Update session metadata
     */
    public static function updateMetadata($sessionId, $metadata) {
        $db = getDbConnection();
        
        $metadataJson = json_encode($metadata);
        
        $stmt = $db->prepare("
            UPDATE check_in_sessions 
            SET metadata = ?
            WHERE id = ?
        ");
        
        $stmt->execute([$metadataJson, $sessionId]);
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Mark session as synced to Microsoft 365
     */
    public static function markSynced($sessionId, $sharepointListId = null, $teamsChannelId = null) {
        $db = getDbConnection();
        
        $stmt = $db->prepare("
            UPDATE check_in_sessions 
            SET microsoft_365_synced = TRUE,
                sharepoint_list_id = COALESCE(?, sharepoint_list_id),
                teams_channel_id = COALESCE(?, teams_channel_id)
            WHERE id = ?
        ");
        
        $stmt->execute([$sharepointListId, $teamsChannelId, $sessionId]);
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Get check-in count for a session
     */
    public static function getCheckInCount($sessionId) {
        $db = getDbConnection();
        
        $stmt = $db->prepare("
            SELECT COUNT(*) as count
            FROM check_ins
            WHERE session_id = ?
        ");
        
        $stmt->execute([$sessionId]);
        $result = $stmt->fetch();
        
        return $result ? (int)$result['count'] : 0;
    }
}

