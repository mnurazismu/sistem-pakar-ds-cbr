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

$id_user = $_GET['id'];
$query = "SELECT * FROM users WHERE id_user = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['update'])) {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);
    $tipe_user = $_POST['tipe_user'];
    $password = $_POST['password'];
    
    $errors = [];
    
    // Validasi input
    if (empty($nama_lengkap)) {
        $errors[] = "Nama lengkap harus diisi!";
    }
    
    if (empty($email)) {
        $errors[] = "Email harus diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid!";
    }
    
    // Cek email duplikat kecuali untuk user yang sedang diedit
    $query_check = "SELECT id_user FROM users WHERE email = ? AND id_user != ?";
    $stmt_check = mysqli_prepare($conn, $query_check);
    mysqli_stmt_bind_param($stmt_check, "si", $email, $id_user);
    mysqli_stmt_execute($stmt_check);
    if (mysqli_stmt_fetch($stmt_check)) {
        $errors[] = "Email sudah digunakan!";
    }
    
    if (empty($errors)) {
        if (!empty($password)) {
            // Update dengan password baru
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $query_update = "UPDATE users SET nama_lengkap = ?, email = ?, tipe_user = ?, password = ? WHERE id_user = ?";
            $stmt_update = mysqli_prepare($conn, $query_update);
            mysqli_stmt_bind_param($stmt_update, "ssssi", $nama_lengkap, $email, $tipe_user, $password_hash, $id_user);
        } else {
            // Update tanpa password
            $query_update = "UPDATE users SET nama_lengkap = ?, email = ?, tipe_user = ? WHERE id_user = ?";
            $stmt_update = mysqli_prepare($conn, $query_update);
            mysqli_stmt_bind_param($stmt_update, "sssi", $nama_lengkap, $email, $tipe_user, $id_user);
        }
        
        if (mysqli_stmt_execute($stmt_update)) {
            echo '
            <script src="../../src/jquery-3.6.3.min.js"></script>
            <script src="../../src/sweetalert2.all.min.js"></script>
            <script>
            $(document).ready(function() {
                Swal.fire({
                    position: "top-center",
                    icon: "success",
                    title: "Data berhasil diupdate!",
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.href = "index.php";
                });
            });
            </script>';
            exit;
        } else {
            $errors[] = "Gagal mengupdate data!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Sistem Pakar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-50">
    <div class="p-4">
        <div class="p-4">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Edit User</h2>
                    <a href="index.php" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <ul class="list-disc list-inside">
                            <?php foreach ($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label for="nama_lengkap" class="block text-sm font-medium text-gray-700">
                            Nama Lengkap
                        </label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" 
                               value="<?= htmlspecialchars($user['nama_lengkap']) ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Email
                        </label>
                        <input type="email" id="email" name="email" 
                               value="<?= htmlspecialchars($user['email']) ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="tipe_user" class="block text-sm font-medium text-gray-700">
                            Role
                        </label>
                        <select id="tipe_user" name="tipe_user" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="User" <?= $user['tipe_user'] === 'User' ? 'selected' : '' ?>>User</option>
                            <option value="Admin" <?= $user['tipe_user'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Password Baru (kosongkan jika tidak ingin mengubah)
                        </label>
                        <input type="password" id="password" name="password"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="index.php" 
                           class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Batal
                        </a>
                        <button type="submit" name="update"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>
</html>