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

// Cek id solusi
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_solusi = mysqli_real_escape_string($conn, $_GET['id']);

// Mulai transaksi
mysqli_begin_transaction($conn);

try {
    // Hapus dulu data di tabel penyakit_solusi
    $query_relasi = "DELETE FROM penyakit_solusi WHERE id_solusi = ?";
    $stmt_relasi = mysqli_prepare($conn, $query_relasi);
    mysqli_stmt_bind_param($stmt_relasi, "i", $id_solusi);
    
    if (!mysqli_stmt_execute($stmt_relasi)) {
        throw new Exception("Gagal menghapus relasi penyakit-solusi");
    }

    // Baru kemudian hapus solusi
    $query = "DELETE FROM solusi WHERE id_solusi = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_solusi);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Gagal menghapus solusi");
    }

    mysqli_commit($conn);
    $_SESSION['flash_message'] = [
        'type' => 'success',
        'message' => 'Solusi berhasil dihapus.'
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