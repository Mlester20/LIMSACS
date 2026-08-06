<?php

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once __DIR__ . '/StudentsService.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

    class StudentImportService {
        const REQUIRED_HEADERS = ['first_name', 'last_name', 'gender', 'birth_date'];

        const TEMPLATE_HEADERS = [
            'lrn', 'first_name', 'middle_name', 'last_name', 'suffix', 'gender',
            'birth_date', 'age', 'place_of_birth', 'nationality', 'religion',
            'address', 'contact_number',
        ];

        /**
         * Load an .xlsx/.xls/.csv file and map its header row (by name, case/whitespace
         * insensitive) to the canonical students-table column keys.
         * @return array{error: ?string, rows: array<int, array{rowNumber:int, cells:array}>}
         */
        public function parseSpreadsheet(string $absolutePath): array {
            try {
                $spreadsheet = IOFactory::load($absolutePath);
            } catch (\Throwable $e) {
                error_log('StudentImportService::parseSpreadsheet - ' . $e->getMessage());
                return ['error' => 'The uploaded file could not be read. Please make sure it is a valid .xlsx, .xls, or .csv file.', 'rows' => []];
            }

            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestDataRow();
            $highestColumnIndex = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

            if ($highestRow < 1) {
                return ['error' => 'The uploaded file is empty.', 'rows' => []];
            }

            // Map header row (row 1) -> canonical column key, by name, not position
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
         * @param array<string,true> $seenLrnsInFile running set of LRNs already accepted in this file (passed by reference)
         * @param array<string,true> $existingLrns LRNs already present in the students table
         * @return array{status:string, reason:?string, rowNumber:int, lrn:?string, name:string, data:?array}
         */
        public function validateRow(array $cells, int $rowNumber, array &$seenLrnsInFile, array $existingLrns): array {
            $firstName = self::nullIfBlank($cells['first_name'] ?? null);
            $lastName = self::nullIfBlank($cells['last_name'] ?? null);
            $middleName = self::nullIfBlank($cells['middle_name'] ?? null);
            $suffix = self::nullIfBlank($cells['suffix'] ?? null);
            $lrn = self::nullIfBlank($cells['lrn'] ?? null);
            $placeOfBirth = self::nullIfBlank($cells['place_of_birth'] ?? null);
            $nationality = self::nullIfBlank($cells['nationality'] ?? null);
            $religion = self::nullIfBlank($cells['religion'] ?? null);
            $address = self::nullIfBlank($cells['address'] ?? null);
            $contactNumber = self::nullIfBlank($cells['contact_number'] ?? null);

            $name = trim(($firstName ?? '') . ' ' . ($lastName ?? '')) ?: '(unnamed row)';

            $errors = [];

            if ($firstName === null) $errors[] = 'first_name is required';
            if ($lastName === null) $errors[] = 'last_name is required';

            $gender = self::normalizeGender($cells['gender'] ?? null);
            if ($gender === null) {
                $errors[] = 'gender is required';
            } elseif ($gender === 'invalid') {
                $errors[] = 'gender must be Male or Female';
            }

            $birthDate = self::normalizeBirthDate($cells['birth_date'] ?? null);
            if ($birthDate === null) {
                $errors[] = 'birth_date is required and must be a valid date';
            }

            if (!empty($errors)) {
                return [
                    'status' => 'fail',
                    'reason' => implode('; ', $errors),
                    'rowNumber' => $rowNumber,
                    'lrn' => $lrn,
                    'name' => $name,
                    'data' => null,
                ];
            }

            if ($lrn !== null && (isset($existingLrns[$lrn]) || isset($seenLrnsInFile[$lrn]))) {
                return [
                    'status' => 'skip',
                    'reason' => 'duplicate LRN',
                    'rowNumber' => $rowNumber,
                    'lrn' => $lrn,
                    'name' => $name,
                    'data' => null,
                ];
            }
            if ($lrn !== null) {
                $seenLrnsInFile[$lrn] = true;
            }

            $ageRaw = $cells['age'] ?? null;
            $age = ($ageRaw !== null && trim((string)$ageRaw) !== '')
                ? (int)$ageRaw
                : StudentsService::calculateAge($birthDate);

            return [
                'status' => 'ok',
                'reason' => null,
                'rowNumber' => $rowNumber,
                'lrn' => $lrn,
                'name' => $name,
                'data' => [
                    'lrn' => $lrn,
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'suffix' => $suffix,
                    'gender' => $gender,
                    'birth_date' => $birthDate,
                    'age' => $age,
                    'place_of_birth' => $placeOfBirth,
                    'nationality' => $nationality,
                    'religion' => $religion,
                    'address' => $address,
                    'contact_number' => $contactNumber,
                ],
            ];
        }

        private static function canonicalizeHeader($raw): ?string {
            if ($raw === null) return null;
            $key = strtolower(trim((string)$raw));
            $key = preg_replace('/\s+/', '_', $key);
            return $key === '' ? null : $key;
        }

        /**
         * @return string|null 'Male'/'Female' (valid), 'invalid' (non-empty but unrecognized), or null (blank)
         */
        private static function normalizeGender($raw): ?string {
            if ($raw === null) return null;
            $v = strtolower(trim((string)$raw));
            if ($v === '') return null;
            if ($v === 'male') return 'Male';
            if ($v === 'female') return 'Female';
            return 'invalid';
        }

        /**
         * Handles both Excel serial date numbers and plain date strings.
         * @return string|null 'Y-m-d', or null if blank/unparseable
         */
        private static function normalizeBirthDate($raw): ?string {
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
