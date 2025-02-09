<?php
require_once 'functions.php';

// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simpan tipe user sebelum session dihapus untuk menentukan pesan
$tipe_user = isset($_SESSION['tipe_user']) ? $_SESSION['tipe_user'] : '';

// Hapus semua data session
$_SESSION = array();

// Hapus cookie session jika ada
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Hancurkan session
session_destroy();

// Tampilkan pesan logout dengan SweetAlert2
echo '
<!DOCTYPE html>
<html>
<head>
    <script src="../src/jquery-3.6.3.min.js"></script>
    <script src="../src/sweetalert2.all.min.js"></script>
</head>
<body>
    <script>
    $(document).ready(function() {
        Swal.fire({
            position: "top-center",
            icon: "success",
            title: "Logout Berhasil!",
            text: "' . ($tipe_user === 'Admin' ? 'Terima kasih Admin!' : 'Terima kasih telah menggunakan sistem!') . '",
            showConfirmButton: false,
            timer: 1500
        }).then(function() {
            window.location.href = "../auth/login.php";
        });
    });
    </script>
</body>
</html>
';
exit;
?>