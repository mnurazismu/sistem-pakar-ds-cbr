<?php
require_once '../config/database.php';
require_once 'functions.php';

if (isLoggedIn()) {
    header("Location: ../index.php");
    exit;
}

$errors = [];

if (isset($_POST['register'])) {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi Nama Lengkap
    if (empty($nama_lengkap)) {
        $errors[] = "Nama lengkap harus diisi!";
    } elseif (strlen($nama_lengkap) < 3) {
        $errors[] = "Nama lengkap minimal 3 karakter!";
    } elseif (!preg_match("/^[a-zA-Z\s]*$/", $nama_lengkap)) {
        $errors[] = "Nama lengkap hanya boleh berisi huruf dan spasi!";
    }
    
    // Validasi Email
    if (empty($email)) {
        $errors[] = "Email harus diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid!";
    } elseif (!preg_match("/@(gmail|yahoo|hotmail)\.(com|co\.id)$/i", $email)) {
        $errors[] = "Gunakan email dari provider yang valid (Gmail, Yahoo, atau Hotmail)!";
    }
    
    // Validasi Password
    if (empty($password)) {
        $errors[] = "Password harus diisi!";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password minimal 8 karakter!";
    } elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $password)) {
        $errors[] = "Password harus mengandung huruf besar, huruf kecil, angka, dan karakter khusus!";
    }
    
    // Validasi Konfirmasi Password
    if ($password !== $confirm_password) {
        $errors[] = "Konfirmasi password tidak sesuai!";
    }
    
    // Jika tidak ada error, proses registrasi
    if (empty($errors)) {
        $result = register($nama_lengkap, $email, $password);
        
        if ($result === true) {
            echo '
            <script src="../src/jquery-3.6.3.min.js"></script>
            <script src="../src/sweetalert2.all.min.js"></script>
            <script>
            $(document).ready(function() {
                Swal.fire({
                    position: "top-center",
                    icon: "success",
                    title: "Registrasi Berhasil!",
                    text: "Silahkan login untuk melanjutkan.",
                    showConfirmButton: false,
                    timer: 2000
                }).then(function() {
                    window.location.href = "login.php";
                });
            });
            </script>';
            exit;
        } else {
            $errors[] = $result;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sistem Pakar Diagnosa Penyakit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Left Side - Information -->
        <div class="hidden lg:flex lg:w-1/2 bg-blue-600 text-white p-12 flex-col justify-between">
            <div>
                <h1 class="text-4xl font-bold mb-4">Bergabung dengan Sistem Pakar</h1>
                <p class="text-lg mb-8">Dapatkan akses ke fitur diagnosis penyakit menggunakan metode DS & CBR</p>
                
                <div class="space-y-6">
                    <div class="flex items-start space-x-4">
                        <div class="bg-blue-500 p-3 rounded-lg">
                            <i class="fas fa-user-plus text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-xl mb-1">Daftar Gratis</h3>
                            <p class="text-blue-100">Buat akun Anda secara gratis dan mulai menggunakan sistem pakar.</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="bg-blue-500 p-3 rounded-lg">
                            <i class="fas fa-shield-alt text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-xl mb-1">Keamanan Data</h3>
                            <p class="text-blue-100">Data Anda aman dan terenkripsi dengan sistem keamanan terkini.</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="bg-blue-500 p-3 rounded-lg">
                            <i class="fas fa-clock text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-xl mb-1">Akses 24/7</h3>
                            <p class="text-blue-100">Akses sistem kapan saja dan di mana saja sesuai kebutuhan Anda.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-sm text-blue-100">
                &copy; <?= date('Y') ?> Sistem Pakar Diagnosa Penyakit. All rights reserved.
            </div>
        </div>

        <!-- Right Side - Register Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
            <div class="max-w-md w-full">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Buat Akun Baru</h2>
                    <p class="text-gray-600">Lengkapi data diri Anda untuk membuat akun</p>
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user text-gray-400 mr-2"></i>Nama Lengkap
                        </label>
                        <input type="text" name="nama_lengkap" required
                               value="<?= htmlspecialchars($nama_lengkap ?? '') ?>"
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope text-gray-400 mr-2"></i>Email
                        </label>
                        <input type="email" name="email" required
                               value="<?= htmlspecialchars($email ?? '') ?>"
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-sm text-gray-500">Gunakan email dari Gmail, Yahoo, atau Hotmail</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock text-gray-400 mr-2"></i>Password
                        </label>
                        <input type="password" name="password" id="password" required
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <div id="password-strength" class="mt-1 text-sm"></div>
                        <p class="mt-1 text-sm text-gray-500">Minimal 8 karakter, kombinasi huruf besar, kecil, angka, dan simbol</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-check-circle text-gray-400 mr-2"></i>Konfirmasi Password
                        </label>
                        <input type="password" name="confirm_password" required
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <button type="submit" name="register"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                            <i class="fas fa-user-plus mr-2"></i>Daftar Sekarang
                        </button>
                    </div>

                    <div class="text-center">
                        <p class="text-gray-600">
                            Sudah punya akun? 
                            <a href="login.php" class="text-blue-600 hover:text-blue-800 font-medium">
                                Masuk di sini
                            </a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('registerForm');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const passwordStrength = document.getElementById('password-strength');

        // Validasi kekuatan password real-time
        password.addEventListener('input', function() {
            const value = this.value;
            let strength = 0;
            let message = '';

            if (value.length >= 8) strength++;
            if (value.match(/[a-z]+/)) strength++;
            if (value.match(/[A-Z]+/)) strength++;
            if (value.match(/[0-9]+/)) strength++;
            if (value.match(/[@$!%*?&]+/)) strength++;

            switch(strength) {
                case 0:
                case 1:
                    message = '<span class="text-red-500">Sangat Lemah</span>';
                    break;
                case 2:
                    message = '<span class="text-orange-500">Lemah</span>';
                    break;
                case 3:
                    message = '<span class="text-yellow-500">Sedang</span>';
                    break;
                case 4:
                    message = '<span class="text-blue-500">Kuat</span>';
                    break;
                case 5:
                    message = '<span class="text-green-500">Sangat Kuat</span>';
                    break;
            }

            passwordStrength.innerHTML = `Kekuatan Password: ${message}`;
        });

        // Validasi konfirmasi password real-time
        confirmPassword.addEventListener('input', function() {
            if (this.value !== password.value) {
                this.setCustomValidity('Password tidak cocok');
                this.reportValidity();
            } else {
                this.setCustomValidity('');
            }
        });
    });
    </script>
</body>

</html>