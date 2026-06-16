<?php

class FileUpload {
    /**
     * @param $file
     * @param string $folder  - subfolder under storage/ (e.g. 'student_documents')
     * @param string $prefix  - filename prefix (e.g. 'doc_1' for student ID 1)
     * @return string         - relative path to the uploaded file
     * @throws Exception
     */
    public static function upload($file, string $folder = 'student_documents', string $prefix = 'doc'): string {
        try {
            // Validate file presence and errors
            if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No file uploaded or upload error occurred.');
            }

            // Resolve storage base path.
            // This file lives at: app/helpers/fileUpload.php
            // storage/ lives at the PROJECT ROOT, a sibling of app/ (see: app, backups,
            // database, public, resources, storage). So from app/helpers/ we only need
            // to go up 2 levels (helpers -> app -> root), not 3.
            $base_dir = dirname(__DIR__, 2) . '/storage/';

            // Build target subdirectory and create it if it doesn't exist
            $target_dir = $base_dir . $folder . '/';
            if (!is_dir($target_dir)) {
                if (!mkdir($target_dir, 0755, true)) {
                    throw new Exception("Failed to create directory: $target_dir");
                }
            }

            // Extract and validate file extension
            $original_name = basename($file['name']);
            $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

            // Documents-focused allowed extensions
            $allowed_extensions = ['pdf', 'docx', 'doc', 'xlsx', 'xls', 'png', 'jpg', 'jpeg'];
            if (!in_array($ext, $allowed_extensions)) {
                throw new Exception("File type '.{$ext}' is not allowed.");
            }

            // Build filename: {prefix}_{timestamp}.{ext}  e.g. doc_1_1780462154.pdf
            $timestamp   = time();
            $filename    = "{$prefix}_{$timestamp}.{$ext}";
            $target_file = $target_dir . $filename;

            // Move uploaded file to target location
            if (!move_uploaded_file($file['tmp_name'], $target_file)) {
                throw new Exception('Failed to move uploaded file to destination.');
            }

            // Return the relative path for storing in DB
            return "storage/{$folder}/{$filename}";

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Delete a file by its stored relative path
     *
     * @param string $relative_path  e.g. 'storage/student_documents/doc_1_1780462154.pdf'
     * @return bool
     */
    public static function delete(string $relative_path): bool {
        // Same fix here: project root is 2 levels up from app/helpers/, not 3.
        $base_dir  = dirname(__DIR__, 2) . '/';
        $full_path = $base_dir . $relative_path;

        if (file_exists($full_path)) {
            return unlink($full_path);
        }

        return false;
    }
}