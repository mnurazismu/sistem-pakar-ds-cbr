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

// Cek id fitur
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_fitur = mysqli_real_escape_string($conn, $_GET['id']);

// Ambil data fitur
$query_fitur = "SELECT * FROM fitur_kasus WHERE id_fitur = '$id_fitur'";
$result_fitur = mysqli_query($conn, $query_fitur);

if (mysqli_num_rows($result_fitur) == 0) {
    header("Location: index.php");
    exit;
}

$fitur = mysqli_fetch_assoc($result_fitur);

// Ambil data kasus untuk dropdown
$query_kasus = "SELECT * FROM kasus ORDER BY kode_kasus ASC";
$result_kasus = mysqli_query($conn, $query_kasus);

// Ambil data gejala untuk dropdown
$query_gejala = "SELECT * FROM gejala ORDER BY nama_gejala ASC";
$result_gejala = mysqli_query($conn, $query_gejala);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_kasus = mysqli_real_escape_string($conn, $_POST['id_kasus']);
    $id_gejala = mysqli_real_escape_string($conn, $_POST['id_gejala']);
    $nilai_fitur = mysqli_real_escape_string($conn, $_POST['nilai_fitur']);
    $bobot_fitur = mysqli_real_escape_string($conn, $_POST['bobot_fitur']);

    // Validasi input
    $errors = [];

    if (empty($id_kasus) || empty($id_gejala) || empty($nilai_fitur) || empty($bobot_fitur)) {
        $errors[] = "Semua field wajib diisi!";
    }

    // Validasi nilai dan bobot (harus berupa angka antara 0.1 dan 1)
    if (!is_numeric($nilai_fitur) || $nilai_fitur < 0.1 || $nilai_fitur > 1) {
        $errors[] = "Nilai fitur harus berupa angka antara 0.1 dan 1!";
    }

    if (!is_numeric($bobot_fitur) || $bobot_fitur < 0.1 || $bobot_fitur > 1) {
        $errors[] = "Bobot fitur harus berupa angka antara 0.1 dan 1!";
    }

    if (empty($errors)) {
        // Konversi ke format yang sesuai dengan tipe data di database
        $nilai_fitur = number_format((float) $nilai_fitur, 2, '.', '');
        $bobot_fitur = number_format((float) $bobot_fitur, 2, '.', '');

        $query = "UPDATE fitur_kasus 
                 SET id_kasus = ?, 
                     id_gejala = ?,
                     nilai_fitur = ?, 
                     bobot_fitur = ?,
                     updated_at = NOW()
                 WHERE id_fitur = ?";

        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "iiddi", $id_kasus, $id_gejala, $nilai_fitur, $bobot_fitur, $id_fitur);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => 'Fitur kasus berhasil diperbarui.'
            ];
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Terjadi kesalahan: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Fitur Kasus - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-50">
    <?php renderAdminSidebar('fitur_kasus'); ?>

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
                                Fitur Kasus
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
                        <h2 class="text-2xl font-semibold text-gray-800">Edit Fitur Kasus</h2>
                        <p class="text-gray-600 mt-1">Edit informasi fitur untuk kasus</p>
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
                        <!-- Kasus Section -->
                        <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                            <h3 class="font-medium text-gray-900 mb-2">
                                <i class="fas fa-folder-open text-blue-500 mr-2"></i>Informasi Kasus
                            </h3>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Kasus <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-folder text-gray-400"></i>
                                    </div>
                                    <select name="id_kasus" required
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5">
                                        <option value="">Pilih Kasus</option>
                                        <?php while ($kasus = mysqli_fetch_assoc($result_kasus)): ?>
                                        <option value="<?= $kasus['id_kasus'] ?>"
                                            <?= $fitur['id_kasus'] == $kasus['id_kasus'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($kasus['kode_kasus'] . ' - ' . $kasus['deskripsi_kasus']) ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Gejala Section -->
                        <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                            <h3 class="font-medium text-gray-900 mb-2">
                                <i class="fas fa-file-medical text-red-500 mr-2"></i>Informasi Gejala
                            </h3>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Gejala <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-notes-medical text-gray-400"></i>
                                    </div>
                                    <select name="id_gejala" required
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5">
                                        <option value="">Pilih Gejala</option>
                                        <?php while ($gejala = mysqli_fetch_assoc($result_gejala)): ?>
                                        <option value="<?= $gejala['id_gejala'] ?>"
                                            <?= $fitur['id_gejala'] == $gejala['id_gejala'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($gejala['nama_gejala']) ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Nilai dan Bobot Section -->
                        <div class="md:col-span-2 bg-gray-50 p-4 rounded-lg space-y-4">
                            <h3 class="font-medium text-gray-900 mb-2">
                                <i class="fas fa-sliders-h text-green-500 mr-2"></i>Nilai dan Bobot
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-900">
                                        Nilai Fitur <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                            <i class="fas fa-chart-line text-gray-400"></i>
                                        </div>
                                        <input type="number" name="nilai_fitur" required step="0.1" min="0.1" max="1"
                                            value="<?= htmlspecialchars($fitur['nilai_fitur']) ?>"
                                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
                                            placeholder="Masukkan nilai fitur (0.1-1)">
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">Nilai antara 0.1 dan 1, dimana 1 berarti
                                        sangat mirip</p>
                                </div>

                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-900">
                                        Bobot Fitur <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div
                                            class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                            <i class="fas fa-weight text-gray-400"></i>
                                        </div>
                                        <input type="number" name="bobot_fitur" required step="0.1" min="0.1" max="1"
                                            value="<?= htmlspecialchars($fitur['bobot_fitur']) ?>"
                                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
                                            placeholder="Masukkan bobot fitur (0.1-1)">
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">Nilai antara 0.1 dan 1, dimana 1 berarti
                                        sangat penting</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-6">
                        <a href="index.php"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:ring-2 focus:ring-offset-2 focus:ring-gray-200">
                            <i class="fas fa-times mr-2"></i>
                            Batal
                        </a>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-save mr-2"></i>
                            Update Fitur
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>