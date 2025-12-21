<?php
/**
 * User Import Class
 * Handles bulk import of users from CSV or JSON files
 */

class UserImport {
    
    /**
     * Import users from CSV file
     * 
     * @param int $organisationId
     * @param string $filePath
     * @param bool $createEmployees
     * @return array ['success' => bool, 'users_created' => int, 'users_updated' => int, 'users_skipped' => int, 'employees_created' => int, 'warnings' => array]
     */
    public static function importFromCsv($organisationId, $filePath, $createEmployees = false) {
        $warnings = [];
        $usersCreated = 0;
        $usersUpdated = 0;
        $usersSkipped = 0;
        $employeesCreated = 0;
        
        if (!file_exists($filePath)) {
            return ['success' => false, 'message' => 'File not found', 'users_created' => 0, 'users_updated' => 0, 'users_skipped' => 0, 'employees_created' => 0, 'warnings' => []];
        }
        
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return ['success' => false, 'message' => 'Could not open file', 'users_created' => 0, 'users_updated' => 0, 'users_skipped' => 0, 'employees_created' => 0, 'warnings' => []];
        }
        
        // Read header
        $header = fgetcsv($handle);
        if (!$header || !in_array('email', $header) || !in_array('first_name', $header) || !in_array('last_name', $header)) {
            fclose($handle);
            return ['success' => false, 'message' => 'Invalid CSV format: missing required columns (email, first_name, last_name)', 'users_created' => 0, 'users_updated' => 0, 'users_skipped' => 0, 'employees_created' => 0, 'warnings' => []];
        }
        
        $db = getDbConnection();
        $lineNumber = 1;
        
        while (($data = fgetcsv($handle)) !== false) {
            $lineNumber++;
            
            if (count($data) !== count($header)) {
                $warnings[] = "Line {$lineNumber}: Column count mismatch, skipping";
                $usersSkipped++;
                continue;
            }
            
            $row = array_combine($header, $data);
            $email = trim($row['email'] ?? '');
            $firstName = trim($row['first_name'] ?? '');
            $lastName = trim($row['last_name'] ?? '');
            $employeeReference = trim($row['employee_reference'] ?? '');
            $password = trim($row['password'] ?? '');
            
            if (empty($email) || empty($firstName) || empty($lastName)) {
                $warnings[] = "Line {$lineNumber}: Missing required fields (email, first_name, or last_name), skipping";
                $usersSkipped++;
                continue;
            }
            
            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $warnings[] = "Line {$lineNumber}: Invalid email address '{$email}', skipping";
                $usersSkipped++;
                continue;
            }
            
            // Check if user exists
            $stmt = $db->prepare("SELECT id, organisation_id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $existingUser = $stmt->fetch();
            
            if ($existingUser) {
                // User exists - check if they belong to this organisation
                if ($existingUser['organisation_id'] == $organisationId) {
                    // Update existing user (only name if different)
                    $updateStmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ? WHERE id = ?");
                    $updateStmt->execute([$firstName, $lastName, $existingUser['id']]);
                    $usersUpdated++;
                    
                    // Create employee profile if requested and doesn't exist
                    if ($createEmployees && !empty($employeeReference)) {
                        $empCheck = $db->prepare("SELECT id FROM employees WHERE user_id = ?");
                        $empCheck->execute([$existingUser['id']]);
                        if (!$empCheck->fetch()) {
                            $empResult = Employee::create($existingUser['id'], $organisationId, $employeeReference);
                            if ($empResult['success']) {
                                $employeesCreated++;
                            } else {
                                $warnings[] = "Line {$lineNumber}: Failed to create employee profile: " . $empResult['message'];
                            }
                        }
                    }
                } else {
                    $warnings[] = "Line {$lineNumber}: User '{$email}' already exists in another organisation, skipping";
                    $usersSkipped++;
                }
            } else {
                // Create new user
                // Generate password if not provided
                if (empty($password)) {
                    $password = self::generateTemporaryPassword();
                }
                
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                try {
                    $stmt = $db->prepare("
                        INSERT INTO users (email, first_name, last_name, password_hash, organisation_id, email_verified, is_active)
                        VALUES (?, ?, ?, ?, ?, TRUE, TRUE)
                    ");
                    $stmt->execute([$email, $firstName, $lastName, $hashedPassword, $organisationId]);
                    $userId = $db->lastInsertId();
                    $usersCreated++;
                    
                    // Send welcome email with temporary password
                    self::sendWelcomeEmail($email, $firstName, $password);
                    
                    // Create employee profile if requested
                    if ($createEmployees && !empty($employeeReference)) {
                        $empResult = Employee::create($userId, $organisationId, $employeeReference);
                        if ($empResult['success']) {
                            $employeesCreated++;
                        } else {
                            $warnings[] = "Line {$lineNumber}: User created but failed to create employee profile: " . $empResult['message'];
                        }
                    }
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                        $warnings[] = "Line {$lineNumber}: User '{$email}' already exists, skipping";
                        $usersSkipped++;
                    } else {
                        $warnings[] = "Line {$lineNumber}: Failed to create user '{$email}': " . $e->getMessage();
                        $usersSkipped++;
                    }
                }
            }
        }
        
        fclose($handle);
        
        return [
            'success' => true,
            'users_created' => $usersCreated,
            'users_updated' => $usersUpdated,
            'users_skipped' => $usersSkipped,
            'employees_created' => $employeesCreated,
            'warnings' => $warnings
        ];
    }
    
    /**
     * Import users from JSON file
     * 
     * @param int $organisationId
     * @param string $filePath
     * @param bool $createEmployees
     * @return array ['success' => bool, 'users_created' => int, 'users_updated' => int, 'users_skipped' => int, 'employees_created' => int, 'warnings' => array]
     */
    public static function importFromJson($organisationId, $filePath, $createEmployees = false) {
        $warnings = [];
        $usersCreated = 0;
        $usersUpdated = 0;
        $usersSkipped = 0;
        $employeesCreated = 0;
        
        if (!file_exists($filePath)) {
            return ['success' => false, 'message' => 'File not found', 'users_created' => 0, 'users_updated' => 0, 'users_skipped' => 0, 'employees_created' => 0, 'warnings' => []];
        }
        
        $content = file_get_contents($filePath);
        $data = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'message' => 'Invalid JSON: ' . json_last_error_msg(), 'users_created' => 0, 'users_updated' => 0, 'users_skipped' => 0, 'employees_created' => 0, 'warnings' => []];
        }
        
        // Support both formats: array of users or object with users array
        $users = [];
        if (isset($data['users']) && is_array($data['users'])) {
            $users = $data['users'];
        } elseif (is_array($data) && isset($data[0])) {
            // Assume it's a direct array of users
            $users = $data;
        } else {
            return ['success' => false, 'message' => 'Invalid JSON format: expected "users" array or array of user objects', 'users_created' => 0, 'users_updated' => 0, 'users_skipped' => 0, 'employees_created' => 0, 'warnings' => []];
        }
        
        $db = getDbConnection();
        
        foreach ($users as $index => $userData) {
            $email = trim($userData['email'] ?? '');
            $firstName = trim($userData['first_name'] ?? '');
            $lastName = trim($userData['last_name'] ?? '');
            $employeeReference = trim($userData['employee_reference'] ?? '');
            $password = trim($userData['password'] ?? '');
            
            if (empty($email) || empty($firstName) || empty($lastName)) {
                $warnings[] = "User " . ($index + 1) . ": Missing required fields (email, first_name, or last_name), skipping";
                $usersSkipped++;
                continue;
            }
            
            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $warnings[] = "User " . ($index + 1) . ": Invalid email address '{$email}', skipping";
                $usersSkipped++;
                continue;
            }
            
            // Check if user exists
            $stmt = $db->prepare("SELECT id, organisation_id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $existingUser = $stmt->fetch();
            
            if ($existingUser) {
                // User exists - check if they belong to this organisation
                if ($existingUser['organisation_id'] == $organisationId) {
                    // Update existing user
                    $updateStmt = $db->prepare("UPDATE users SET first_name = ?, last_name = ? WHERE id = ?");
                    $updateStmt->execute([$firstName, $lastName, $existingUser['id']]);
                    $usersUpdated++;
                    
                    // Create employee profile if requested and doesn't exist
                    if ($createEmployees && !empty($employeeReference)) {
                        $empCheck = $db->prepare("SELECT id FROM employees WHERE user_id = ?");
                        $empCheck->execute([$existingUser['id']]);
                        if (!$empCheck->fetch()) {
                            $empResult = Employee::create($existingUser['id'], $organisationId, $employeeReference);
                            if ($empResult['success']) {
                                $employeesCreated++;
                            } else {
                                $warnings[] = "User " . ($index + 1) . ": Failed to create employee profile: " . $empResult['message'];
                            }
                        }
                    }
                } else {
                    $warnings[] = "User " . ($index + 1) . ": User '{$email}' already exists in another organisation, skipping";
                    $usersSkipped++;
                }
            } else {
                // Create new user
                // Generate password if not provided
                if (empty($password)) {
                    $password = self::generateTemporaryPassword();
                }
                
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                try {
                    $stmt = $db->prepare("
                        INSERT INTO users (email, first_name, last_name, password_hash, organisation_id, email_verified, is_active)
                        VALUES (?, ?, ?, ?, ?, TRUE, TRUE)
                    ");
                    $stmt->execute([$email, $firstName, $lastName, $hashedPassword, $organisationId]);
                    $userId = $db->lastInsertId();
                    $usersCreated++;
                    
                    // Send welcome email with temporary password
                    self::sendWelcomeEmail($email, $firstName, $password);
                    
                    // Create employee profile if requested
                    if ($createEmployees && !empty($employeeReference)) {
                        $empResult = Employee::create($userId, $organisationId, $employeeReference);
                        if ($empResult['success']) {
                            $employeesCreated++;
                        } else {
                            $warnings[] = "User " . ($index + 1) . ": User created but failed to create employee profile: " . $empResult['message'];
                        }
                    }
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                        $warnings[] = "User " . ($index + 1) . ": User '{$email}' already exists, skipping";
                        $usersSkipped++;
                    } else {
                        $warnings[] = "User " . ($index + 1) . ": Failed to create user '{$email}': " . $e->getMessage();
                        $usersSkipped++;
                    }
                }
            }
        }
        
        return [
            'success' => true,
            'users_created' => $usersCreated,
            'users_updated' => $usersUpdated,
            'users_skipped' => $usersSkipped,
            'employees_created' => $employeesCreated,
            'warnings' => $warnings
        ];
    }
    
    /**
     * Generate temporary password
     */
    private static function generateTemporaryPassword() {
        // Generate a secure random password
        $length = 12;
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }
    
    /**
     * Send welcome email with temporary password
     */
    private static function sendWelcomeEmail($email, $firstName, $temporaryPassword) {
        $subject = 'Welcome to ' . APP_NAME;
        $message = "Hello {$firstName},\n\n";
        $message .= "Your account has been created for " . APP_NAME . ".\n\n";
        $message .= "Your temporary password is: {$temporaryPassword}\n\n";
        $message .= "Please log in and change your password immediately.\n\n";
        $message .= "Login URL: " . APP_URL . url('login.php') . "\n\n";
        $message .= "If you did not expect this email, please contact your organisation administrator.\n\n";
        $message .= "Best regards,\n" . APP_NAME . " Team";
        
        Email::send($email, $subject, $message);
    }
}



