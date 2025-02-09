<?php
// admin/create_admin.php
require_once '../config/database.php';
require_once '../auth/functions.php';

// Token yang valid (dalam praktik nyata, sebaiknya disimpan di environment variable)
$VALID_ADMIN_TOKEN = "admin_secret_token_2024";
$error = null;
$success = null;

if (isset($_POST['register_admin'])) {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $token = $_POST['admin_token'];

    // Validasi token
    if ($token !== $VALID_ADMIN_TOKEN) {
        $error = "Token admin tidak valid!";
    }
    // Validasi input
    else if (empty($nama_lengkap)) {
        $error = "Nama lengkap harus diisi!";
    } else if (empty($email)) {
        $error = "Email harus diisi!";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } else if (empty($password)) {
        $error = "Password harus diisi!";
    } else if (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else if ($password !== $confirm_password) {
        $error = "Konfirmasi password tidak sesuai!";
    }

    // Jika tidak ada error, proses registrasi admin
    if (!$error) {
        $result = createAdmin($nama_lengkap, $email, $password);

        if ($result === true) {
            $success = "Akun admin berhasil dibuat! Silahkan login.";
        } else {
            $error = $result;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Admin - Sistem Pakar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../src/jquery-3.6.3.min.js"></script>
    <script src="../src/sweetalert2.all.min.js"></script>
</head>

<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800">
                    Daftar Akun Admin
                </h2>
                <p class="mt-2 text-gray-600">
                    Khusus untuk pendaftaran admin sistem
                </p>
            </div>

            <?php if ($error): ?>
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    <?= $success ?>
                </div>
                <script>
                    setTimeout(function() {
                        window.location.href = '../auth/login.php';
                    }, 2000);
                </script>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">
                <!-- Nama Lengkap -->
                <div>
                    <label for="nama_lengkap" class="block text-sm font-medium text-gray-700">
                        Nama Lengkap
                    </label>
                    <input id="nama_lengkap"
                        name="nama_lengkap"
                        type="text"
                        required
                        value="<?= htmlspecialchars($nama_lengkap ?? '') ?>"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        Email
                    </label>
                    <input id="email"
                        name="email"
                        type="email"
                        required
                        value="<?= htmlspecialchars($email ?? '') ?>"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Password
                    </label>
                    <input id="password"
                        name="password"
                        type="password"
                        required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">
                        Konfirmasi Password
                    </label>
                    <input id="confirm_password"
                        name="confirm_password"
                        type="password"
                        required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Token Admin -->
                <div>
                    <label for="admin_token" class="block text-sm font-medium text-gray-700">
                        Token Admin
                    </label>
                    <input id="admin_token"
                        name="admin_token"
                        type="password"
                        required
                        placeholder="Masukkan token admin"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-sm text-gray-500">
                        Token diperlukan untuk verifikasi pendaftaran admin
                    </p>
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit"
                        name="register_admin"
                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Daftar Admin
                    </button>
                </div>

                <div class="text-center">
                    <a href="../auth/login.php" class="text-sm text-blue-600 hover:text-blue-500">
                        Kembali ke halaman login
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Validasi password match secara real-time
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');

        function validatePassword() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Password tidak cocok');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }

        password.onchange = validatePassword;
        confirmPassword.onkeyup = validatePassword;
    </script>
</body>

</html>