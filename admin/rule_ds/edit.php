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

// Ambil data rule yang akan diedit
$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : 0;
$query = "SELECT r.*, g.kode_gejala, g.nama_gejala, g.belief_value,
                 p.kode_penyakit, p.nama_penyakit, p.tingkat_keparahan
          FROM rule_ds r 
          JOIN gejala g ON r.id_gejala = g.id_gejala 
          JOIN penyakit p ON r.id_penyakit = p.id_penyakit 
          WHERE r.id_rule = '$id'";
$result = mysqli_query($conn, $query);
$rule = mysqli_fetch_assoc($result);

if (!$rule) {
    header("Location: index.php");
    exit;
}

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nilai_densitas = mysqli_real_escape_string($conn, $_POST['nilai_densitas']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

    // Validasi input
    $errors = [];

    if ($nilai_densitas < 0 || $nilai_densitas > 1) {
        $errors[] = "Nilai densitas harus antara 0 dan 1";
    }

    if (empty($errors)) {
        $query = "UPDATE rule_ds SET 
                    nilai_densitas = '$nilai_densitas',
                    keterangan = '$keterangan'
                  WHERE id_rule = '$id'";

        if (mysqli_query($conn, $query)) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Rule berhasil diperbarui.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.href = 'index.php';
                });
            </script>";
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
    <title>Edit Rule DS - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-50">
    <?php renderAdminSidebar('rule_ds'); ?>

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
                                Rule DS
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
                        <h2 class="text-2xl font-semibold text-gray-800">Edit Rule Dempster-Shafer</h2>
                        <p class="text-gray-600 mt-1">Edit rule untuk gejala
                            <?= htmlspecialchars($rule['kode_gejala']) ?>
                            dan penyakit <?= htmlspecialchars($rule['kode_penyakit']) ?></p>
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
                    <!-- Informasi Gejala Section -->
                    <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                        <h3 class="font-medium text-gray-900 mb-2">
                            <i class="fas fa-file-medical text-blue-500 mr-2"></i>Informasi Gejala
                        </h3>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="fas fa-notes-medical text-gray-400"></i>
                            </div>
                            <input type="text"
                                value="<?= htmlspecialchars($rule['kode_gejala'] . ' - ' . $rule['nama_gejala']) ?>"
                                class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full pl-10 p-2.5"
                                readonly>
                        </div>
                        <p class="text-sm text-gray-500">
                            <i class="fas fa-chart-line text-gray-400 mr-2"></i>
                            Belief value: <?= number_format($rule['belief_value'], 2) ?>
                        </p>
                    </div>

                    <!-- Informasi Penyakit Section -->
                    <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                        <h3 class="font-medium text-gray-900 mb-2">
                            <i class="fas fa-virus text-red-500 mr-2"></i>Informasi Penyakit
                        </h3>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="fas fa-disease text-gray-400"></i>
                            </div>
                            <input type="text"
                                value="<?= htmlspecialchars($rule['kode_penyakit'] . ' - ' . $rule['nama_penyakit']) ?>"
                                class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full pl-10 p-2.5"
                                readonly>
                        </div>
                        <p class="text-sm text-gray-500">
                            <i class="fas fa-exclamation-triangle text-gray-400 mr-2"></i>
                            Tingkat keparahan: <?= $rule['tingkat_keparahan'] ?>
                        </p>
                    </div>

                    <!-- Nilai Densitas Section -->
                    <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                        <h3 class="font-medium text-gray-900 mb-2">
                            <i class="fas fa-percentage text-green-500 mr-2"></i>Nilai Densitas
                        </h3>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">
                                Nilai Densitas <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="fas fa-calculator text-gray-400"></i>
                                </div>
                                <input type="number" name="nilai_densitas" required step="0.01" min="0" max="1"
                                    value="<?= htmlspecialchars($rule['nilai_densitas']) ?>"
                                    class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
                                    placeholder="Masukkan nilai densitas (0-1)">
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Nilai antara 0 dan 1</p>
                        </div>
                    </div>

                    <!-- Keterangan Section -->
                    <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                        <h3 class="font-medium text-gray-900 mb-2">
                            <i class="fas fa-align-left text-purple-500 mr-2"></i>Keterangan
                        </h3>
                        <textarea name="keterangan" rows="3"
                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                            placeholder="Masukkan keterangan tambahan (opsional)..."><?= htmlspecialchars($rule['keterangan']) ?></textarea>
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
                            Update Rule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>