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

// Cek id fitur
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'ID fitur tidak valid.'
    ];
    header("Location: index.php");
    exit;
}

$id_fitur = mysqli_real_escape_string($conn, $_GET['id']);

// Mulai transaksi
mysqli_begin_transaction($conn);

try {
    // Hapus fitur kasus
    $query = "DELETE FROM fitur_kasus WHERE id_fitur = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_fitur);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal menghapus fitur kasus");
    }

    // Commit transaksi
    mysqli_commit($conn);

    $_SESSION['flash_message'] = [
        'type' => 'success',
        'message' => 'Fitur kasus berhasil dihapus.'
    ];
} catch (Exception $e) {
    // Rollback jika terjadi error
    mysqli_rollback($conn);

    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Gagal menghapus fitur kasus: ' . $e->getMessage()
    ];
}

header("Location: index.php");
exit;
?>