<?php

function setFlash($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type,   // success | error | warning | info
        'message' => $message
    ];
}

function showFlash()
{
    if (!isset($_SESSION['flash'])) {
        return;
    }

    $type = $_SESSION['flash']['type'];
    $message = $_SESSION['flash']['message'];
    
    // Map flash type to SweetAlert2 icon
    $iconMap = [
        'success' => 'success',
        'error' => 'error',
        'warning' => 'warning',
        'info' => 'info'
    ];
    
    $icon = isset($iconMap[$type]) ? $iconMap[$type] : 'info';

    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        Swal.fire({
            icon: '{$icon}',
            title: '" . ucfirst($type) . "',
            text: '" . addslashes($message) . "',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        });
    </script>
    ";

    unset($_SESSION['flash']); // IMPORTANT: flash only once
}
?>