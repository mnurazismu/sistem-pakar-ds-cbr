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

// Cek id penyakit
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_penyakit = mysqli_real_escape_string($conn, $_GET['id']);

// Cek apakah penyakit masih digunakan di tabel kasus
$check_kasus = "SELECT 1 FROM kasus WHERE id_penyakit = ? LIMIT 1";
$stmt_check = mysqli_prepare($conn, $check_kasus);
mysqli_stmt_bind_param($stmt_check, "i", $id_penyakit);
mysqli_stmt_execute($stmt_check);
mysqli_stmt_store_result($stmt_check);

if (mysqli_stmt_num_rows($stmt_check) > 0) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Penyakit tidak dapat dihapus karena masih digunakan dalam data kasus. Hapus terlebih dahulu kasus yang terkait.'
    ];
    header("Location: index.php");
    exit;
}

// Cek apakah penyakit masih digunakan di tabel rule_ds
$check_rule = "SELECT 1 FROM rule_ds WHERE id_penyakit = ? LIMIT 1";
$stmt_check_rule = mysqli_prepare($conn, $check_rule);
mysqli_stmt_bind_param($stmt_check_rule, "i", $id_penyakit);
mysqli_stmt_execute($stmt_check_rule);
mysqli_stmt_store_result($stmt_check_rule);

if (mysqli_stmt_num_rows($stmt_check_rule) > 0) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Penyakit tidak dapat dihapus karena masih digunakan dalam aturan Dempster-Shafer. Hapus terlebih dahulu aturan yang terkait.'
    ];
    header("Location: index.php");
    exit;
}

// Cek apakah penyakit masih digunakan di tabel penyakit_solusi
$check_solusi = "SELECT 1 FROM penyakit_solusi WHERE id_penyakit = ? LIMIT 1";
$stmt_check_solusi = mysqli_prepare($conn, $check_solusi);
mysqli_stmt_bind_param($stmt_check_solusi, "i", $id_penyakit);
mysqli_stmt_execute($stmt_check_solusi);
mysqli_stmt_store_result($stmt_check_solusi);

if (mysqli_stmt_num_rows($stmt_check_solusi) > 0) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Penyakit tidak dapat dihapus karena masih memiliki solusi terkait. Hapus terlebih dahulu solusi yang terkait.'
    ];
    header("Location: index.php");
    exit;
}

// Mulai transaksi
mysqli_begin_transaction($conn);

try {
    // Hapus penyakit
    $query = "DELETE FROM penyakit WHERE id_penyakit = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_penyakit);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal menghapus penyakit");
    }

    mysqli_commit($conn);
    $_SESSION['flash_message'] = [
        'type' => 'success',
        'message' => 'Penyakit berhasil dihapus.'
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