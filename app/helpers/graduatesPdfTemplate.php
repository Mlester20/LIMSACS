<?php

class GraduatesPdfTemplate {
    /**
     * Build the HTML for the graduates master list PDF (Dompdf input).
     * @param array $records rows shaped like GraduatesModel::getAllForExport()
     * @param array $meta ['registrar_name'=>, 'registrar_title'=>, 'principal_name'=>, 'principal_title'=>, 'batch_label'=>]
     *               -- registrar/principal come from school_settings, so the signature
     *               block stays correct regardless of which account (admin or registrar)
     *               triggers the export. batch_label is the selected school year's label
     *               (e.g. "2025-2026"), shown in the heading when a single batch is filtered.
     * @return string
     */
    public static function render(array $records, array $meta = []): string {
        $e = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');

        // Dompdf runs with isRemoteEnabled=false (no HTTP fetches), so the logo
        // is embedded as a base64 data URI instead of linked by URL.
        $logoPath = dirname(__DIR__, 2) . '/public/assets/img/favicon/logo.png';
        $logoSrc = is_file($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : '';

        $registrarName = trim($meta['registrar_name'] ?? '');
        $registrarTitle = trim($meta['registrar_title'] ?? '') ?: 'Registrar';
        $principalName = trim($meta['principal_name'] ?? '');
        $principalTitle = trim($meta['principal_title'] ?? '') ?: 'School Principal';
        $batchLabel = trim($meta['batch_label'] ?? '');

        $html = '<style>
            body { font-family: sans-serif; font-size: 10px; }
            .report-header { text-align: center; margin-bottom: 10px; }
            .report-header img { width: 70px; height: 70px; }
            .report-header .school-name { font-size: 16px; font-weight: bold; margin: 4px 0 0; }
            .report-header .school-address { font-size: 10px; color: #444; margin: 0; }
            h2 { text-align: center; margin: 10px 0 4px; }
            p.meta { text-align: center; margin-top: 0; color: #666; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
            th { background: #eee; }
            .signatures { width: 100%; margin-top: 50px; }
            .signatures td { border: none; padding: 0 20px; vertical-align: top; }
            .signatures .sig-label { text-align: left; font-weight: bold; margin: 0 0 40px; }
            .signatures .sig-line { border-top: 1px solid #333; padding-top: 4px; font-weight: bold; text-align: center; }
            .signatures .sig-note { color: #777; font-style: italic; text-align: center; margin: 0; }
            .signatures .sig-title { color: #555; text-align: center; margin: 0; }
        </style>';

        $html .= '<div class="report-header">';
        if ($logoSrc !== '') {
            $html .= '<img src="' . $logoSrc . '" alt="School Logo">';
        }
        $html .= '<p class="school-name">Aurora Central School</p>';
        $html .= '<p class="school-address">Aurora, Isabela</p>';
        $html .= '</div>';

        $html .= '<h2>Graduates Master List' . ($batchLabel !== '' ? ' &mdash; Batch ' . $e($batchLabel) : '') . '</h2>';
        $html .= '<p class="meta">Generated ' . $e(date('F j, Y g:i A')) . ' &mdash; ' . count($records) . ' record(s)</p>';
        $html .= '<table><thead><tr>
            <th>LRN</th><th>Name</th><th>Gender</th>
        </tr></thead><tbody>';

        foreach ($records as $row) {
            $html .= '<tr>'
                . '<td>' . $e($row['lrn']) . '</td>'
                . '<td>' . $e($row['student_full_name']) . '</td>'
                . '<td>' . $e($row['gender']) . '</td>'
                . '</tr>';
        }

        $html .= '</tbody></table>';

        $html .= '<table class="signatures"><tr>
            <td style="width:50%;">
                <p class="sig-label">Prepared by:</p>
                <div class="sig-line">' . ($registrarName !== '' ? $e($registrarName) : '&nbsp;') . '</div>
                <p class="sig-note">Signature over Printed Name</p>
                <p class="sig-title">' . $e($registrarTitle) . '</p>
            </td>
            <td style="width:50%;">
                <p class="sig-label">Checked by:</p>
                <div class="sig-line">' . ($principalName !== '' ? $e($principalName) : '&nbsp;') . '</div>
                <p class="sig-note">Signature over Printed Name</p>
                <p class="sig-title">' . $e($principalTitle) . '</p>
            </td>
        </tr></table>';

        return $html;
    }
}
