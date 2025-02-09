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
    
    // Toggle status
    $query = "UPDATE pertanyaan SET status_aktif = NOT status_aktif WHERE id_pertanyaan = '$id'";
    
    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
    } else {
        echo "Terjadi kesalahan: " . mysqli_error($conn);
    }
} else {
    header("Location: index.php");
}
?>