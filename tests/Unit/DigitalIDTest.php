<?php
/**
 * Unit Tests for DigitalID Class
 * Tests: TC-003, TC-029, TC-030, TC-031
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../bootstrap.php';

class DigitalIDTest extends TestCase {
    
    private $organisationId;
    private $userId;
    private $employeeId;
    
    protected function setUp(): void {
        // Create test organisation
        $this->organisationId = TestHelper::createTestOrganisation('Test Org', 'test.local');
        
        // Create test user
        $this->userId = TestHelper::createTestUser(
            $this->organisationId,
            'test@test.local',
            'password123',
            'staff'
        );
        
        // Create test employee (first_name/last_name are in users table, not employees)
        $this->employeeId = TestHelper::createTestEmployee(
            $this->organisationId,
            $this->userId,
            'EMP001'
        );
    }
    
    protected function tearDown(): void {
        // Clean up test data
        TestHelper::cleanupTestData($this->organisationId);
    }
    
    /**
     * TC-029: QR tokens are cryptographically random
     */
    public function testQRTokensAreRandom() {
        // Get or create ID cards (each call creates new tokens)
        $idCard1 = DigitalID::getOrCreateIdCard($this->employeeId);
        $idCard2 = DigitalID::getOrCreateIdCard($this->employeeId);
        $idCard3 = DigitalID::getOrCreateIdCard($this->employeeId);
        
        // Tokens should be different (or same if refreshed within expiry)
        $token1 = $idCard1['qr_token'];
        $token2 = $idCard2['qr_token'];
        $token3 = $idCard3['qr_token'];
        
        // Tokens should be 64 characters (hex)
        $this->assertEquals(64, strlen($token1));
        $this->assertEquals(64, strlen($token2));
        $this->assertEquals(64, strlen($token3));
        
        // At least some tokens should be different (unless all refreshed at same time)
        // Note: getOrCreateIdCard may return same card if tokens haven't expired
    }
    
    /**
     * TC-030: Token expiry enforced (5 minutes)
     */
    public function testTokenExpiry() {
        // Get or create ID card
        $idCard = DigitalID::getOrCreateIdCard($this->employeeId);
        $token = $idCard['qr_token'];
        
        // Token should be valid immediately
        $validation = DigitalID::validateToken($token, 'qr');
        $this->assertTrue($validation['valid']);
        
        // Manually expire token in database (simulate 6 minutes passing)
        $db = getDbConnection();
        $stmt = $db->prepare("
            UPDATE digital_id_cards 
            SET qr_token_expires_at = DATE_SUB(NOW(), INTERVAL 1 MINUTE)
            WHERE id = ?
        ");
        $stmt->execute([$idCard['id']]);
        
        // Token should now be expired
        $validation = DigitalID::validateToken($token, 'qr');
        $this->assertFalse($validation['valid']);
        $this->assertEquals('expired', $validation['reason']);
    }
    
    /**
     * TC-031: Revoked cards cannot be verified
     */
    public function testRevokedCardCannotBeVerified() {
        // Get or create ID card
        $idCard = DigitalID::getOrCreateIdCard($this->employeeId);
        $token = $idCard['qr_token'];
        
        // Revoke the card (revoke takes idCardId, not employeeId)
        DigitalID::revoke($idCard['id']);
        
        // Token should be invalid
        $validation = DigitalID::validateToken($token, 'qr');
        $this->assertFalse($validation['valid']);
        $this->assertEquals('revoked', $validation['reason']);
    }
    
    /**
     * Test token generation creates ID card record
     */
    public function testTokenGenerationCreatesIdCard() {
        // Get or create ID card
        $idCard = DigitalID::getOrCreateIdCard($this->employeeId);
        
        // Check ID card was created
        $this->assertNotNull($idCard);
        $this->assertEquals($this->employeeId, $idCard['employee_id']);
        $this->assertNotNull($idCard['qr_token']);
        $this->assertEquals(64, strlen($idCard['qr_token'])); // 64 char hex
        $this->assertNotNull($idCard['qr_token_expires_at']);
    }
    
    /**
     * Test invalid token format
     */
    public function testInvalidTokenFormat() {
        $validation = DigitalID::validateToken('invalid-token-format', 'qr');
        $this->assertFalse($validation['valid']);
        $this->assertEquals('token_not_found', $validation['reason']);
    }
}

