#!/usr/bin/env php
<?php
/** Nightly reconcile: for every organisation managed by Care Core, make Digital ID match it exactly (catches anything a notification missed).
 *  Usage: php scripts/sync-care-core.php [organisation_id] */
require_once dirname(__DIR__) . '/config/config.php';
CareCore::ensureSchema();
$db = getDbConnection();
$only = isset($argv[1]) ? (int) $argv[1] : 0;
$orgs = $db->query("SELECT DISTINCT organisation_id FROM organisation_settings WHERE setting_key = 'care_core_api_key'")->fetchAll(PDO::FETCH_COLUMN);
$code = 0;
foreach ($orgs as $o) {
    $o = (int) $o;
    if ($only && $o !== $only) continue;
    if (!CareCore::managed($o)) continue;
    try {
        $r = CareCoreSync::syncOrganisation($o);
        echo date('c') . " org $o: created {$r['created']}, updated {$r['updated']}, reactivated {$r['reactivated']}, deactivated {$r['deactivated']}, photos {$r['photos']}, skipped {$r['skipped']}, errors " . count($r['errors']) . "\n";
        foreach ($r['errors'] as $e) echo "  - $e\n";
    } catch (Throwable $e) {
        CareCore::set($o, 'care_core_last_result', json_encode(['errors' => [$e->getMessage()]]));
        echo date('c') . " org $o FAILED: " . $e->getMessage() . "\n"; $code = 1;
    }
}
exit($code);
