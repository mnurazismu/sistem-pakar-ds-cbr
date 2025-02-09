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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    
    // Ambil informasi rule sebelum dihapus untuk pesan konfirmasi
    $query_info = "SELECT r.*, g.kode_gejala, g.nama_gejala, p.kode_penyakit, p.nama_penyakit 
                   FROM rule_ds r
                   JOIN gejala g ON r.id_gejala = g.id_gejala
                   JOIN penyakit p ON r.id_penyakit = p.id_penyakit
                   WHERE r.id_rule = '$id'";
    $result_info = mysqli_query($conn, $query_info);
    $rule = mysqli_fetch_assoc($result_info);
    
    // Cek apakah rule ditemukan
    if (!$rule) {
        $response = [
            'status' => 'error',
            'message' => 'Rule tidak ditemukan!'
        ];
    } else {
        // Hapus rule
        $query_delete = "DELETE FROM rule_ds WHERE id_rule = '$id'";
        
        if (mysqli_query($conn, $query_delete)) {
            $response = [
                'status' => 'success',
                'message' => "Rule berhasil dihapus!\nGejala: {$rule['kode_gejala']} - {$rule['nama_gejala']}\nPenyakit: {$rule['kode_penyakit']} - {$rule['nama_penyakit']}"
            ];
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus rule: ' . mysqli_error($conn)
            ];
        }
    }
    
    // Kirim response dalam format JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Jika bukan request POST, redirect ke halaman index
header("Location: index.php");
exit;
?>