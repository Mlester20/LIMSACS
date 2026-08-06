<?php

class GraduatesPdfTemplate {
    /**
     * Build the HTML for the graduates master list PDF (Dompdf input).
     * @param array $records rows shaped like GraduatesModel::getAllForExport()
     * @return string
     */
    public static function render(array $records): string {
        $e = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');

        $html = '<style>
            body { font-family: sans-serif; font-size: 10px; }
            h2 { text-align: center; margin-bottom: 4px; }
            p.meta { text-align: center; margin-top: 0; color: #666; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
            th { background: #eee; }
        </style>';
        $html .= '<h2>Graduates Master List</h2>';
        $html .= '<p class="meta">Generated ' . $e(date('F j, Y g:i A')) . ' &mdash; ' . count($records) . ' record(s)</p>';
        $html .= '<table><thead><tr>
            <th>LRN</th><th>Student Name</th><th>Gender</th><th>Grade Level</th><th>Section</th>
            <th>Adviser</th><th>School Year</th><th>Date Graduated</th><th>Registrar</th><th>Status</th>
        </tr></thead><tbody>';

        foreach ($records as $row) {
            $graduationDate = !empty($row['graduation_date']) ? date('F j, Y', strtotime($row['graduation_date'])) : '';
            $html .= '<tr>'
                . '<td>' . $e($row['lrn']) . '</td>'
                . '<td>' . $e($row['student_full_name']) . '</td>'
                . '<td>' . $e($row['gender']) . '</td>'
                . '<td>' . $e($row['grade_level']) . '</td>'
                . '<td>' . $e($row['section_name']) . '</td>'
                . '<td>' . $e($row['adviser_name']) . '</td>'
                . '<td>' . $e($row['school_year']) . '</td>'
                . '<td>' . $e($graduationDate) . '</td>'
                . '<td>' . $e($row['registrar_name']) . '</td>'
                . '<td>' . $e($row['enrollment_status']) . '</td>'
                . '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }
}
