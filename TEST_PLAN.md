# Digital ID Application - Comprehensive Test Plan

## Overview
This test plan covers all features promoted in the Digital ID application to ensure they work as advertised. Each section includes test cases with steps, expected results, and acceptance criteria.

## Test Environment Setup

### Database Configuration
- **Test Database**: Separate test database with sample data
- **Database Name**: `digital_ids_test` (or similar)
- **Required Tables**: All tables from `sql/schema.sql` and migrations
- **Test Data**: 
  - At least 2 organisations with different domains
  - Multiple employees per organisation
  - Active and inactive users
  - Various verification logs for testing filters

### Test Organisations
- **Organisation A**:
  - Domain: `orga.test`
  - Name: "Test Organisation A"
  - At least 5 employees
  - 1 organisation admin user
  - 4 regular staff users
- **Organisation B**:
  - Domain: `orgb.test`
  - Name: "Test Organisation B"
  - At least 3 employees
  - 1 organisation admin user
  - 2 regular staff users

### Test Users
- **Superadmin User**:
  - Email: `superadmin@test.local`
  - Password: (use secure test password)
  - Role: `superadmin`
  - Can access all organisations
- **Organisation Admin Users**:
  - Org A Admin: `admina@orga.test`
  - Org B Admin: `adminb@orgb.test`
  - Role: `organisation_admin`
  - Can only access their own organisation
- **Regular Staff Users**:
  - Org A Staff: `staff1@orga.test`, `staff2@orga.test`, etc.
  - Org B Staff: `staff1@orgb.test`, `staff2@orgb.test`
  - Role: `staff`
  - Can only view their own ID card

### Test Devices
- **Desktop Browser**: Chrome 120+ or Firefox 120+ on Windows/Mac/Linux
- **Mobile Device**: Android 10+ with NFC support (for NFC testing)
- **iOS Device**: iOS 14+ (for PWA testing)
- **Network**: Both localhost and production/staging environments

### Test Data Requirements
- **Employee Records**: 
  - Employees with approved photos
  - Employees with pending photos
  - Employees with no photos
  - Active and inactive employees
  - Employees with different employee reference formats
- **ID Cards**:
  - Active ID cards
  - Revoked ID cards
  - Expired ID cards (if testing expiry)
- **Verification Logs**:
  - Logs from last 30 days (for default filter testing)
  - Logs from different verification types (QR, NFC, visual)
  - Logs from building access API (with location/device)
  - Logs from different employees
  - Logs from different organisations (for isolation testing)

---

## 1. Core Features

### 1.1 Digital ID Card Display
**Feature**: Users can view their digital ID card with photo, details, and QR code

**Test Cases**:

1. **TC-001**: View ID card as logged-in user
   - **Priority**: P0 - Critical
   - **Pre-conditions**:
     - User account exists and is verified
     - Employee record exists for the user
     - ID card is active (not revoked)
     - User is logged in
   - **Test Data**:
     - User: `staff1@orga.test`
     - Employee reference: `EMP001`
     - Employee has approved photo
   - **Detailed Steps**:
     1. Navigate to `{APP_URL}/login.php`
     2. Enter email: `staff1@orga.test`
     3. Enter password: (test password)
     4. Click "Login" button
     5. Verify redirect to homepage or dashboard
     6. Navigate to `{APP_URL}/id-card.php` (or click "View ID Card" link)
     7. Wait for page to load completely
   - **Expected Results**:
     - Page loads without errors
     - ID card container is visible
     - Employee photo displays (if uploaded and approved)
     - Employee full name displays: "First Last"
     - Employee reference displays: "EMP001" (or configured format)
     - Organisation name displays: "Test Organisation A"
     - QR code image is visible and rendered
     - QR code is a valid image (not broken)
     - Page title shows "Digital ID Card"
     - No JavaScript errors in console
   - **Post-conditions**:
     - QR token is generated and stored in database
     - `qr_token_expires_at` is set to 5 minutes from now
     - Verification log entry created (if auto-logging enabled)
   - **Acceptance Criteria**:
     - All elements visible and correctly formatted
     - QR code is scannable (test with phone camera)
     - No layout issues or overlapping elements
     - Page loads in under 2 seconds
   - **Edge Cases to Test**:
     - Employee with no photo (placeholder should show)
     - Employee with pending photo (should show pending status)
     - Very long employee reference (should not break layout)
     - Very long organisation name (should not break layout)
   - **Error Scenarios**:
     - User without employee record (should show appropriate message)
     - Revoked ID card (should show revocation message)
     - Expired ID card (should show expiry message)

2. **TC-002**: QR code displays and is scannable
   - **Priority**: P0 - Critical
   - **Pre-conditions**:
     - User is logged in and viewing their ID card
     - ID card page has loaded completely
     - QR code is visible on screen
   - **Test Data**:
     - User: `staff1@orga.test`
     - QR token: (generated from TC-001)
   - **Detailed Steps**:
     1. Complete TC-001 to view ID card
     2. Locate QR code on the ID card
     3. Open phone camera app (iOS Camera or Android Camera)
     4. Point camera at QR code on screen
     5. Wait for QR code to be detected (usually 1-2 seconds)
     6. Tap notification or link that appears
     7. Verify browser opens to verification page
   - **Expected Results**:
     - QR code is detected by phone camera
     - QR code contains a URL in format: `{APP_URL}/verify.php?token={64-char-hex}&type=qr`
     - Browser opens to verification page
     - Verification page shows employee details
     - Verification succeeds (green success message)
     - Employee photo, name, and reference are displayed
   - **Post-conditions**:
     - Verification log entry created with:
       - `verification_type` = 'qr'
       - `verification_result` = 'success'
       - `verified_at` = current timestamp
       - `employee_id` = correct employee ID
   - **Acceptance Criteria**:
     - QR code scans successfully on first attempt
     - QR code is clear and readable (minimum 200x200px)
     - Verification page loads correctly
     - Employee information matches ID card
   - **Edge Cases to Test**:
     - QR code at different screen sizes (mobile, tablet, desktop)
     - QR code with screen brightness variations
     - QR code with screen glare or reflections
     - Scanning from different angles
   - **Error Scenarios**:
     - Expired token (should show expiry message)
     - Invalid token format (should show error)
     - Network error during verification (should show appropriate message)

3. **TC-003**: QR code token expiry (5 minutes)
   - **Priority**: P0 - Critical
   - **Pre-conditions**:
     - User is logged in and viewing their ID card
     - QR code is visible and contains a valid token
     - Current time is recorded
   - **Test Data**:
     - User: `staff1@orga.test`
     - Initial QR token: (captured from TC-001)
     - Wait time: 6 minutes (1 minute past expiry)
   - **Detailed Steps**:
     1. Complete TC-001 to view ID card
     2. Note the current time: `{start_time}`
     3. Capture the QR code URL/token (screenshot or copy URL)
     4. Wait exactly 6 minutes (use timer)
     5. After 6 minutes, scan the QR code with phone camera
     6. Or manually navigate to: `{APP_URL}/verify.php?token={captured_token}&type=qr`
     7. Observe the verification result
   - **Expected Results**:
     - Verification fails
     - Error message displayed: "Token has expired" or similar
     - Error type: `expired` or `token_expired`
     - HTTP status: 200 (or appropriate error code)
     - Verification log entry created with:
       - `verification_result` = 'failed'
       - `failure_reason` = 'expired' or similar
   - **Post-conditions**:
     - Verification log entry created for failed attempt
     - Token is no longer valid for verification
     - User can refresh ID card page to get new token
   - **Acceptance Criteria**:
     - Token expires exactly at 5 minutes (not before, not after)
     - Error message clearly indicates token expiry
     - User can get new token by refreshing ID card page
   - **Edge Cases to Test**:
     - Token expires at exactly 5 minutes (boundary test)
     - Token still valid at 4 minutes 59 seconds
     - Token expired at 5 minutes 1 second
     - Multiple expired token attempts (should all fail)
   - **Error Scenarios**:
     - System clock changes during test (should handle gracefully)
     - Token used exactly at expiry boundary

4. **TC-004**: ID card works offline (PWA)
   - Steps: Install PWA → View ID card → Go offline → Refresh page
   - Expected: ID card still displays when offline
   - Acceptance: Card visible without internet connection

### 1.2 Progressive Web App (PWA)
**Feature**: Install as app on phone, works offline, feels like native app

**Test Cases**:
5. **TC-005**: PWA installation prompt appears
   - Steps: Visit site on mobile device
   - Expected: Browser shows "Install App" prompt
   - Acceptance: Installation option available

6. **TC-006**: PWA installs successfully
   - Steps: Click install → Add to home screen
   - Expected: App icon appears on home screen
   - Acceptance: App launches from home screen icon

7. **TC-007**: PWA works offline
   - Steps: Install PWA → View ID card → Enable airplane mode → Reload
   - Expected: ID card still displays
   - Acceptance: Core functionality works without internet

8. **TC-008**: PWA service worker registration
   - Steps: Open browser console → Check service worker status
   - Expected: Service worker registered and active
   - Acceptance: Console shows successful registration

### 1.3 Verification Methods

#### 1.3.1 Visual Verification
**Feature**: Visual comparison of photo and details

**Test Cases**:
9. **TC-009**: Public verification page accessible
   - **Priority**: P0 - Critical
   - **Pre-conditions**:
     - User is NOT logged in (or use incognito/private browsing)
     - No authentication cookies present
     - Verification page exists at correct path
   - **Test Data**:
     - URL: `{APP_URL}/verify.php`
     - No authentication required
   - **Detailed Steps**:
     1. Open browser in incognito/private mode (or clear cookies)
     2. Navigate to `{APP_URL}/verify.php`
     3. Wait for page to load
     4. Check page content
     5. Check browser console for errors
   - **Expected Results**:
     - Page loads successfully (HTTP 200)
     - No redirect to login page
     - Verification form is visible
     - "Verify by Employee Reference" section is visible
     - "Verify by QR Code" instructions are visible
     - No authentication errors
     - Page title: "Verify Digital ID"
   - **Post-conditions**:
     - Page is accessible without authentication
     - User can enter employee reference and search
     - User can verify using QR code token
   - **Acceptance Criteria**:
     - Page accessible to public (no login required)
     - All verification functionality available
     - No security warnings or errors
   - **Edge Cases to Test**:
     - Access from different IP addresses
     - Access with different user agents
     - Access with cookies disabled
   - **Error Scenarios**:
     - Page not found (404) - should not occur
     - Server error (500) - should not occur

10. **TC-010**: Manual lookup by employee reference
    - **Priority**: P0 - Critical
    - **Pre-conditions**:
     - Verification page is accessible (TC-009)
     - Employee record exists with known reference
     - Employee has approved photo
     - Employee is active
   - **Test Data**:
     - Organisation: "Test Organisation A" (ID: 1)
     - Employee reference: `EMP001`
     - Employee name: "John Smith"
   - **Detailed Steps**:
     1. Navigate to `{APP_URL}/verify.php` (without login)
     2. Locate "Verify by Employee Reference" section
     3. Select organisation from dropdown: "Test Organisation A"
     4. Enter employee reference: `EMP001`
     5. Click "Verify" or "Search" button
     6. Wait for verification result
   - **Expected Results**:
     - Form submits successfully
     - Verification succeeds
     - Employee details displayed:
       - Full name: "John Smith"
       - Employee reference: `EMP001`
       - Organisation name: "Test Organisation A"
       - Employee photo (if approved)
     - Green success message: "Verification Successful" or similar
     - ID card preview displayed
     - Verification timestamp shown
   - **Post-conditions**:
     - Verification log entry created:
       - `verification_type` = 'visual' or 'reference'
       - `verification_result` = 'success'
       - `employee_id` = correct employee ID
       - `organisation_id` = correct organisation ID
   - **Acceptance Criteria**:
     - Correct employee information shown
     - Photo displays if approved
     - All employee details match database
     - Verification logged correctly
   - **Edge Cases to Test**:
     - Employee reference with special characters
     - Employee reference with leading/trailing spaces (should trim)
     - Employee reference in different case (should match)
     - Employee with no photo (should show placeholder)
     - Employee with pending photo (should show appropriate status)
     - Inactive employee (should show inactive status)
   - **Error Scenarios**:
     - Invalid employee reference (should show "Employee not found")
     - Employee from different organisation (should not be found)
     - Empty employee reference (should show validation error)
     - CSRF token missing (should show security error)

#### 1.3.2 QR Code Verification
**Feature**: Scan QR code for instant verification

**Test Cases**:
11. **TC-011**: QR code verification succeeds with valid token
    - Steps: Scan QR code from ID card → Verify token
    - Expected: Verification succeeds, employee details shown
    - Acceptance: Green success message, employee info displayed

12. **TC-012**: QR code verification fails with expired token
    - Steps: Wait 6 minutes after viewing ID card → Scan QR code
    - Expected: Verification fails with expiry message
    - Acceptance: Error message indicates token expired

13. **TC-013**: QR code verification fails with revoked card
    - Steps: Admin revokes ID card → User scans QR code
    - Expected: Verification fails with revocation message
    - Acceptance: Error indicates card is revoked

#### 1.3.3 NFC Verification
**Feature**: Contactless verification using NFC technology

**Test Cases**:
14. **TC-014**: NFC activation button appears
    - Steps: View ID card on Android device with NFC
    - Expected: "Activate NFC" button visible
    - Acceptance: Button present and clickable

15. **TC-015**: NFC token written to device
    - Steps: Click "Activate NFC" → Grant permissions
    - Expected: NFC token written successfully
    - Acceptance: Success message displayed

16. **TC-016**: NFC verification with valid token
    - Steps: Write NFC token → Verify using NFC token
    - Expected: Verification succeeds
    - Acceptance: Employee verified via NFC

17. **TC-017**: NFC verification fails with expired token
    - Steps: Write NFC token → Wait 6 minutes → Verify
    - Expected: Verification fails
    - Acceptance: Error indicates expired token

---

## 2. Security Features

### 2.1 Multi-Tenant Organisation Isolation
**Feature**: Each organisation's data is completely isolated

**Test Cases**:
18. **TC-018**: User cannot access other organisation's data
    - **Priority**: P0 - Critical
    - **Pre-conditions**:
     - Organisation A admin user exists: `admina@orga.test`
     - Organisation B exists with employees
     - Organisation B employee ID known: (e.g., employee_id = 10)
     - User is logged in as Org A admin
   - **Test Data**:
     - Org A Admin: `admina@orga.test`
     - Org B Employee ID: 10 (from different organisation)
     - Org B Employee Reference: `EMP999`
   - **Detailed Steps**:
     1. Login as Org A admin: `admina@orga.test`
     2. Verify organisation_id = 1 (Org A)
     3. Attempt to access Org B employee via URL:
        - Navigate to: `{APP_URL}/admin/employees-edit.php?id=10` (Org B employee)
        - Or: `{APP_URL}/id-card.php?employee_id=10`
     4. Check if Org B employee data is accessible
     5. Attempt SQL injection via URL: `?id=10 OR organisation_id=2`
     6. Check employees list: `{APP_URL}/admin/employees.php`
     7. Check verification logs: `{APP_URL}/admin/verification-logs.php`
   - **Expected Results**:
     - URL manipulation attempts return:
       - 403 Forbidden, OR
       - 404 Not Found, OR
       - Empty results (no data)
     - Employees list shows only Org A employees
     - Verification logs show only Org A logs
     - No Org B data visible anywhere
     - SQL injection attempts fail (prepared statements prevent)
   - **Post-conditions**:
     - No cross-organisation data accessed
     - Security logs (if enabled) show access attempts
   - **Acceptance Criteria**:
     - No cross-organisation data visible
     - All queries filtered by `organisation_id`
     - URL manipulation does not bypass security
     - SQL injection attempts fail safely
   - **Edge Cases to Test**:
     - Employee ID from different organisation
     - Organisation ID manipulation in POST data
     - Session hijacking attempts
     - Direct database queries (should be filtered)
   - **Error Scenarios**:
     - 403 Forbidden (preferred)
     - 404 Not Found (acceptable)
     - Empty results (acceptable)
     - Should NEVER show Org B data

19. **TC-019**: Database queries filtered by organisation_id
    - Steps: Login as Org A user → View employees list
    - Expected: Only Org A employees shown
    - Acceptance: No employees from other organisations

20. **TC-020**: Verification logs isolated by organisation
    - Steps: Org A admin views verification logs
    - Expected: Only Org A verification logs displayed
    - Acceptance: No logs from other organisations

21. **TC-021**: MCP server respects ORGANISATION_ID
    - Steps: Configure MCP server with Org A ID → Query employees
    - Expected: Only Org A employees returned
    - Acceptance: Organisation isolation enforced

### 2.2 Authentication & Authorization

**Test Cases**:
22. **TC-022**: User registration requires email verification
    - Steps: Register new account → Check email
    - Expected: Verification email sent, account inactive until verified
    - Acceptance: Cannot login without email verification

23. **TC-023**: Role-based access control (Superadmin)
    - Steps: Login as superadmin → Access superadmin features
    - Expected: All features accessible
    - Acceptance: Can manage all organisations

24. **TC-024**: Role-based access control (Organisation Admin)
    - Steps: Login as org admin → Access admin features
    - Expected: Organisation admin features accessible
    - Acceptance: Can manage own organisation only

25. **TC-025**: Role-based access control (Staff)
    - Steps: Login as staff user → Attempt admin access
    - Expected: Admin features not accessible
    - Acceptance: Redirected or access denied

26. **TC-026**: CSRF protection on forms
    - Steps: Submit form without CSRF token
    - Expected: Form submission rejected
    - Acceptance: Error message about invalid security token

27. **TC-027**: XSS protection
    - Steps: Enter `<script>alert('XSS')</script>` in form field → Submit
    - Expected: Script not executed, content escaped
    - Acceptance: Script appears as text, not executed

28. **TC-028**: SQL injection prevention
    - Steps: Enter `' OR '1'='1` in search field
    - Expected: Query handled safely, no data breach
    - Acceptance: Prepared statements prevent injection

### 2.3 Token Security

**Test Cases**:
29. **TC-029**: QR tokens are cryptographically random
    - Steps: View ID card multiple times → Compare QR tokens
    - Expected: Tokens are different each time
    - Acceptance: No predictable pattern

30. **TC-030**: Token expiry enforced (5 minutes)
    - Steps: Generate token → Wait 6 minutes → Verify
    - Expected: Verification fails
    - Acceptance: Expiry time enforced correctly

31. **TC-031**: Revoked cards cannot be verified
    - Steps: Admin revokes ID card → User attempts verification
    - Expected: Verification fails
    - Acceptance: Revocation enforced immediately

---

## 3. Microsoft Entra/365 Integration

### 3.1 Single Sign-On (SSO)
**Feature**: Login with Microsoft Entra ID

**Test Cases**:
32. **TC-032**: Entra login button appears when configured
    - Steps: Configure Entra settings → View login page
    - Expected: "Sign in with Microsoft" button visible
    - Acceptance: Button present and functional

33. **TC-033**: SSO login redirects to Microsoft
    - Steps: Click "Sign in with Microsoft"
    - Expected: Redirected to Microsoft login page
    - Acceptance: Microsoft authentication page loads

34. **TC-034**: SSO login creates user account
    - Steps: First-time SSO login with Microsoft account
    - Expected: User account created automatically
    - Acceptance: User can login and access system

35. **TC-035**: SSO login links to existing account
    - Steps: Login with Microsoft account that matches existing email
    - Expected: Logged into existing account
    - Acceptance: No duplicate account created

### 3.2 Employee Synchronisation
**Feature**: Automatic employee sync from Microsoft 365

**Test Cases**:
36. **TC-036**: Employee sync imports users from Entra
    - Steps: Configure Entra sync → Run sync
    - Expected: Employees imported from Microsoft 365
    - Acceptance: Employee records created

37. **TC-037**: Sync updates existing employees
    - Steps: Change name in Microsoft 365 → Run sync
    - Expected: Employee name updated in Digital ID
    - Acceptance: Changes reflected in system

38. **TC-038**: Sync handles deactivated users
    - Steps: Deactivate user in Microsoft 365 → Run sync
    - Expected: User marked inactive or removed
    - Acceptance: Deactivation synced correctly

### 3.3 Microsoft 365 Workflows
**Feature**: Check-in data syncs to SharePoint, Teams notifications

**Test Cases**:
39. **TC-039**: Check-in syncs to SharePoint List
    - Steps: Configure SharePoint sync → Create check-in session → Staff check in
    - Expected: Check-in data appears in SharePoint List
    - Acceptance: Data visible in configured SharePoint List

40. **TC-040**: Teams notification sent on check-in
    - Steps: Configure Teams channel → Staff check in
    - Expected: Notification sent to Teams channel
    - Acceptance: Message appears in Teams

41. **TC-041**: Power Automate workflow triggered
    - Steps: Configure Power Automate → Staff check in
    - Expected: Workflow executes
    - Acceptance: Workflow actions completed

---

## 4. Check-In Sessions

### 4.1 Session Management
**Feature**: Create check-in sessions for fire drills, safety meetings, emergencies

**Test Cases**:
42. **TC-042**: Admin can create check-in session
    - **Priority**: P1 - High
    - **Pre-conditions**:
     - User is logged in as organisation admin
     - Check-in sessions feature is enabled
     - User has admin permissions
   - **Test Data**:
     - Admin user: `admina@orga.test`
     - Session name: "Fire Drill - Building A - 2025-01-15"
     - Session type: "fire_drill"
     - Location name: "Building A - Main Floor"
   - **Detailed Steps**:
     1. Login as admin: `admina@orga.test`
     2. Navigate to: `{APP_URL}/admin/check-in-sessions.php`
     3. Click "Create New Session" button (or navigate to create page)
     4. Fill in form:
       - Session name: "Fire Drill - Building A - 2025-01-15"
       - Session type: Select "Fire Drill" from dropdown
       - Location name: "Building A - Main Floor" (optional)
     5. Click "Create Session" or "Start Session" button
     6. Wait for confirmation
     7. Verify session appears in active sessions list
   - **Expected Results**:
     - Form submits successfully
     - Success message: "Check-in session created successfully"
     - Session appears in "Active Sessions" list
     - Session details show:
       - Session name
       - Session type: "Fire Drill"
       - Started by: Admin's name
       - Started at: Current timestamp
       - Status: "Active" or "In Progress"
     - Session ID is generated
   - **Post-conditions**:
     - `check_in_sessions` table has new record:
       - `organisation_id` = admin's organisation
       - `session_name` = entered name
       - `session_type` = 'fire_drill'
       - `started_by` = admin's user ID
       - `started_at` = current timestamp
       - `ended_at` = NULL
   - **Acceptance Criteria**:
     - Session created successfully
     - Session appears in active sessions list
     - All session details saved correctly
     - Session can be used for check-ins
   - **Edge Cases to Test**:
     - Very long session name (should not break)
     - Session name with special characters
     - Session name with emojis (should be sanitised)
     - Creating multiple sessions simultaneously
     - Session with no location name
   - **Error Scenarios**:
     - Missing session name (should show validation error)
     - Missing session type (should show validation error)
     - CSRF token missing (should show security error)
     - Non-admin user attempts (should be denied)

43. **TC-043**: Session types available (fire_drill, fire_alarm, safety_meeting, emergency)
    - Steps: Create session → Select session type
    - Expected: All types available in dropdown
    - Acceptance: Can create session of each type

44. **TC-044**: Session can be ended
    - Steps: Create session → End session
    - Expected: Session marked as ended
    - Acceptance: No new check-ins allowed

### 4.2 Check-In Functionality
**Feature**: Staff can check in using QR codes or manually

**Test Cases**:
45. **TC-045**: Staff can check in with QR code
    - **Priority**: P1 - High
    - **Pre-conditions**:
     - Active check-in session exists (from TC-042)
     - Staff user is logged in and viewing ID card
     - QR code is visible and valid (within 5 minutes)
     - Admin or check-in device has access to check-in interface
   - **Test Data**:
     - Staff user: `staff1@orga.test`
     - Session ID: (from TC-042, e.g., 1)
     - QR token: (from staff's ID card)
   - **Detailed Steps**:
     1. Create active check-in session (TC-042)
     2. Staff user logs in: `staff1@orga.test`
     3. Staff navigates to: `{APP_URL}/id-card.php`
     4. Staff views their QR code
     5. Admin or check-in operator navigates to: `{APP_URL}/check-in.php` or `{APP_URL}/check-in-qr.php`
     6. Admin selects the active session from dropdown
     7. Admin scans staff's QR code (or enters token manually)
     8. Click "Check In" button
     9. Verify check-in success
     10. Navigate to session view: `{APP_URL}/admin/check-in-sessions/view.php?id={session_id}`
     11. Verify staff appears in attendance list
   - **Expected Results**:
     - QR code scan succeeds
     - Check-in form shows employee details:
       - Name: "John Smith"
       - Employee reference: "EMP001"
     - Check-in button enables
     - Success message: "Checked in successfully!"
     - Staff appears in attendance list with:
       - Name
       - Employee reference
       - Check-in time (timestamp)
       - Check-in method: "QR Scan"
   - **Post-conditions**:
     - `check_ins` table has new record:
       - `session_id` = session ID
       - `employee_id` = staff employee ID
       - `check_in_method` = 'qr_scan'
       - `checked_in_at` = current timestamp
       - `location_lat` = NULL (unless GPS enabled)
       - `location_lng` = NULL (unless GPS enabled)
   - **Acceptance Criteria**:
     - Check-in successful
     - Staff appears in attendance list
     - Check-in method recorded correctly
     - Timestamp accurate
   - **Edge Cases to Test**:
     - Expired QR token (should fail gracefully)
     - Revoked ID card (should fail)
     - Check-in to ended session (should fail)
     - Duplicate check-in (see TC-049)
     - Check-in from different organisation (should fail)
   - **Error Scenarios**:
     - Invalid QR token (should show error)
     - Session not found (should show error)
     - Employee not found (should show error)
     - Network error during check-in (should handle gracefully)

46. **TC-046**: Staff can check in manually
    - Steps: Staff enters employee reference → Check in
    - Expected: Check-in successful
    - Acceptance: Staff appears in attendance list

47. **TC-047**: Check-in records location (GPS)
    - Steps: Check in with location enabled
    - Expected: Location coordinates saved
    - Acceptance: Location visible in attendance records

48. **TC-048**: Check-in records device info
    - Steps: Check in from mobile device
    - Expected: Device information saved
    - Acceptance: Device info visible in logs

49. **TC-049**: Duplicate check-in prevented
    - Steps: Check in → Attempt to check in again
    - Expected: Duplicate prevented or handled
    - Acceptance: Only one check-in per session

### 4.3 Attendance Tracking
**Feature**: Automatic attendance tracking and export

**Test Cases**:
50. **TC-050**: Attendance list shows all checked-in staff
    - Steps: Multiple staff check in → View session
    - Expected: All checked-in staff listed
    - Acceptance: Complete attendance list

51. **TC-051**: Attendance can be exported
    - Steps: View session → Export attendance
    - Expected: CSV/Excel file downloaded
    - Acceptance: File contains all attendance data

52. **TC-052**: Attendance includes timestamps
    - Steps: Check in → View attendance
    - Expected: Check-in time recorded
    - Acceptance: Timestamp accurate

---

## 5. Building Access Control

### 5.1 API Endpoint
**Feature**: JSON API for turnstile and building access systems

**Test Cases**:
53. **TC-053**: API endpoint accessible
    - **Priority**: P1 - High
    - **Pre-conditions**:
     - API endpoint exists at correct path
     - Server is running and accessible
     - No authentication required for basic access
   - **Test Data**:
     - Endpoint URL: `{APP_URL}/api/verify-token.php`
     - Method: GET
   - **Detailed Steps**:
     1. Open terminal or API testing tool (Postman, curl, etc.)
     2. Send GET request: `GET {APP_URL}/api/verify-token.php`
     3. Check response status code
     4. Check response headers
     5. Check response body format
   - **Expected Results**:
     - HTTP Status: 400 (Bad Request - missing token parameter)
     - Content-Type: `application/json`
     - Response body is valid JSON:
       ```json
       {
         "success": false,
         "message": "Token parameter is required",
         "error": "missing_token"
       }
       ```
     - CORS headers present (if configured):
       - `Access-Control-Allow-Origin: *`
       - `Access-Control-Allow-Methods: GET, POST, OPTIONS`
   - **Post-conditions**:
     - Endpoint is accessible
     - API responds with JSON format
     - Error handling works correctly
   - **Acceptance Criteria**:
     - Endpoint responds (not 404 or 500)
     - Returns JSON format
     - Proper error message for missing parameters
     - CORS headers present (if needed for turnstile integration)
   - **Edge Cases to Test**:
     - OPTIONS request (preflight) - should return 200
     - POST request with empty body
     - Request with invalid Content-Type
   - **Error Scenarios**:
     - 404 Not Found (endpoint doesn't exist)
     - 500 Internal Server Error (server issue)
     - Timeout (server not responding)

54. **TC-054**: API verifies valid QR token
    - **Priority**: P1 - High
    - **Pre-conditions**:
     - User is logged in and viewing ID card
     - QR token is generated and valid (not expired)
     - Employee record exists and is active
     - ID card is not revoked
   - **Test Data**:
     - User: `staff1@orga.test`
     - QR token: (from ID card, captured within 5 minutes)
     - Employee reference: `EMP001`
   - **Detailed Steps**:
     1. Complete TC-001 to view ID card
     2. Extract QR token from page source or scan QR code URL
     3. Token format: `{64-character-hex-string}`
     4. Send GET request: `GET {APP_URL}/api/verify-token.php?token={token}&type=qr`
     5. Or send POST request with JSON body:
        ```json
        {
          "token": "{token}",
          "type": "qr"
        }
        ```
     6. Check response
   - **Expected Results**:
     - HTTP Status: 200 (OK)
     - Content-Type: `application/json`
     - Response body:
       ```json
       {
         "success": true,
         "valid": true,
         "employee": {
           "id": 1,
           "employee_reference": "EMP001",
           "display_reference": "EMP001",
           "first_name": "John",
           "last_name": "Smith",
           "full_name": "John Smith",
           "organisation_id": 1,
           "organisation_name": "Test Organisation A",
           "is_active": true
         },
         "id_card": {
           "id": 1,
           "is_revoked": false,
           "expires_at": "2025-12-31T23:59:59Z"
         },
         "verification_type": "qr"
       }
       ```
   - **Post-conditions**:
     - Verification log entry created:
       - `verification_type` = 'qr'
       - `verification_result` = 'success'
       - `employee_id` = correct employee ID
       - `verified_at` = current timestamp
   - **Acceptance Criteria**:
     - Returns success with employee data
     - All employee fields present and correct
     - Verification logged in database
     - Response time < 500ms
   - **Edge Cases to Test**:
     - Token from different organisation (should still verify if valid)
     - Token with extra whitespace (should trim)
     - Token in different case (should work if case-insensitive)
   - **Error Scenarios**:
     - Expired token (see TC-055)
     - Revoked card (see TC-013)
     - Invalid token format
     - Missing token parameter

55. **TC-055**: API rejects expired token
    - Steps: Use expired token → Call API
    - Expected: Verification fails
    - Acceptance: Returns error response

56. **TC-056**: API logs location and device ID
    - **Priority**: P1 - High
    - **Pre-conditions**:
     - Valid QR token available
     - Admin access to verification logs
     - API endpoint accessible
   - **Test Data**:
     - QR token: (valid token from ID card)
     - Location: "Main_Entrance"
     - Device ID: "TURNSTILE_01"
   - **Detailed Steps**:
     1. Get valid QR token (from TC-001, within 5 minutes)
     2. Send GET request: 
        `GET {APP_URL}/api/verify-token.php?token={token}&type=qr&location=Main_Entrance&device_id=TURNSTILE_01`
     3. Or send POST request:
        ```json
        {
          "token": "{token}",
          "type": "qr",
          "location": "Main_Entrance",
          "device_id": "TURNSTILE_01"
        }
        ```
     4. Verify response is successful
     5. Login as admin: `admina@orga.test`
     6. Navigate to `{APP_URL}/admin/verification-logs.php`
     7. Find the verification log entry
     8. Check for location and device information
   - **Expected Results**:
     - API returns success (HTTP 200)
     - Verification log entry created
     - Log entry `notes` field contains:
       - `Location: Main_Entrance`
       - `Device: TURNSTILE_01`
     - Admin logs page displays:
       - Location column shows: "Main_Entrance"
       - Device ID column shows: "TURNSTILE_01"
     - CSV export includes location and device columns
   - **Post-conditions**:
     - Verification log entry updated with location/device
     - Data is searchable/filterable by location
     - Data is searchable/filterable by device ID
   - **Acceptance Criteria**:
     - Location and device logged correctly
     - Data visible in admin logs table
     - Data extractable from CSV export
     - Filters work for location and device
   - **Edge Cases to Test**:
     - Location with special characters: "Building A - Floor 2"
     - Device ID with special characters: "TURNSTILE-01"
     - Very long location name (should not truncate)
     - Very long device ID (should not truncate)
     - Location only (no device_id)
     - Device ID only (no location)
     - Neither location nor device_id (should still work)
   - **Error Scenarios**:
     - Invalid token (location/device still logged if verification fails)
     - SQL injection in location/device (should be sanitised)

57. **TC-057**: API supports GET and POST requests
    - Steps: Test both GET and POST methods
    - Expected: Both methods work
    - Acceptance: Same functionality via both methods

58. **TC-058**: API key authentication (optional)
    - Steps: Configure API key → Call API with key
    - Expected: Request authenticated
    - Acceptance: API key validated

### 5.2 Access Logging
**Feature**: All access attempts logged for security monitoring

**Test Cases**:
59. **TC-059**: Access attempts appear in verification logs
    - Steps: Use building access API → View admin logs
    - Expected: Access attempt logged
    - Acceptance: Log entry includes location and device

60. **TC-060**: Logs filterable by location
    - Steps: View logs → Filter by location
    - Expected: Only logs for that location shown
    - Acceptance: Filter works correctly

61. **TC-061**: Logs filterable by device ID
    - Steps: View logs → Filter by device_id
    - Expected: Only logs for that device shown
    - Acceptance: Filter works correctly

62. **TC-062**: Logs exportable to CSV
    - Steps: View logs → Export CSV
    - Expected: CSV file downloaded with all log data
    - Acceptance: CSV includes location and device columns

---

## 6. Staff Service Integration

### 6.1 Staff Synchronisation
**Feature**: Sync staff data from Staff Service

**Test Cases**:
63. **TC-063**: Staff Service connection test succeeds
    - **Priority**: P1 - High
    - **Pre-conditions**:
     - User is logged in as organisation admin
     - Staff Service URL is known and accessible
     - Staff Service API credentials available (if required)
     - Network connectivity to Staff Service
   - **Test Data**:
     - Admin user: `admina@orga.test`
     - Staff Service URL: `https://salmon-tarsier-739827.hostingersite.com`
     - API endpoint: `/api/staff` (or as configured)
   - **Detailed Steps**:
     1. Login as admin: `admina@orga.test`
     2. Navigate to: `{APP_URL}/admin/staff-service-settings.php`
     3. Fill in Staff Service configuration:
       - Staff Service URL: `https://salmon-tarsier-739827.hostingersite.com`
       - API Key/Token: (if required)
       - Organisation ID mapping: (if required)
     4. Click "Test Connection" button
     5. Wait for connection test result
     6. Observe success/error message
   - **Expected Results**:
     - Connection test initiates
     - Success message: "Connection successful" or similar
     - Test completes within 5 seconds
     - No error messages
     - Connection status shows as "Connected" or "Active"
   - **Post-conditions**:
     - Staff Service settings saved to database
     - `use_staff_service` setting = true (or enabled)
     - Connection can be used for sync operations
   - **Acceptance Criteria**:
     - Connection test passes
     - Settings saved correctly
     - Ready for staff synchronisation
   - **Edge Cases to Test**:
     - Invalid URL format (should show validation error)
     - Unreachable URL (should show connection error)
     - URL with invalid SSL certificate (should show warning)
     - URL requiring authentication (should prompt for credentials)
     - Very slow connection (should timeout appropriately)
   - **Error Scenarios**:
     - Network timeout (should show timeout error)
     - Invalid credentials (should show authentication error)
     - Service unavailable (should show service error)
     - SSL/TLS errors (should show security error)

64. **TC-064**: Sync imports staff from Staff Service
    - Steps: Configure Staff Service → Run sync
    - Expected: Staff imported as employees
    - Acceptance: Employee records created

65. **TC-065**: Sync updates existing employees
    - Steps: Change data in Staff Service → Run sync
    - Expected: Employee data updated
    - Acceptance: Changes reflected

66. **TC-066**: Sync handles deactivated staff
    - Steps: Deactivate staff in Staff Service → Webhook received
    - Expected: ID card automatically revoked
    - Acceptance: Card revoked, cannot verify

### 6.2 Webhook Integration
**Feature**: Automatic ID card revocation on staff deactivation

**Test Cases**:
67. **TC-067**: Webhook receives person.deactivated event
    - Steps: Deactivate person in Staff Service
    - Expected: Webhook called with deactivation event
    - Acceptance: Event received and processed

68. **TC-068**: ID card revoked on deactivation
    - Steps: Staff member has active ID card → Deactivate in Staff Service
    - Expected: ID card automatically revoked
    - Acceptance: Verification fails for revoked card

---

## 7. MCP Server

### 7.1 Server Functionality
**Feature**: MCP server provides programmatic access to Digital ID data

**Test Cases**:
69. **TC-069**: MCP server starts successfully
    - **Priority**: P2 - Medium
    - **Pre-conditions**:
     - Node.js installed (version 18+)
     - MCP server dependencies installed (`npm install` completed)
     - Database accessible from MCP server
     - `.env` file configured in `mcp-server/` directory
   - **Test Data**:
     - Database connection details in `.env`:
       ```
       DB_HOST=localhost
       DB_NAME=digital_ids
       DB_USER=test_user
       DB_PASS=test_password
       ORGANISATION_ID=1
       ```
   - **Detailed Steps**:
     1. Navigate to MCP server directory: `cd mcp-server`
     2. Verify `.env` file exists with required variables
     3. Verify `ORGANISATION_ID` is set
     4. Build TypeScript: `npm run build`
     5. Start server: `npm start` (or `node dist/index.js`)
     6. Observe startup output
     7. Check for errors in console
   - **Expected Results**:
     - Build completes without errors
     - Server starts successfully
     - No error messages in console
     - Server is listening/ready for connections
     - MCP protocol initialised
     - Database connection established
   - **Post-conditions**:
     - Server process running
     - Ready to accept MCP tool calls
     - Database connection active
   - **Acceptance Criteria**:
     - Server starts without errors
     - Server running and responsive
     - Can accept tool calls via MCP protocol
   - **Edge Cases to Test**:
     - Missing `ORGANISATION_ID` (should fail with clear error)
     - Invalid database credentials (should fail with connection error)
     - Database not accessible (should fail gracefully)
     - Port already in use (should show appropriate error)
   - **Error Scenarios**:
     - Missing dependencies (should show npm error)
     - TypeScript compilation errors (should show build errors)
     - Database connection failure (should show DB error)
     - Missing environment variables (should show config error)

70. **TC-070**: MCP server requires ORGANISATION_ID
    - Steps: Start server without ORGANISATION_ID
    - Expected: Server fails to start or errors
    - Acceptance: Organisation ID required

### 7.2 Available Tools

**Test Cases**:
71. **TC-071**: get_employee tool works
    - Steps: Call get_employee with email/ID/reference
    - Expected: Employee data returned
    - Acceptance: Correct employee information

72. **TC-072**: verify_id_card tool works
    - Steps: Call verify_id_card with QR token
    - Expected: Verification result returned
    - Acceptance: Success/failure status correct

73. **TC-073**: get_verification_logs tool works
    - Steps: Call get_verification_logs with filters
    - Expected: Filtered logs returned
    - Acceptance: Logs match filter criteria

74. **TC-074**: list_employees tool works
    - Steps: Call list_employees
    - Expected: List of employees returned
    - Acceptance: Only organisation's employees shown

75. **TC-075**: get_organisation tool works
    - Steps: Call get_organisation
    - Expected: Organisation data returned
    - Acceptance: Correct organisation information

76. **TC-076**: revoke_id_card tool works
    - Steps: Call revoke_id_card with employee ID
    - Expected: ID card revoked
    - Acceptance: Verification fails for revoked card

77. **TC-077**: get_pending_photos tool works
    - Steps: Call get_pending_photos
    - Expected: List of employees with pending photos
    - Acceptance: Correct list returned

### 7.3 Organisation Isolation
**Feature**: MCP server enforces organisation-level access control

**Test Cases**:
78. **TC-078**: MCP server only returns configured organisation's data
    - Steps: Configure server for Org A → Query employees
    - Expected: Only Org A employees returned
    - Acceptance: No cross-organisation data

79. **TC-079**: MCP server cannot access other organisations
    - Steps: Attempt to query Org B data with Org A server
    - Expected: Query fails or returns empty
    - Acceptance: Organisation isolation enforced

---

## 8. Administrative Features

### 8.1 Employee Management
**Feature**: Admins can create, edit, and manage employees

**Test Cases**:
80. **TC-080**: Admin can create employee record
    - **Priority**: P0 - Critical
    - **Pre-conditions**:
     - User is logged in as organisation admin
     - User account exists and is linked to organisation
     - Employee creation form is accessible
   - **Test Data**:
     - Admin user: `admina@orga.test`
     - Employee data:
       - First name: "Jane"
       - Last name: "Doe"
       - Employee reference: "EMP100"
       - Email: `jane.doe@orga.test` (optional, for linking to user)
   - **Detailed Steps**:
     1. Login as admin: `admina@orga.test`
     2. Navigate to: `{APP_URL}/admin/employees.php`
     3. Click "Create Employee" or "Add Employee" button
     4. Fill in employee form:
       - First name: "Jane"
       - Last name: "Doe"
       - Employee reference: "EMP100"
       - Email: `jane.doe@orga.test` (if linking to user account)
       - Photo: (optional, upload test image)
     5. Click "Create" or "Save" button
     6. Wait for form submission
     7. Verify success message
     8. Check employees list for new employee
   - **Expected Results**:
     - Form submits successfully
     - Success message: "Employee created successfully" or similar
     - Employee appears in employees list:
       - Name: "Jane Doe"
       - Employee reference: "EMP100"
       - Status: "Active" (or pending if photo required)
     - Employee record created in database
   - **Post-conditions**:
     - `employees` table has new record:
       - `organisation_id` = admin's organisation
       - `first_name` = "Jane"
       - `last_name` = "Doe"
       - `employee_reference` = "EMP100"
       - `user_id` = linked user ID (if email provided)
       - `is_active` = true
     - Digital ID card can be generated for employee
   - **Acceptance Criteria**:
     - Employee created successfully
     - Employee appears in list
     - All fields saved correctly
     - Employee can view their ID card (if user account linked)
   - **Edge Cases to Test**:
     - Employee reference already exists (should show duplicate error)
     - Very long employee reference (should validate length)
     - Employee reference with special characters (should validate format)
     - Employee with no linked user account (should still create)
     - Employee with linked user account (should link correctly)
   - **Error Scenarios**:
     - Missing required fields (should show validation errors)
     - Invalid email format (should show validation error)
     - Duplicate employee reference (should show duplicate error)
     - CSRF token missing (should show security error)
     - Non-admin user attempts (should be denied)

81. **TC-081**: Admin can edit employee details
    - Steps: Edit employee → Save changes
    - Expected: Changes saved
    - Acceptance: Updated data visible

82. **TC-082**: Admin can upload employee photo
    - Steps: Upload photo for employee
    - Expected: Photo uploaded and displayed
    - Acceptance: Photo visible on ID card

83. **TC-083**: Photo approval workflow
    - Steps: Upload photo → Admin approves/rejects
    - Expected: Photo status updated
    - Acceptance: Approval workflow functions

84. **TC-084**: Admin can revoke ID card
    - Steps: Revoke employee's ID card
    - Expected: Card revoked
    - Acceptance: Verification fails

### 8.2 Verification Logs
**Feature**: Complete audit trail of all verification attempts

**Test Cases**:
85. **TC-085**: Admin can view verification logs
    - Steps: Login as admin → View verification logs
    - Expected: Logs displayed
    - Acceptance: All verification attempts listed

86. **TC-086**: Logs filterable by date range
    - Steps: Filter logs by date range
    - Expected: Only logs in range shown
    - Acceptance: Filter works correctly

87. **TC-087**: Logs filterable by employee
    - Steps: Filter logs by employee
    - Expected: Only that employee's logs shown
    - Acceptance: Filter works correctly

88. **TC-088**: Logs filterable by verification type
    - Steps: Filter by QR/NFC/visual
    - Expected: Only selected type shown
    - Acceptance: Filter works correctly

89. **TC-089**: Logs exportable to CSV
    - Steps: Export logs to CSV
    - Expected: CSV file downloaded
    - Acceptance: CSV contains all log data

90. **TC-090**: Logs include location and device (building access)
    - Steps: View logs from building access API
    - Expected: Location and device columns visible
    - Acceptance: Data correctly displayed

### 8.3 User Management
**Feature**: Admins can manage users and roles

**Test Cases**:
91. **TC-091**: Admin can view users list
    - Steps: Login as admin → View users
    - Expected: All organisation users listed
    - Acceptance: Complete user list

92. **TC-092**: Admin can assign roles
    - Steps: Assign role to user
    - Expected: Role assigned
    - Acceptance: User has correct permissions

93. **TC-093**: Admin can activate/deactivate users
    - Steps: Deactivate user account
    - Expected: User cannot login
    - Acceptance: Account inactive

---

## 9. Data Portability

### 9.1 Export/Import
**Feature**: JSON export/import for employee ID data

**Test Cases**:
94. **TC-094**: Admin can export employee data
    - Steps: Export employee data to JSON
    - Expected: JSON file downloaded
    - Acceptance: File contains employee data

95. **TC-095**: Admin can import employee data
    - Steps: Import JSON file with employee data
    - Expected: Employees imported
    - Acceptance: Data correctly imported

96. **TC-096**: Export includes all employee fields
    - Steps: Export employee → Check JSON structure
    - Expected: All fields included
    - Acceptance: Complete data exported

---

## 10. User Experience Features

### 10.1 Responsive Design
**Feature**: Works on all devices - smartphone, tablet, computer

**Test Cases**:
97. **TC-097**: Site responsive on mobile
    - Steps: View site on mobile device
    - Expected: Layout adapts to mobile
    - Acceptance: All features accessible

98. **TC-098**: Site responsive on tablet
    - Steps: View site on tablet
    - Expected: Layout adapts to tablet
    - Acceptance: Optimal viewing experience

99. **TC-099**: Site works on desktop
    - Steps: View site on desktop browser
    - Expected: Full layout displayed
    - Acceptance: All features accessible

### 10.2 Accessibility
**Feature**: Accessible to all users

**Test Cases**:
100. **TC-100**: Keyboard navigation works
    - Steps: Navigate site using only keyboard
    - Expected: All features accessible via keyboard
    - Acceptance: No mouse required

101. **TC-101**: Screen reader compatible
    - Steps: Use screen reader on site
    - Expected: Content readable
    - Acceptance: All information accessible

---

## Test Execution Tracking

### Test Results Template
For each test case, record:
- **Test Case ID**: (e.g., TC-001)
- **Date Tested**: 
- **Tester**: 
- **Result**: Pass / Fail / Blocked / Skipped
- **Notes**: (Any issues, screenshots, etc.)
- **Environment**: (Local / Staging / Production)

### Priority Levels
- **P0 - Critical**: Core functionality (TC-001 to TC-031)
- **P1 - High**: Security and integration (TC-032 to TC-068)
- **P2 - Medium**: Advanced features (TC-069 to TC-096)
- **P3 - Low**: UX and polish (TC-097 to TC-101)

---

## Test Completion Criteria

### Definition of Done
All tests must be executed and:
- **P0 tests**: 100% pass rate required
- **P1 tests**: 95% pass rate required
- **P2 tests**: 90% pass rate required
- **P3 tests**: 85% pass rate required

### Sign-off
- [ ] All P0 tests passed
- [ ] All P1 tests passed
- [ ] Critical bugs fixed and retested
- [ ] Test results documented
- [ ] Ready for production deployment

---

## Test Execution Workflow

### Pre-Testing Checklist
- [ ] Test database is set up with required schema
- [ ] Test data is populated (organisations, users, employees)
- [ ] Test environment is accessible (localhost or staging)
- [ ] All required test accounts are created
- [ ] Test devices are ready (desktop, mobile, iOS)
- [ ] API testing tools are installed (Postman, curl, etc.)
- [ ] Browser developer tools are accessible

### Test Execution Process
1. **Start with P0 Tests**: Execute all P0 (Critical) tests first
2. **Document Results**: Record pass/fail for each test case
3. **Capture Evidence**: Take screenshots for:
   - Failed tests
   - Critical functionality
   - Error messages
4. **Report Issues**: Document bugs with:
   - Test case ID
   - Steps to reproduce
   - Expected vs actual results
   - Screenshots/logs
5. **Retest After Fixes**: Re-run failed tests after bug fixes
6. **Sign Off**: Complete sign-off checklist before production

### Test Data Management
- Use separate test database (never production)
- Reset test data between test runs if needed
- Maintain test data scripts for quick setup
- Document any test data dependencies

### API Testing Tools
- **Postman**: For API endpoint testing (TC-053 to TC-062)
- **curl**: For command-line API testing
- **Browser DevTools**: For network inspection
- **MCP Client**: For MCP server testing (TC-069 to TC-079)

### Database Verification
For tests that modify data, verify database state:
```sql
-- Example: Check employee was created
SELECT * FROM employees WHERE employee_reference = 'EMP100';

-- Example: Check verification log was created
SELECT * FROM verification_logs WHERE employee_id = 1 ORDER BY verified_at DESC LIMIT 1;

-- Example: Check organisation isolation
SELECT * FROM employees WHERE organisation_id = 1; -- Should only show Org A employees
```

### Performance Benchmarks
- Page load time: < 2 seconds
- API response time: < 500ms
- Database query time: < 100ms (for simple queries)
- PWA offline load: < 1 second (from cache)

## Notes
- This test plan should be updated as new features are added
- Test cases should be expanded with more detailed steps as needed
- Consider automating repetitive tests (P0 and P1) where possible
- Regular regression testing recommended after each release
- Keep test data realistic but clearly identifiable as test data
- Document any environment-specific requirements

## Test Plan Expansion Summary

This test plan has been expanded to include:

### Enhanced Test Cases
- **Detailed Step-by-Step Instructions**: Each test case now includes specific steps with URLs and actions
- **Pre-conditions**: Clear requirements before test execution
- **Test Data**: Specific test data examples for each test
- **Expected Results**: Detailed expected outcomes with examples
- **Post-conditions**: Database and system state after test execution
- **Edge Cases**: Additional scenarios to test
- **Error Scenarios**: How the system should handle errors

### Test Environment Setup
- Comprehensive database configuration requirements
- Detailed test organisation and user setup
- Device and network requirements
- Test data requirements

### Additional Sections
- Test execution workflow
- Pre-testing checklist
- API testing tool recommendations
- Database verification queries
- Performance benchmarks

### Priority Levels
- **P0 - Critical**: Core functionality (must pass 100%)
- **P1 - High**: Security and integration (must pass 95%)
- **P2 - Medium**: Advanced features (must pass 90%)
- **P3 - Low**: UX and polish (must pass 85%)

### Next Steps
1. Execute P0 tests first to verify core functionality
2. Expand remaining test cases with same level of detail as needed
3. Create automated test scripts for repetitive P0/P1 tests
4. Set up continuous integration (CI) for automated testing
5. Maintain test results tracking spreadsheet or database

