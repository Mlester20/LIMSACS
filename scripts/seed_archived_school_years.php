<?php
// One-off seeder: backfills archived school_year records for the 2015-2016
// through 2025-2026 batches (11 school years), so historical data (imported
// students, past graduates, etc.) has real school_year rows to reference.
//
// Run once from the command line:
//   php scripts/seed_archived_school_years.php
//
// Safe to re-run: existing `school_year` labels are skipped, not duplicated.

require_once __DIR__ . '/../database/config/config.php';
require_once __DIR__ . '/../app/models/Model.php';
require_once __DIR__ . '/../app/models/admin/SchoolYearModel.php';

$model = new SchoolYearModel($con);

$startBatchYear = 2015;
$endBatchYear = 2025; // last batch label is "2025-2026"

$inserted = [];
$skipped = [];

for ($year = $startBatchYear; $year <= $endBatchYear; $year++) {
    $label = "{$year}-" . ($year + 1);

    // Skip if this label already exists, so the seeder is safe to re-run.
    $existsStmt = $con->prepare("SELECT id FROM school_year WHERE school_year = ? LIMIT 1");
    $existsStmt->bind_param('s', $label);
    $existsStmt->execute();
    if ($existsStmt->get_result()->fetch_assoc()) {
        $skipped[] = $label;
        continue;
    }

    // Start date somewhere in June of $year, end date somewhere in April of $year+1
    // (mirrors the day-of-month spread already used by the seeded rows in limsacsdb.sql).
    $startDay = 1 + (($year - $startBatchYear) % 10);
    $endDay = 1 + (($year - $startBatchYear) % 10);

    $data = [
        'school_year' => $label,
        'start_date'  => sprintf('%d-06-%02d', $year, $startDay),
        'end_date'    => sprintf('%d-04-%02d', $year + 1, $endDay),
        'status'      => 'archived',
    ];

    if ($model->create($data)) {
        $inserted[] = $label;
    } else {
        echo "[" . date('Y-m-d H:i:s') . "] FAILED to insert {$label}\n";
    }
}

echo '[' . date('Y-m-d H:i:s') . '] Inserted ' . count($inserted) . " archived school year(s): " . implode(', ', $inserted) . "\n";
if (!empty($skipped)) {
    echo '[' . date('Y-m-d H:i:s') . '] Skipped ' . count($skipped) . " already-existing school year(s): " . implode(', ', $skipped) . "\n";
}
