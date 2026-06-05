<?php
session_start();

require_once __DIR__ . '/../helpers/message.php';
require_once __DIR__ . '/../models/UpdateProfileModel.php';
require_once __DIR__ . '/../../database/config/config.php';

// Check if user is authenticated
if (!isset($_SESSION['id'])) {
    setFlash('error', 'Please log in to access this page.');
    header("Location: ../../../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updateProfileModel = new UpdateProfileModel($con);
    
    $userId = $_SESSION['id'];
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
    $errors = [];

    if (empty($full_name)) {
        $errors[] = 'Full name is required.';
    }

    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    } elseif ($updateProfileModel->emailExists($email, $userId)) {
        $errors[] = 'Email is already in use by another account.';
    }

    // Current password is required for ANY profile changes (security)
    if (empty($currentPassword)) {
        $errors[] = 'Current password is required to make any changes.';
    } else {
        // Verify current password
        $userPassword = $updateProfileModel->getUserPassword($userId);
        if (!$updateProfileModel->verifyPassword($currentPassword, $userPassword)) {
            $errors[] = 'Current password is incorrect.';
        }
    }

    // Password validation (only if changing password)
    if (!empty($newPassword) || !empty($confirmPassword)) {
        if (empty($newPassword)) {
            $errors[] = 'New password is required.';
        } elseif (strlen($newPassword) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        }

        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }
    }

    // If there are errors, set flash and redirect
    if (!empty($errors)) {
        setFlash('error', implode(' ', $errors));
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    // Handle profile picture upload
    $profilePicturePath = null;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        $fileType = $_FILES['profile_pic']['type'];
        $fileSize = $_FILES['profile_pic']['size'];

        if (!in_array($fileType, $allowedTypes)) {
            setFlash('error', 'Invalid file type. Only JPG, PNG, and GIF are allowed.');
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }

        if ($fileSize > $maxSize) {
            setFlash('error', 'File size exceeds 2MB limit.');
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }

        // Create upload directory if it doesn't exist
        $uploadDir = __DIR__ . '/../../storage/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $fileExtension = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $fileName = 'pfp_' . $userId . '_' . time() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadPath)) {
            // Store path relative to project root for consistency
            $profilePicturePath = 'storage/profiles/' . $fileName;
        } else {
            setFlash('error', 'Failed to upload profile picture.');
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }
    }

    // Update profile
    $passwordToUpdate = !empty($newPassword) ? $newPassword : null;
    if ($updateProfileModel->updateProfile($userId, $full_name, $email, $passwordToUpdate)) {
        // Update session variables immediately to reflect changes
        $_SESSION['full_name'] = $full_name;
        $_SESSION['email'] = $email;

        // Update profile picture if uploaded
        if ($profilePicturePath) {
            $updateProfileModel->updateProfilePicture($userId, $profilePicturePath);
            // Store relative path for session (will be resolved by views)
            $_SESSION['profile_picture'] = $profilePicturePath;
        }

        setFlash('success', 'Profile updated successfully!');
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    } else {
        setFlash('error', 'Failed to update profile. Please try again.');
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
}
?>