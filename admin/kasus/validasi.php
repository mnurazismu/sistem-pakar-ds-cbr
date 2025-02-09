<?php
require_once '../../config/database.php';
require_once '../../auth/functions.php';

// Cek login dan role
if (!isLoggedIn()) {
    header("Location: ../../auth/login.php");
    exit;
} elseif (!isAdmin()) {
    header("Location: ../../user/dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_kasus = mysqli_real_escape_string($conn, $_POST['id_kasus']);
    $status_validasi = mysqli_real_escape_string($conn, $_POST['status_validasi']);
    
    // Update status validasi
    $query = "UPDATE kasus SET 
              status_validasi = ?,
              tanggal_validasi = NOW()
              WHERE id_kasus = ?";
              
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $status_validasi, $id_kasus);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => 'Status validasi kasus berhasil diperbarui.'
        ];
    } else {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => 'Terjadi kesalahan: ' . mysqli_error($conn)
        ];
    }
}

header("Location: index.php");
exit;
?>