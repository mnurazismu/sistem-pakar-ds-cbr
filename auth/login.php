<?php
require_once '../config/database.php';
require_once 'functions.php';

if (isLoggedIn()) {
    header("Location: ../index.php");
    exit;
}

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $errors = [];

    // Validasi Email
    if (empty($email)) {
        $errors[] = "Email harus diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid!";
    }

    // Validasi Password
    if (empty($password)) {
        $errors[] = "Password harus diisi!";
    }

    // Tambahkan rate limiting
    if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 3) {
        if (time() - $_SESSION['last_attempt'] < 300) { // 5 menit cooldown
            $wait_time = 300 - (time() - $_SESSION['last_attempt']);
            $errors[] = "Terlalu banyak percobaan login. Silakan tunggu " . ceil($wait_time/60) . " menit lagi.";
        } else {
            // Reset attempts setelah cooldown
            $_SESSION['login_attempts'] = 0;
        }
    }

    if (empty($errors)) {
        if (login($email, $password)) {
            // Reset login attempts jika berhasil
            unset($_SESSION['login_attempts']);
            unset($_SESSION['last_attempt']);

            // Redirect dengan SweetAlert2
            if ($_SESSION['tipe_user'] === 'Admin') {
                echo '
                <script src="../src/jquery-3.6.3.min.js"></script>
                <script src="../src/sweetalert2.all.min.js"></script>
                <script>
                $(document).ready(function() {
                    Swal.fire({
                        position: "top-center",
                        icon: "success",
                        title: "Login Berhasil!",
                        text: "Selamat datang Admin!",
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        window.location.href = "../admin/dashboard.php";
                    });
                });
                </script>';
            } else {
                echo '
                <script src="../src/jquery-3.6.3.min.js"></script>
                <script src="../src/sweetalert2.all.min.js"></script>
                <script>
                $(document).ready(function() {
                    Swal.fire({
                        position: "top-center",
                        icon: "success",
                        title: "Login Berhasil!",
                        text: "Selamat datang User!",
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        window.location.href = "../user/dashboard.php";
                    });
                });
                </script>';
            }
            exit;
        } else {
            // Increment login attempts
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
            $_SESSION['last_attempt'] = time();
            
            $errors[] = "Email atau password salah! Sisa percobaan: " . (3 - $_SESSION['login_attempts']);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Pakar Diagnosa Penyakit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Left Side - Information -->
        <div class="hidden lg:flex lg:w-1/2 bg-blue-600 text-white p-12 flex-col justify-between">
            <div>
                <h1 class="text-4xl font-bold mb-4">Sistem Pakar Diagnosa Penyakit</h1>
                <p class="text-lg mb-8">Menggunakan Metode Dempster-Shafer & Case-Based Reasoning</p>
                
                <div class="space-y-6">
                    <div class="flex items-start space-x-4">
                        <div class="bg-blue-500 p-3 rounded-lg">
                            <i class="fas fa-brain text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-xl mb-1">Diagnosa Cerdas</h3>
                            <p class="text-blue-100">Menggunakan dua metode untuk hasil yang lebih akurat dalam mendiagnosa penyakit berdasarkan gejala.</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="bg-blue-500 p-3 rounded-lg">
                            <i class="fas fa-chart-line text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-xl mb-1">Analisis Terperinci</h3>
                            <p class="text-blue-100">Mendapatkan hasil analisis detail dengan tingkat kepercayaan dan kemiripan dari setiap diagnosis.</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="bg-blue-500 p-3 rounded-lg">
                            <i class="fas fa-history text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-xl mb-1">Riwayat Lengkap</h3>
                            <p class="text-blue-100">Pantau riwayat diagnosis Anda dan dapatkan validasi dari pakar untuk setiap hasil diagnosis.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-sm text-blue-100">
                &copy; <?= date('Y') ?> Sistem Pakar Diagnosa Penyakit. All rights reserved.
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
            <div class="max-w-md w-full">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Selamat Datang</h2>
                    <p class="text-gray-600">Masuk untuk mengakses sistem pakar</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded">
                        <ul class="list-disc list-inside">
                            <?php foreach ($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="email">
                            <i class="fas fa-envelope text-gray-400 mr-2"></i>Email
                        </label>
                        <input class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            id="email" type="email" name="email" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="password">
                            <i class="fas fa-lock text-gray-400 mr-2"></i>Password
                        </label>
                        <input class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            id="password" type="password" name="password" required>
                    </div>

                    <div>
                        <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200"
                            type="submit" name="login">
                            <i class="fas fa-sign-in-alt mr-2"></i>Masuk
                        </button>
                    </div>

                    <div class="text-center">
                        <p class="text-gray-600">
                            Belum punya akun? 
                            <a href="register.php" class="text-blue-600 hover:text-blue-800 font-medium">
                                Daftar sekarang
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>