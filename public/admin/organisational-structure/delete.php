<?php
require_once dirname(__DIR__, 3) . '/config/config.php';

Auth::requireLogin();
RBAC::requireOrganisationAdmin();

$organisationId = Auth::getOrganisationId();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('admin/organisational-structure.php'));
    exit;
}

if (!CSRF::validatePost()) {
    $_SESSION['error'] = 'Invalid security token.';
    header('Location: ' . url('admin/organisational-structure.php'));
    exit;
}

$unitId = intval($_POST['unit_id'] ?? 0);

if ($unitId <= 0) {
    $_SESSION['error'] = 'Invalid unit ID.';
    header('Location: ' . url('admin/organisational-structure.php'));
    exit;
}

// Verify unit belongs to organisation
$unit = OrganisationalUnits::findById($unitId);
if (!$unit || $unit['organisation_id'] != $organisationId) {
    $_SESSION['error'] = 'Unit not found.';
    header('Location: ' . url('admin/organisational-structure.php'));
    exit;
}

// Delete unit
$result = OrganisationalUnits::delete($unitId);

if ($result['success']) {
    $_SESSION['success'] = "Unit '{$unit['name']}' deleted successfully.";
} else {
    $_SESSION['error'] = $result['message'];
}

header('Location: ' . url('admin/organisational-structure.php'));
exit;

