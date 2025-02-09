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

// Cek id kasus
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_kasus = mysqli_real_escape_string($conn, $_GET['id']);

// Cek apakah kasus memiliki fitur terkait
$check_fitur = "SELECT 1 FROM fitur_kasus WHERE id_kasus = '$id_kasus' LIMIT 1";
$result_fitur = mysqli_query($conn, $check_fitur);

if (mysqli_num_rows($result_fitur) > 0) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Kasus tidak dapat dihapus karena masih memiliki fitur terkait.'
    ];
    header("Location: index.php");
    exit;
}

// Mulai transaksi
mysqli_begin_transaction($conn);

try {
    // Hapus kasus
    $query = "DELETE FROM kasus WHERE id_kasus = '$id_kasus'";
    
    if (!mysqli_query($conn, $query)) {
        throw new Exception("Gagal menghapus kasus");
    }

    mysqli_commit($conn);
    $_SESSION['flash_message'] = [
        'type' => 'success',
        'message' => 'Kasus berhasil dihapus.'
    ];
} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ];
}

header("Location: index.php");
exit;
?>