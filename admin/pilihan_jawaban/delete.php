<?php
require_once '../../config/database.php';
require_once '../../auth/functions.php';

// Cek login dan role
if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../../auth/login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Cek apakah pilihan jawaban sudah digunakan di jawaban_user
    $check_query = "SELECT id_jawaban FROM jawaban_user WHERE id_pilihan = '$id'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>
            alert('Pilihan jawaban tidak dapat dihapus karena sudah digunakan!');
            window.location.href = 'index.php';
        </script>";
        exit;
    }
    
    // Hapus pilihan jawaban
    $query = "DELETE FROM pilihan_jawaban WHERE id_pilihan = '$id'";
    
    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
    } else {
        echo "Terjadi kesalahan: " . mysqli_error($conn);
    }
} else {
    header("Location: index.php");
}
?>