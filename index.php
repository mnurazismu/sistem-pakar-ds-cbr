<?php
session_start();
require_once 'config/database.php';
require_once 'auth/functions.php';

// Cek status login
if (!isLoggedIn()) {
    header("Location: auth/login.php");
    exit;
}

// Redirect berdasarkan tipe user
if (isAdmin()) {
    header("Location: admin/dashboard.php");
} else {
    header("Location: user/dashboard.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pakar - Diagnosis Penyakit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
</head>

<body class="bg-gray-100">
    <!-- Loading screen -->
    <div class="fixed top-0 left-0 right-0 bottom-0 w-full h-screen z-50 overflow-hidden bg-gradient-to-br from-primary via-secondary to-quinary flex flex-col items-center justify-center">
        <div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12 mb-4"></div>
        <h2 class="text-center text-white text-xl font-semibold">Loading...</h2>
        <p class="w-1/3 text-center text-white">Mohon tunggu sebentar, Anda akan diarahkan ke halaman yang sesuai.</p>
    </div>

    <style>
        .loader {
            border-top-color: #3498db;
            -webkit-animation: spinner 1.5s linear infinite;
            animation: spinner 1.5s linear infinite;
        }

        @-webkit-keyframes spinner {
            0% {
                -webkit-transform: rotate(0deg);
            }

            100% {
                -webkit-transform: rotate(360deg);
            }
        }

        @keyframes spinner {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>

    <script>
        // Redirect setelah loading
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                <?php if (isAdmin()): ?>
                    window.location.href = 'admin/dashboard.php';
                <?php else: ?>
                    window.location.href = 'user/dashboard.php';
                <?php endif; ?>
            }, 1500); // Redirect setelah 1.5 detik
        });
    </script>

    <!-- SweetAlert2 untuk notifikasi -->
    <script src="src/jquery-3.6.3.min.js"></script>
    <script src="src/sweetalert2.all.min.js"></script>
</body>

</html>