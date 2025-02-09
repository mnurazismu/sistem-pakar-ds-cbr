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

$id_gejala = $_GET['id'];
$query = "SELECT * FROM gejala WHERE id_gejala = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id_gejala);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$gejala = mysqli_fetch_assoc($result);

if (!$gejala) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['update'])) {
    $kode_gejala = trim($_POST['kode_gejala']);
    $nama_gejala = trim($_POST['nama_gejala']);
    $keterangan = trim($_POST['keterangan']);
    $belief_value = trim($_POST['belief_value']);

    $errors = [];

    // Validasi input
    if (empty($kode_gejala)) {
        $errors[] = "Kode gejala harus diisi!";
    }

    if (empty($nama_gejala)) {
        $errors[] = "Nama gejala harus diisi!";
    }

    if (!is_numeric($belief_value) || $belief_value < 0 || $belief_value > 1) {
        $errors[] = "Nilai kepercayaan harus berupa angka antara 0 dan 1!";
    }

    // Cek duplikasi kode gejala kecuali untuk gejala yang sedang diedit
    $query_check = "SELECT id_gejala FROM gejala WHERE kode_gejala = ? AND id_gejala != ?";
    $stmt_check = mysqli_prepare($conn, $query_check);
    mysqli_stmt_bind_param($stmt_check, "si", $kode_gejala, $id_gejala);
    mysqli_stmt_execute($stmt_check);
    if (mysqli_stmt_fetch($stmt_check)) {
        $errors[] = "Kode gejala sudah digunakan!";
    }

    if (empty($errors)) {
        $query = "UPDATE gejala SET kode_gejala = ?, nama_gejala = ?, keterangan = ?, belief_value = ? WHERE id_gejala = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssdi", $kode_gejala, $nama_gejala, $keterangan, $belief_value, $id_gejala);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: index.php?status=success&message=Gejala berhasil diupdate");
            exit;
        } else {
            $errors[] = "Gagal mengupdate data gejala!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Gejala - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-50">
    <?php renderAdminSidebar('gejala'); ?>

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
                                Gejala
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
                        <h2 class="text-2xl font-semibold text-gray-800">Edit Gejala</h2>
                        <p class="text-gray-600 mt-1">Edit informasi gejala dengan kode
                            <?= htmlspecialchars($gejala['kode_gejala']) ?></p>
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

                            <!-- Kode Gejala -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Kode Gejala <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-code text-gray-400"></i>
                                    </div>
                                    <input type="text" name="kode_gejala"
                                        value="<?= htmlspecialchars($gejala['kode_gejala']) ?>"
                                        class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full pl-10 p-2.5"
                                        readonly>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Kode gejala tidak dapat diubah</p>
                            </div>

                            <!-- Nama Gejala -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Nama Gejala <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-file-medical text-gray-400"></i>
                                    </div>
                                    <input type="text" name="nama_gejala" required
                                        value="<?= htmlspecialchars($gejala['nama_gejala']) ?>"
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
                                        placeholder="Masukkan nama gejala">
                                </div>
                            </div>
                        </div>

                        <!-- Nilai Kepercayaan Section -->
                        <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                            <h3 class="font-medium text-gray-900 mb-2">
                                <i class="fas fa-chart-line text-green-500 mr-2"></i>Nilai Kepercayaan
                            </h3>

                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Nilai Kepercayaan <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-percentage text-gray-400"></i>
                                    </div>
                                    <input type="number" name="belief_value" required step="0.1" min="0" max="1"
                                        value="<?= htmlspecialchars($gejala['belief_value']) ?>"
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
                                        placeholder="Masukkan nilai kepercayaan (0-1)">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Nilai antara 0 dan 1</p>
                            </div>
                        </div>

                        <!-- Deskripsi Section -->
                        <div class="md:col-span-2 bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-medium text-gray-900 mb-4">
                                <i class="fas fa-align-left text-purple-500 mr-2"></i>Deskripsi Gejala
                            </h3>
                            <textarea name="keterangan" rows="4"
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                placeholder="Masukkan deskripsi atau penjelasan tentang gejala..."><?= htmlspecialchars($gejala['keterangan']) ?></textarea>
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
                            Update Gejala
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
    <script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '<?= $_GET['message'] ?>',
        showConfirmButton: false,
        timer: 1500
    });
    </script>
    <?php endif; ?>
</body>

</html>