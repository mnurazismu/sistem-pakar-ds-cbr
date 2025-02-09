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
    $id_diagnosis = mysqli_real_escape_string($conn, $_POST['id_diagnosis']);
    $status_validasi = mysqli_real_escape_string($conn, $_POST['status_validasi']);
    $keterangan_admin = mysqli_real_escape_string($conn, $_POST['keterangan_admin']);
    
    // Update status validasi
    $query = "UPDATE hasil_diagnosis SET 
              status_validasi = ?,
              keterangan_admin = ?
              WHERE id_diagnosis = ?";
              
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssi", $status_validasi, $keterangan_admin, $id_diagnosis);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => 'Status validasi hasil diagnosis berhasil diperbarui.'
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