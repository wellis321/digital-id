<?php
require_once dirname(__DIR__, 2) . '/config/config.php';

Auth::requireLogin();
RBAC::requireAdmin();

// Superadmins don't manage verification logs - they manage organisations
if (RBAC::isSuperAdmin()) {
    header('Location: ' . url('admin/organisations.php'));
    exit;
}

$organisationId = Auth::getOrganisationId();
$error = '';
$success = '';

// Get filter parameters
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$employeeId = $_GET['employee_id'] ?? '';
$verificationType = $_GET['verification_type'] ?? '';
$verificationResult = $_GET['verification_result'] ?? '';
$export = $_GET['export'] ?? '';

$db = getDbConnection();

// Get all employees for filter dropdown
$stmt = $db->prepare("
    SELECT e.id, u.first_name, u.last_name, e.display_reference, e.employee_reference
    FROM employees e
    JOIN users u ON e.user_id = u.id
    WHERE e.organisation_id = ?
    ORDER BY u.last_name, u.first_name
");
$stmt->execute([$organisationId]);
$employees = $stmt->fetchAll();

// Build query with filters
$whereConditions = ["e.organisation_id = ?"];
$params = [$organisationId];

if (!empty($dateFrom)) {
    $whereConditions[] = "DATE(vl.verified_at) >= ?";
    $params[] = $dateFrom;
}

if (!empty($dateTo)) {
    $whereConditions[] = "DATE(vl.verified_at) <= ?";
    $params[] = $dateTo;
}

if (!empty($employeeId)) {
    $whereConditions[] = "vl.employee_id = ?";
    $params[] = $employeeId;
}

if (!empty($verificationType)) {
    $whereConditions[] = "vl.verification_type = ?";
    $params[] = $verificationType;
}

if (!empty($verificationResult)) {
    $whereConditions[] = "vl.verification_result = ?";
    $params[] = $verificationResult;
}

$whereClause = implode(' AND ', $whereConditions);

// Handle CSV export
if ($export === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="verification-logs-' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // CSV headers
    fputcsv($output, [
        'Date/Time',
        'Employee Name',
        'Employee Reference',
        'Verification Type',
        'Result',
        'Verified By',
        'IP Address',
        'Device',
        'Notes'
    ]);
    
    // Get all matching records (no limit for export)
    $stmt = $db->prepare("
        SELECT 
            vl.verified_at,
            u_emp.first_name,
            u_emp.last_name,
            e.display_reference,
            e.employee_reference,
            vl.verification_type,
            vl.verification_result,
            u_verifier.first_name as verifier_first_name,
            u_verifier.last_name as verifier_last_name,
            vl.verified_by_ip,
            vl.verified_by_device,
            vl.notes
        FROM verification_logs vl
        INNER JOIN employees e ON vl.employee_id = e.id
        INNER JOIN users u_emp ON e.user_id = u_emp.id
        LEFT JOIN users u_verifier ON vl.verified_by = u_verifier.id
        WHERE $whereClause
        ORDER BY vl.verified_at DESC
    ");
    $stmt->execute($params);
    
    while ($row = $stmt->fetch()) {
        $employeeName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
        $employeeRef = $row['display_reference'] ?? $row['employee_reference'] ?? 'N/A';
        $verifierName = '';
        if ($row['verifier_first_name'] || $row['verifier_last_name']) {
            $verifierName = trim(($row['verifier_first_name'] ?? '') . ' ' . ($row['verifier_last_name'] ?? ''));
        } else {
            $verifierName = 'Public Verification';
        }
        
        fputcsv($output, [
            $row['verified_at'],
            $employeeName,
            $employeeRef,
            strtoupper($row['verification_type']),
            ucfirst($row['verification_result']),
            $verifierName,
            $row['verified_by_ip'] ?? 'N/A',
            $row['verified_by_device'] ?? 'N/A',
            $row['notes'] ?? ''
        ]);
    }
    
    fclose($output);
    exit;
}

// Get pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Get total count for pagination
$countStmt = $db->prepare("
    SELECT COUNT(*) as total
    FROM verification_logs vl
    INNER JOIN employees e ON vl.employee_id = e.id
    WHERE $whereClause
");
$countStmt->execute($params);
$totalRecords = $countStmt->fetch()['total'];
$totalPages = ceil($totalRecords / $perPage);

// Get verification logs
$stmt = $db->prepare("
    SELECT 
        vl.id,
        vl.verified_at,
        vl.verification_type,
        vl.verification_result,
        vl.verified_by_ip,
        vl.verified_by_device,
        vl.notes,
        e.id as employee_id,
        u_emp.first_name,
        u_emp.last_name,
        e.display_reference,
        e.employee_reference,
        u_verifier.id as verifier_id,
        u_verifier.first_name as verifier_first_name,
        u_verifier.last_name as verifier_last_name
    FROM verification_logs vl
    INNER JOIN employees e ON vl.employee_id = e.id
    INNER JOIN users u_emp ON e.user_id = u_emp.id
    LEFT JOIN users u_verifier ON vl.verified_by = u_verifier.id
    WHERE $whereClause
    ORDER BY vl.verified_at DESC
    LIMIT ? OFFSET ?
");
$params[] = $perPage;
$params[] = $offset;
$stmt->execute($params);
$logs = $stmt->fetchAll();

$pageTitle = 'Verification Logs';
include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="container">
    <div class="card">
        <h1>Verification Logs</h1>
        <p>View and export audit trail of all verification attempts for compliance and security monitoring.</p>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <!-- Filters -->
        <form method="GET" action="" class="filter-form" style="margin: 2rem 0; padding: 1.5rem; background: #f9fafb; border: 1px solid #e5e7eb;">
            <h2 style="font-size: 1.25rem; margin-bottom: 1rem;">Filters</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label for="date_from">Date From</label>
                    <input type="date" id="date_from" name="date_from" value="<?php echo htmlspecialchars($dateFrom); ?>" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="date_to">Date To</label>
                    <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars($dateTo); ?>" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="employee_id">Employee</label>
                    <select id="employee_id" name="employee_id" class="form-control">
                        <option value="">All Employees</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?php echo $emp['id']; ?>" <?php echo ($employeeId == $emp['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(trim($emp['first_name'] . ' ' . $emp['last_name']) . ' (' . ($emp['display_reference'] ?? $emp['employee_reference']) . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="verification_type">Verification Type</label>
                    <select id="verification_type" name="verification_type" class="form-control">
                        <option value="">All Types</option>
                        <option value="visual" <?php echo ($verificationType === 'visual') ? 'selected' : ''; ?>>Visual</option>
                        <option value="qr" <?php echo ($verificationType === 'qr') ? 'selected' : ''; ?>>QR Code</option>
                        <option value="nfc" <?php echo ($verificationType === 'nfc') ? 'selected' : ''; ?>>NFC</option>
                        <option value="ble" <?php echo ($verificationType === 'ble') ? 'selected' : ''; ?>>BLE</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="verification_result">Result</label>
                    <select id="verification_result" name="verification_result" class="form-control">
                        <option value="">All Results</option>
                        <option value="success" <?php echo ($verificationResult === 'success') ? 'selected' : ''; ?>>Success</option>
                        <option value="failed" <?php echo ($verificationResult === 'failed') ? 'selected' : ''; ?>>Failed</option>
                        <option value="expired" <?php echo ($verificationResult === 'expired') ? 'selected' : ''; ?>>Expired</option>
                        <option value="revoked" <?php echo ($verificationResult === 'revoked') ? 'selected' : ''; ?>>Revoked</option>
                    </select>
                </div>
            </div>
            
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="<?php echo url('admin/verification-logs.php'); ?>" class="btn btn-secondary">Clear Filters</a>
                <a href="<?php 
                    $exportParams = $_GET;
                    $exportParams['export'] = 'csv';
                    echo url('admin/verification-logs.php?' . http_build_query($exportParams)); 
                ?>" class="btn btn-secondary">
                    <i class="fas fa-download"></i> Export CSV
                </a>
            </div>
        </form>
        
        <!-- Results Summary -->
        <div style="margin: 1.5rem 0; padding: 1rem; background: #f0f9ff; border-left: 4px solid #06b6d4;">
            <strong>Total Records:</strong> <?php echo number_format($totalRecords); ?> 
            <?php if ($totalRecords > 0): ?>
                (Showing <?php echo number_format($offset + 1); ?> - <?php echo number_format(min($offset + $perPage, $totalRecords)); ?>)
            <?php endif; ?>
        </div>
        
        <!-- Verification Logs Table -->
        <?php if (empty($logs)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No verification logs found matching your filters.
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="data-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600;">Date/Time</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600;">Employee</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600;">Type</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600;">Result</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600;">Verified By</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600;">IP Address</th>
                            <th style="padding: 0.75rem; text-align: left; font-weight: 600;">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <?php
                            $employeeName = trim(($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? ''));
                            $employeeRef = $log['display_reference'] ?? $log['employee_reference'] ?? 'N/A';
                            $verifierName = '';
                            if ($log['verifier_first_name'] || $log['verifier_last_name']) {
                                $verifierName = trim(($log['verifier_first_name'] ?? '') . ' ' . ($log['verifier_last_name'] ?? ''));
                            } else {
                                $verifierName = '<span style="color: #6b7280;">Public Verification</span>';
                            }
                            
                            $resultClass = '';
                            $resultIcon = '';
                            switch ($log['verification_result']) {
                                case 'success':
                                    $resultClass = 'color: #10b981;';
                                    $resultIcon = '<i class="fas fa-check-circle"></i> ';
                                    break;
                                case 'failed':
                                    $resultClass = 'color: #ef4444;';
                                    $resultIcon = '<i class="fas fa-times-circle"></i> ';
                                    break;
                                case 'expired':
                                    $resultClass = 'color: #f59e0b;';
                                    $resultIcon = '<i class="fas fa-clock"></i> ';
                                    break;
                                case 'revoked':
                                    $resultClass = 'color: #ef4444;';
                                    $resultIcon = '<i class="fas fa-ban"></i> ';
                                    break;
                            }
                            ?>
                            <tr style="border-bottom: 1px solid #e5e7eb;">
                                <td style="padding: 0.75rem;">
                                    <?php echo date('d/m/Y H:i:s', strtotime($log['verified_at'])); ?>
                                </td>
                                <td style="padding: 0.75rem;">
                                    <strong><?php echo htmlspecialchars($employeeName); ?></strong><br>
                                    <span style="color: #6b7280; font-size: 0.875rem;"><?php echo htmlspecialchars($employeeRef); ?></span>
                                </td>
                                <td style="padding: 0.75rem;">
                                    <span style="text-transform: uppercase; font-weight: 500;"><?php echo htmlspecialchars($log['verification_type']); ?></span>
                                </td>
                                <td style="padding: 0.75rem;">
                                    <span style="<?php echo $resultClass; ?> font-weight: 500;">
                                        <?php echo $resultIcon; ?><?php echo ucfirst($log['verification_result']); ?>
                                    </span>
                                </td>
                                <td style="padding: 0.75rem;">
                                    <?php echo $verifierName; ?>
                                </td>
                                <td style="padding: 0.75rem;">
                                    <span style="font-family: monospace; font-size: 0.875rem; color: #6b7280;">
                                        <?php echo htmlspecialchars($log['verified_by_ip'] ?? 'N/A'); ?>
                                    </span>
                                </td>
                                <td style="padding: 0.75rem;">
                                    <?php if ($log['notes']): ?>
                                        <span style="color: #6b7280; font-size: 0.875rem;" title="<?php echo htmlspecialchars($log['notes']); ?>">
                                            <?php echo htmlspecialchars(substr($log['notes'], 0, 50)); ?><?php echo strlen($log['notes']) > 50 ? '...' : ''; ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #9ca3af;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div style="margin-top: 2rem; display: flex; justify-content: center; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <?php
                    $paginationParams = $_GET;
                    if ($page > 1):
                        $paginationParams['page'] = $page - 1;
                    ?>
                        <a href="<?php echo url('admin/verification-logs.php?' . http_build_query($paginationParams)); ?>" class="btn btn-secondary">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    <?php endif; ?>
                    
                    <span style="padding: 0.5rem 1rem;">
                        Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                    </span>
                    
                    <?php
                    if ($page < $totalPages):
                        $paginationParams['page'] = $page + 1;
                    ?>
                        <a href="<?php echo url('admin/verification-logs.php?' . http_build_query($paginationParams)); ?>" class="btn btn-secondary">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>

