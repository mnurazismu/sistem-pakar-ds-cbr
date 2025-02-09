<?php
require_once '../../config/database.php';
require_once '../../auth/functions.php';
require_once '../components/sidebar.php';

// Cek login dan role
if (!isLoggedIn()) {
    header("Location: ../../auth/login.php");
    exit;
} elseif (!isAdmin()) {
    header("Location: ../../user/dashboard.php");
    exit;
}

if (isset($_POST['submit'])) {
    $kode_penyakit = trim($_POST['kode_penyakit']);
    $nama_penyakit = trim($_POST['nama_penyakit']);
    $deskripsi = trim($_POST['deskripsi']);
    $tingkat_keparahan = $_POST['tingkat_keparahan'];
    
    $errors = [];
    
    // Validasi input
    if (empty($kode_penyakit)) {
        $errors[] = "Kode penyakit harus diisi!";
    }
    
    if (empty($nama_penyakit)) {
        $errors[] = "Nama penyakit harus diisi!";
    }

    if (!in_array($tingkat_keparahan, ['Ringan', 'Sedang', 'Berat'])) {
        $errors[] = "Tingkat keparahan tidak valid!";
    }
    
    // Cek duplikasi kode penyakit
    $query_check = "SELECT id_penyakit FROM penyakit WHERE kode_penyakit = ?";
    $stmt_check = mysqli_prepare($conn, $query_check);
    mysqli_stmt_bind_param($stmt_check, "s", $kode_penyakit);
    mysqli_stmt_execute($stmt_check);
    if (mysqli_stmt_fetch($stmt_check)) {
        $errors[] = "Kode penyakit sudah digunakan!";
    }
    
    if (empty($errors)) {
        $query = "INSERT INTO penyakit (kode_penyakit, nama_penyakit, deskripsi, tingkat_keparahan) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $kode_penyakit, $nama_penyakit, $deskripsi, $tingkat_keparahan);
        
        if (mysqli_stmt_execute($stmt)) {
            echo '
            <script src="../../src/jquery-3.6.3.min.js"></script>
            <script src="../../src/sweetalert2.all.min.js"></script>
            <script>
            $(document).ready(function() {
                Swal.fire({
                    position: "top-center",
                    icon: "success",
                    title: "Data penyakit berhasil ditambahkan!",
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
                    title: "Gagal menambahkan data penyakit!",
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.href = "index.php";
                });
            });
            </script>';
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Penyakit - Sistem Pakar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
</head>
<body class="bg-gray-50">
    <?php renderAdminSidebar('penyakit'); ?>
    
    <div class="p-4 sm:ml-64">
        <div class="p-4">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Tambah Penyakit</h2>
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
                        <label for="kode_penyakit" class="block text-sm font-medium text-gray-700">
                            Kode Penyakit <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="kode_penyakit" name="kode_penyakit" 
                               value="<?= isset($_POST['kode_penyakit']) ? htmlspecialchars($_POST['kode_penyakit']) : '' ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Contoh: P001">
                    </div>

                    <div>
                        <label for="nama_penyakit" class="block text-sm font-medium text-gray-700">
                            Nama Penyakit <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nama_penyakit" name="nama_penyakit"
                               value="<?= isset($_POST['nama_penyakit']) ? htmlspecialchars($_POST['nama_penyakit']) : '' ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="deskripsi" class="block text-sm font-medium text-gray-700">
                            Deskripsi
                        </label>
                        <textarea id="deskripsi" name="deskripsi" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?= isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : '' ?></textarea>
                    </div>

                    <div>
                        <label for="tingkat_keparahan" class="block text-sm font-medium text-gray-700">
                            Tingkat Keparahan <span class="text-red-500">*</span>
                        </label>
                        <select id="tingkat_keparahan" name="tingkat_keparahan"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Pilih Tingkat Keparahan</option>
                            <option value="Ringan" <?= (isset($_POST['tingkat_keparahan']) && $_POST['tingkat_keparahan'] === 'Ringan') ? 'selected' : '' ?>>Ringan</option>
                            <option value="Sedang" <?= (isset($_POST['tingkat_keparahan']) && $_POST['tingkat_keparahan'] === 'Sedang') ? 'selected' : '' ?>>Sedang</option>
                            <option value="Berat" <?= (isset($_POST['tingkat_keparahan']) && $_POST['tingkat_keparahan'] === 'Berat') ? 'selected' : '' ?>>Berat</option>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="index.php" 
                           class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Batal
                        </a>
                        <button type="submit" name="submit"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>
</html>