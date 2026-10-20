<?php
require_once __DIR__ . '/../../../database/config/config.php';
require_once __DIR__ . '/../../../app/middleware/Auth.php';
AuthRole::allowOnly(['registrar']);

// Only a bare filename is accepted (no slashes/".." ) -- the file must already
// live directly under storage/student_documents/, same folder the rest of the
// app already serves these files from without extra access control.
$rawFile = $_GET['file'] ?? '';
$fileName = basename($rawFile);
if ($fileName === '' || $fileName !== $rawFile) {
    http_response_code(400);
    exit('Invalid file.');
}

$fileUrl = BASE_URL . '/storage/student_documents/' . $fileName;
$ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($fileName) ?></title>
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/public/assets/img/favicon/logo.png" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/vendor/css/core.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/vendor/css/theme-default.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/vendor/fonts/boxicons.css" />
    <style>
        body { margin: 0; background: #525659; }
        .preview-toolbar {
            position: sticky; top: 0; z-index: 10;
            background: #fff; border-bottom: 1px solid #dee2e6;
            padding: 10px 20px; display: flex; align-items: center; justify-content: space-between;
        }
        .preview-filename { font-weight: 600; font-size: 0.9rem; }
        .preview-body { max-width: 900px; margin: 24px auto; background: #fff; padding: 32px; border-radius: 6px; min-height: 60vh; }
        .preview-loading, .preview-error { text-align: center; color: #888; padding: 60px 0; }
        .preview-error a { color: #696cff; }
    </style>
</head>
<body>
    <div class="preview-toolbar">
        <span class="preview-filename"><i class='bx bx-file-doc me-1'></i><?= htmlspecialchars($fileName) ?></span>
        <a href="<?= htmlspecialchars($fileUrl) ?>" download class="btn btn-sm btn-primary">
            <i class='bx bx-download me-1'></i>Download
        </a>
    </div>
    <div class="preview-body" id="previewBody">
        <div class="preview-loading"><span class="spinner-border spinner-border-sm me-2"></span>Loading preview...</div>
    </div>

    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/jszip/jszip.min.js"></script>
    <script src="<?= BASE_URL ?>/public/assets/vendor/libs/docx-preview/docx-preview.js"></script>
    <script>
        const fileUrl = <?= json_encode($fileUrl) ?>;
        const ext = <?= json_encode($ext) ?>;
        const container = document.getElementById('previewBody');

        if (ext === 'docx' && typeof docx !== 'undefined') {
            fetch(fileUrl)
                .then(res => {
                    if (!res.ok) throw new Error('File not found');
                    return res.blob();
                })
                .then(blob => {
                    container.innerHTML = '';
                    return docx.renderAsync(blob, container, undefined, { inWrapper: false });
                })
                .catch(() => {
                    container.innerHTML = `
                        <div class="preview-error">
                            Unable to load this document.<br>
                            <a href="${fileUrl}">Download instead</a>
                        </div>
                    `;
                });
        } else {
            // Legacy .doc or anything else that ends up here: no in-browser
            // preview available for this format.
            container.innerHTML = `
                <div class="preview-error">
                    Preview isn't available for this file type.<br>
                    <a href="${fileUrl}">Download instead</a>
                </div>
            `;
        }
    </script>
</body>
</html>
