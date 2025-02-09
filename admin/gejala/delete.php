<?php
require_once '../../config/database.php';
require_once '../../auth/functions.php';

// Cek login dan role
if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../../auth/login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id_gejala = $_GET['id'];
    
    // Cek apakah gejala digunakan di tabel rule_ds
    $query_check = "SELECT 1 FROM rule_ds WHERE id_gejala = ? LIMIT 1";
    $stmt_check = mysqli_prepare($conn, $query_check);
    mysqli_stmt_bind_param($stmt_check, "i", $id_gejala);
    mysqli_stmt_execute($stmt_check);
    
    if (mysqli_stmt_fetch($stmt_check)) {
        echo '
            <script src="../../src/jquery-3.6.3.min.js"></script>
            <script src="../../src/sweetalert2.all.min.js"></script>
            <script>
            $(document).ready(function() {
                Swal.fire({
                    position: "top-center",
                    icon: "success",
                    title: "Data gejala berhasil diupdate!",
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.href = "index.php";
                });
            });
            </script>';
        exit;
    }
    
    // Hapus gejala
    $query = "DELETE FROM gejala WHERE id_gejala = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_gejala);
    
    if (mysqli_stmt_execute($stmt)) {
        echo '
            <script src="../../src/jquery-3.6.3.min.js"></script>
            <script src="../../src/sweetalert2.all.min.js"></script>
            <script>
            $(document).ready(function() {
                Swal.fire({
                    position: "top-center",
                    icon: "success",
                    title: "Data gejala berhasil dihapus!",
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.href = "index.php";
                });
            });
            </script>';
        exit;
    } else {
        echo '
            <script src="../../src/jquery-3.6.3.min.js"></script>
            <script src="../../src/sweetalert2.all.min.js"></script>
            <script>
            $(document).ready(function() {
                Swal.fire({
                    position: "top-center",
                    icon: "error",
                    title: "Gagal menghapus data gejala!",
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.href = "index.php";
                });
            });
            </script>';
    }
    exit;
}

header("Location: index.php");