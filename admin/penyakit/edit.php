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

$id_penyakit = $_GET['id'];
$query = "SELECT * FROM penyakit WHERE id_penyakit = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id_penyakit);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$penyakit = mysqli_fetch_assoc($result);

if (!$penyakit) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['update'])) {
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

    // Cek duplikasi kode penyakit kecuali untuk penyakit yang sedang diedit
    $query_check = "SELECT id_penyakit FROM penyakit WHERE kode_penyakit = ? AND id_penyakit != ?";
    $stmt_check = mysqli_prepare($conn, $query_check);
    mysqli_stmt_bind_param($stmt_check, "si", $kode_penyakit, $id_penyakit);
    mysqli_stmt_execute($stmt_check);
    if (mysqli_stmt_fetch($stmt_check)) {
        $errors[] = "Kode penyakit sudah digunakan!";
    }

    if (empty($errors)) {
        $query = "UPDATE penyakit SET kode_penyakit = ?, nama_penyakit = ?, deskripsi = ?, tingkat_keparahan = ? WHERE id_penyakit = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssi", $kode_penyakit, $nama_penyakit, $deskripsi, $tingkat_keparahan, $id_penyakit);

        if (mysqli_stmt_execute($stmt)) {
            echo '
            <script src="../../src/jquery-3.6.3.min.js"></script>
            <script src="../../src/sweetalert2.all.min.js"></script>
            <script>
            $(document).ready(function() {
                Swal.fire({
                    position: "top-center",
                    icon: "success",
                    title: "Data penyakit berhasil diupdate!",
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
                    title: "Gagal mengupdate data penyakit!",
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
    <title>Edit Penyakit - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-50">
    <?php renderAdminSidebar('penyakit'); ?>

    <div class="p-4 sm:ml-64">
        <div class="p-4">
            <!-- Breadcrumb -->
            <nav class="flex mb-6" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="../dashboard.php" class="text-gray-700 hover:text-blue-600 inline-flex items-center">
                            <i class="fas fa-home mr-2.5"></i>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-300 mx-2"></i>
                            <a href="index.php" class="text-gray-700 hover:text-blue-600">
                                Penyakit
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-300 mx-2"></i>
                            <span class="text-gray-500">Edit</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white rounded-lg shadow-md p-6 md:p-8">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-800">Edit Penyakit</h2>
                        <p class="text-gray-600 mt-1">Edit informasi penyakit dengan kode
                            <?= htmlspecialchars($penyakit['kode_penyakit']) ?>
                        </p>
                    </div>
                    <a href="index.php" class="inline-flex items-center text-blue-600 hover:text-blue-700">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                </div>

                <?php if (!empty($errors)): ?>
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                        <h3 class="text-red-800 font-medium">Terdapat beberapa kesalahan:</h3>
                    </div>
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                        <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Informasi Dasar Section -->
                        <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                            <h3 class="font-medium text-gray-900 mb-2">
                                <i class="fas fa-info-circle text-blue-500 mr-2"></i>Informasi Dasar
                            </h3>

                            <!-- Kode Penyakit -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Kode Penyakit <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-code text-gray-400"></i>
                                    </div>
                                    <input type="text" name="kode_penyakit"
                                        value="<?= htmlspecialchars($penyakit['kode_penyakit']) ?>"
                                        class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full pl-10 p-2.5"
                                        readonly>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Kode penyakit tidak dapat diubah</p>
                            </div>

                            <!-- Nama Penyakit -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Nama Penyakit <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-virus text-gray-400"></i>
                                    </div>
                                    <input type="text" name="nama_penyakit" required
                                        value="<?= htmlspecialchars($penyakit['nama_penyakit']) ?>"
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
                                        placeholder="Masukkan nama penyakit">
                                </div>
                            </div>
                        </div>

                        <!-- Tingkat Keparahan Section -->
                        <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                            <h3 class="font-medium text-gray-900 mb-2">
                                <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>Tingkat Keparahan
                            </h3>

                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Tingkat Keparahan <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-layer-group text-gray-400"></i>
                                    </div>
                                    <select name="tingkat_keparahan" required
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5">
                                        <option value="Ringan"
                                            <?= $penyakit['tingkat_keparahan'] === 'Ringan' ? 'selected' : '' ?>>Ringan
                                        </option>
                                        <option value="Sedang"
                                            <?= $penyakit['tingkat_keparahan'] === 'Sedang' ? 'selected' : '' ?>>Sedang
                                        </option>
                                        <option value="Berat"
                                            <?= $penyakit['tingkat_keparahan'] === 'Berat' ? 'selected' : '' ?>>Berat
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Deskripsi Section -->
                        <div class="md:col-span-2 bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-medium text-gray-900 mb-4">
                                <i class="fas fa-align-left text-purple-500 mr-2"></i>Deskripsi Penyakit
                            </h3>
                            <textarea name="deskripsi" rows="4"
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                placeholder="Masukkan deskripsi atau penjelasan tentang penyakit..."><?= htmlspecialchars($penyakit['deskripsi']) ?></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-6">
                        <a href="index.php"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:ring-2 focus:ring-offset-2 focus:ring-gray-200">
                            <i class="fas fa-times mr-2"></i>
                            Batal
                        </a>
                        <button type="submit" name="update"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-save mr-2"></i>
                            Update Penyakit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>