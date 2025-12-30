<?php
// #region agent log
$logData = ['location' => 'register.php:7', 'message' => 'Register page loaded', 'data' => ['method' => $_SERVER['REQUEST_METHOD'] ?? 'GET', 'timestamp' => date('Y-m-d H:i:s')], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'A'];
file_put_contents('/Users/wellis/Desktop/Cursor/digital-id/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND);
// #endregion

require_once dirname(__DIR__) . '/config/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // #region agent log
    $logData = ['location' => 'register.php:15', 'message' => 'POST request received', 'data' => ['post_keys' => array_keys($_POST), 'has_csrf' => isset($_POST['csrf_token']), 'timestamp' => date('Y-m-d H:i:s')], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'B'];
    file_put_contents('/Users/wellis/Desktop/Cursor/digital-id/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND);
    // #endregion
    
    if (!CSRF::validatePost()) {
        // #region agent log
        $logData = ['location' => 'register.php:19', 'message' => 'CSRF validation failed', 'data' => ['timestamp' => date('Y-m-d H:i:s')], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'B'];
        file_put_contents('/Users/wellis/Desktop/Cursor/digital-id/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND);
        // #endregion
        $error = 'Invalid security token. Please try again.';
    } else {
        // #region agent log
        $logData = ['location' => 'register.php:24', 'message' => 'CSRF validation passed', 'data' => ['timestamp' => date('Y-m-d H:i:s')], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'B'];
        file_put_contents('/Users/wellis/Desktop/Cursor/digital-id/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND);
        // #endregion
        
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $firstName = $_POST['first_name'] ?? '';
        $lastName = $_POST['last_name'] ?? '';
        
        // #region agent log
        $logData = ['location' => 'register.php:32', 'message' => 'Form data extracted', 'data' => ['email' => substr($email, 0, 10) . '...', 'has_password' => !empty($password), 'has_first_name' => !empty($firstName), 'has_last_name' => !empty($lastName), 'timestamp' => date('Y-m-d H:i:s')], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'C'];
        file_put_contents('/Users/wellis/Desktop/Cursor/digital-id/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND);
        // #endregion
        
        // Validate password confirmation
        if ($password !== $passwordConfirm) {
            // #region agent log
            $logData = ['location' => 'register.php:37', 'message' => 'Password mismatch', 'data' => ['timestamp' => date('Y-m-d H:i:s')], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'C'];
            file_put_contents('/Users/wellis/Desktop/Cursor/digital-id/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND);
            // #endregion
            $error = 'Passwords do not match.';
        } else {
            // Validate password strength
            $passwordErrors = Auth::validatePasswordStrength($password);
            if (!empty($passwordErrors)) {
                // #region agent log
                $logData = ['location' => 'register.php:44', 'message' => 'Password validation failed', 'data' => ['errors' => $passwordErrors, 'timestamp' => date('Y-m-d H:i:s')], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'C'];
                file_put_contents('/Users/wellis/Desktop/Cursor/digital-id/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND);
                // #endregion
                $error = implode(' ', $passwordErrors);
            } else {
                // #region agent log
                $logData = ['location' => 'register.php:48', 'message' => 'Calling Auth::register', 'data' => ['email' => substr($email, 0, 10) . '...', 'timestamp' => date('Y-m-d H:i:s')], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D'];
                file_put_contents('/Users/wellis/Desktop/Cursor/digital-id/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND);
                // #endregion
                
                try {
                    $result = Auth::register($email, $password, $firstName, $lastName);
                    
                    // #region agent log
                    $logData = ['location' => 'register.php:53', 'message' => 'Auth::register returned', 'data' => ['success' => $result['success'] ?? false, 'has_message' => isset($result['message']), 'timestamp' => date('Y-m-d H:i:s')], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'D'];
                    file_put_contents('/Users/wellis/Desktop/Cursor/digital-id/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND);
                    // #endregion
                    
                    if ($result['success']) {
                        $success = $result['message'];
                    } else {
                        $error = $result['message'];
                    }
                } catch (Exception $e) {
                    // #region agent log
                    $logData = ['location' => 'register.php:62', 'message' => 'Exception in Auth::register', 'data' => ['error' => $e->getMessage(), 'trace' => substr($e->getTraceAsString(), 0, 200), 'timestamp' => date('Y-m-d H:i:s')], 'timestamp' => time() * 1000, 'sessionId' => 'debug-session', 'runId' => 'run1', 'hypothesisId' => 'E'];
                    file_put_contents('/Users/wellis/Desktop/Cursor/digital-id/.cursor/debug.log', json_encode($logData) . "\n", FILE_APPEND);
                    // #endregion
                    $error = 'Registration failed: ' . $e->getMessage();
                }
            }
        }
    }
}

$pageTitle = 'Register';
include INCLUDES_PATH . '/header.php';
?>

<div class="card">
    <h1>Register</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <?php echo CSRF::tokenField(); ?>
        
        <div class="form-group">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" required>
        </div>
        
        <div class="form-group">
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
            <small>Your email must match an organisation that has been set up. If your organisation hasn't been configured yet, please <a href="<?php echo url('request-access.php'); ?>">request access</a> first.</small>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required 
                   minlength="<?php echo PASSWORD_MIN_LENGTH; ?>"
                   pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{<?php echo PASSWORD_MIN_LENGTH; ?>,}$"
                   oninput="validatePasswordStrength()">
            <div id="password-strength" style="margin-top: 0.5rem;"></div>
            <small id="password-requirements" style="display: block; margin-top: 0.5rem;">
                <strong>Password requirements:</strong>
                <ul style="margin: 0.5rem 0 0 1.5rem; padding: 0;">
                    <li id="req-length">At least <?php echo PASSWORD_MIN_LENGTH; ?> characters</li>
                    <li id="req-uppercase">One uppercase letter (A-Z)</li>
                    <li id="req-lowercase">One lowercase letter (a-z)</li>
                    <li id="req-number">One number (0-9)</li>
                    <li id="req-special">One special character (!@#$%^&*...)</li>
                </ul>
            </small>
        </div>
        
        <div class="form-group">
            <label for="password_confirm">Confirm Password</label>
            <input type="password" id="password_confirm" name="password_confirm" required 
                   oninput="validatePasswordMatch()">
            <div id="password-match" style="margin-top: 0.5rem;"></div>
        </div>
        
        <button type="submit" class="btn btn-primary" id="submit-btn">Register</button>
    </form>
    
    <p style="margin-top: 1rem;">
        <a href="<?php echo url('login.php'); ?>">Already have an account? Login</a>
    </p>
</div>

<style>
.password-requirement {
    transition: color 0.2s;
}

.password-requirement.met {
    color: #059669;
}

.password-requirement.met:before {
    content: "\f00c";
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    margin-right: 0.5rem;
    color: #059669;
}

.password-requirement.unmet {
    color: #6b7280;
}

.password-requirement.unmet:before {
    content: "\f00d";
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    margin-right: 0.5rem;
    color: #dc2626;
}

#password-strength {
    font-weight: 500;
    padding: 0.5rem;
    border-radius: 0;
    margin-top: 0.5rem;
}

#password-strength.weak {
    background-color: #fee2e2;
    color: #991b1b;
}

#password-strength.medium {
    background-color: #fef3c7;
    color: #92400e;
}

#password-strength.strong {
    background-color: #d1fae5;
    color: #065f46;
}

#password-match {
    font-weight: 500;
    padding: 0.5rem;
    border-radius: 0;
}

#password-match.match {
    background-color: #d1fae5;
    color: #065f46;
}

#password-match.mismatch {
    background-color: #fee2e2;
    color: #991b1b;
}
</style>

<script>
function validatePasswordStrength() {
    const password = document.getElementById('password').value;
    const strengthDiv = document.getElementById('password-strength');
    const submitBtn = document.getElementById('submit-btn');
    
    // Requirements
    const hasLength = password.length >= <?php echo PASSWORD_MIN_LENGTH; ?>;
    const hasUppercase = /[A-Z]/.test(password);
    const hasLowercase = /[a-z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    const hasSpecial = /[^A-Za-z0-9]/.test(password);
    
    // Update requirement indicators
    updateRequirement('req-length', hasLength);
    updateRequirement('req-uppercase', hasUppercase);
    updateRequirement('req-lowercase', hasLowercase);
    updateRequirement('req-number', hasNumber);
    updateRequirement('req-special', hasSpecial);
    
    // Calculate strength
    const requirementsMet = [hasLength, hasUppercase, hasLowercase, hasNumber, hasSpecial].filter(Boolean).length;
    const allMet = hasLength && hasUppercase && hasLowercase && hasNumber && hasSpecial;
    
    strengthDiv.className = '';
    strengthDiv.innerHTML = '';
    
    if (password.length === 0) {
        strengthDiv.style.display = 'none';
        submitBtn.disabled = false;
        return;
    }
    
    strengthDiv.style.display = 'block';
    
    if (allMet) {
        strengthDiv.className = 'strong';
        strengthDiv.innerHTML = '<i class="fas fa-check-circle"></i> Password strength: Strong';
        submitBtn.disabled = false;
    } else if (requirementsMet >= 3) {
        strengthDiv.className = 'medium';
        strengthDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Password strength: Medium - ' + (5 - requirementsMet) + ' requirement(s) remaining';
        submitBtn.disabled = true;
    } else {
        strengthDiv.className = 'weak';
        strengthDiv.innerHTML = '<i class="fas fa-times-circle"></i> Password strength: Weak - ' + (5 - requirementsMet) + ' requirement(s) remaining';
        submitBtn.disabled = true;
    }
    
    // Also check password match
    validatePasswordMatch();
}

function updateRequirement(id, met) {
    const element = document.getElementById(id);
    element.className = 'password-requirement ' + (met ? 'met' : 'unmet');
}

function validatePasswordMatch() {
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirm').value;
    const matchDiv = document.getElementById('password-match');
    const submitBtn = document.getElementById('submit-btn');
    
    if (passwordConfirm.length === 0) {
        matchDiv.style.display = 'none';
        return;
    }
    
    matchDiv.style.display = 'block';
    
    if (password === passwordConfirm) {
        matchDiv.className = 'match';
        matchDiv.innerHTML = '<i class="fas fa-check-circle"></i> Passwords match';
    } else {
        matchDiv.className = 'mismatch';
        matchDiv.innerHTML = '<i class="fas fa-times-circle"></i> Passwords do not match';
        submitBtn.disabled = true;
    }
}

// Initialise requirement indicators
document.addEventListener('DOMContentLoaded', function() {
    const requirements = ['req-length', 'req-uppercase', 'req-lowercase', 'req-number', 'req-special'];
    requirements.forEach(id => {
        document.getElementById(id).className = 'password-requirement unmet';
    });
});
</script>

<?php include INCLUDES_PATH . '/footer.php'; ?>

