<?php

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

    class SchoolYearImportService {
        const REQUIRED_HEADERS = ['school_year', 'start_date', 'end_date'];
        const TEMPLATE_HEADERS = ['school_year', 'start_date', 'end_date', 'status'];
        const VALID_STATUSES = ['active', 'inactive', 'archived'];

        /**
         * Load an .xlsx/.xls/.csv file and map its header row (by name, case/whitespace
         * insensitive) to the canonical school_year-table column keys.
         * @return array{error: ?string, rows: array<int, array{rowNumber:int, cells:array}>}
         */
        public function parseSpreadsheet(string $absolutePath): array {
            try {
                $spreadsheet = IOFactory::load($absolutePath);
            } catch (\Throwable $e) {
                error_log('SchoolYearImportService::parseSpreadsheet - ' . $e->getMessage());
                return ['error' => 'The uploaded file could not be read. Please make sure it is a valid .xlsx, .xls, or .csv file.', 'rows' => []];
            }

            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestDataRow();
            $highestColumnIndex = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

            if ($highestRow < 1) {
                return ['error' => 'The uploaded file is empty.', 'rows' => []];
            }

            $columnMap = [];
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $coordinate = Coordinate::stringFromColumnIndex($col) . '1';
                $key = self::canonicalizeHeader($sheet->getCell($coordinate)->getValue());
                if ($key !== null && in_array($key, self::TEMPLATE_HEADERS, true)) {
                    $columnMap[$col] = $key;
                }
            }

            $missing = array_diff(self::REQUIRED_HEADERS, array_values($columnMap));
            if (!empty($missing)) {
                return ['error' => 'The file is missing required column(s): ' . implode(', ', $missing) . '. Please use the downloadable template.', 'rows' => []];
            }

            if ($highestRow < 2) {
                return ['error' => 'The file has no data rows.', 'rows' => []];
            }

            $rows = [];
            for ($r = 2; $r <= $highestRow; $r++) {
                $cells = [];
                $hasValue = false;
                foreach ($columnMap as $col => $key) {
                    $coordinate = Coordinate::stringFromColumnIndex($col) . $r;
                    $value = $sheet->getCell($coordinate)->getValue();
                    if ($value !== null && trim((string)$value) !== '') {
                        $hasValue = true;
                    }
                    $cells[$key] = $value;
                }
                if (!$hasValue) {
                    continue; // skip fully blank rows
                }
                $rows[] = ['rowNumber' => $r, 'cells' => $cells];
            }

            if (empty($rows)) {
                return ['error' => 'The file has no data rows.', 'rows' => []];
            }

            return ['error' => null, 'rows' => $rows];
        }

        /**
         * Validate + normalize one parsed row.
         * @param array $cells canonical column key => raw cell value
         * @param array<string,true> $seenKeysInFile running set of normalized school_year keys already accepted in this file (passed by reference)
         * @param array<string,true> $existingKeys normalized school_year keys already present in the school_year table
         * @param bool $hasExistingActive true if an active school year already exists in the DB (fixed for the whole batch)
         * @param bool $activeAssignedInFile whether a row in this file has already been accepted as 'active' (passed by reference)
         * @return array{status:string, reason:?string, note:?string, rowNumber:int, school_year:?string, data:?array}
         */
        public function validateRow(array $cells, int $rowNumber, array &$seenKeysInFile, array $existingKeys, bool $hasExistingActive, bool &$activeAssignedInFile): array {
            $schoolYearRaw = self::nullIfBlank($cells['school_year'] ?? null);
            $statusRaw = self::nullIfBlank($cells['status'] ?? null);

            $errors = [];

            if ($schoolYearRaw === null) {
                $errors[] = 'school_year is required';
            }

            $startDate = self::normalizeDate($cells['start_date'] ?? null);
            if ($startDate === null) {
                $errors[] = 'start_date is required and must be a valid date';
            }

            $endDate = self::normalizeDate($cells['end_date'] ?? null);
            if ($endDate === null) {
                $errors[] = 'end_date is required and must be a valid date';
            }

            if ($startDate !== null && $endDate !== null && $startDate >= $endDate) {
                $errors[] = 'start_date must be before end_date';
            }

            if (!empty($errors)) {
                return [
                    'status' => 'fail',
                    'reason' => implode('; ', $errors),
                    'note' => null,
                    'rowNumber' => $rowNumber,
                    'school_year' => $schoolYearRaw,
                    'data' => null,
                ];
            }

            $key = self::normalizeSchoolYearKey($schoolYearRaw);
            if (isset($existingKeys[$key]) || isset($seenKeysInFile[$key])) {
                return [
                    'status' => 'skip',
                    'reason' => 'duplicate school year',
                    'note' => null,
                    'rowNumber' => $rowNumber,
                    'school_year' => $schoolYearRaw,
                    'data' => null,
                ];
            }
            $seenKeysInFile[$key] = true;

            $note = null;
            $status = 'inactive';
            if ($statusRaw !== null) {
                $statusKey = strtolower($statusRaw);
                if (in_array($statusKey, self::VALID_STATUSES, true)) {
                    $status = $statusKey;
                } else {
                    $note = "status '{$statusRaw}' not recognized; defaulted to Inactive";
                }
            }

            if ($status === 'active') {
                if ($hasExistingActive || $activeAssignedInFile) {
                    $status = 'inactive';
                    $note = "row marked Active, but another school year is already active; set to Inactive instead";
                } else {
                    $activeAssignedInFile = true;
                }
            }

            return [
                'status' => 'ok',
                'reason' => null,
                'note' => $note,
                'rowNumber' => $rowNumber,
                'school_year' => $schoolYearRaw,
                'data' => [
                    'school_year' => $schoolYearRaw,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => $status,
                ],
            ];
        }

        /**
         * Normalize a school-year string for lookup: trim, lowercase, and collapse
         * common dash variants (en-dash, em-dash, spaced hyphen) to a plain '-'.
         */
        public static function normalizeSchoolYearKey(string $raw): string {
            $v = strtolower(trim($raw));
            $v = str_replace(['–', '—'], '-', $v);
            $v = preg_replace('/\s*-\s*/', '-', $v);
            return $v;
        }

        private static function canonicalizeHeader($raw): ?string {
            if ($raw === null) return null;
            $key = strtolower(trim((string)$raw));
            $key = preg_replace('/\s+/', '_', $key);
            return $key === '' ? null : $key;
        }

        /**
         * Handles both Excel serial date numbers and plain date strings.
         * @return string|null 'Y-m-d', or null if blank/unparseable
         */
        private static function normalizeDate($raw): ?string {
            if ($raw === null) return null;

            if (is_numeric($raw)) {
                try {
                    $dt = ExcelDate::excelToDateTimeObject((float)$raw);
                    return $dt->format('Y-m-d');
                } catch (\Throwable $e) {
                    return null;
                }
            }

            $v = trim((string)$raw);
            if ($v === '') return null;

            foreach (['Y-m-d', 'm/d/Y', 'n/j/Y', 'Y/m/d'] as $format) {
                $dt = DateTime::createFromFormat($format, $v);
                if ($dt instanceof DateTime) {
                    $errors = DateTime::getLastErrors();
                    if ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0)) {
                        return $dt->format('Y-m-d');
                    }
                }
            }

            return null;
        }

        private static function nullIfBlank($v): ?string {
            if ($v === null) return null;
            $v = trim((string)$v);
            return $v === '' ? null : $v;
        }
    }
