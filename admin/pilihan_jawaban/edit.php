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

// Ambil data pilihan yang akan diedit
$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : 0;
$query = "SELECT pj.*, p.kode_pertanyaan, p.isi_pertanyaan, p.jenis_input 
          FROM pilihan_jawaban pj 
          JOIN pertanyaan p ON pj.id_pertanyaan = p.id_pertanyaan 
          WHERE pj.id_pilihan = '$id'";
$result = mysqli_query($conn, $query);
$pilihan = mysqli_fetch_assoc($result);

if (!$pilihan) {
    header("Location: index.php");
    exit;
}

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    if ($pilihan['jenis_input'] === 'range') {
        // Proses edit range/slider
        $nilai_minimum = mysqli_real_escape_string($conn, $_POST['nilai_minimum']);
        $nilai_maksimum = mysqli_real_escape_string($conn, $_POST['nilai_maksimum']);
        $nilai_interval = mysqli_real_escape_string($conn, $_POST['nilai_interval']);
        $bobot_nilai = mysqli_real_escape_string($conn, $_POST['bobot_nilai']);

        // Validasi
        if ($nilai_minimum >= $nilai_maksimum) {
            $errors[] = "Nilai minimum harus lebih kecil dari nilai maksimum";
        }
        if ($bobot_nilai < 0 || $bobot_nilai > 1) {
            $errors[] = "Bobot nilai harus antara 0 dan 1";
        }

        if (empty($errors)) {
            $query = "UPDATE pilihan_jawaban SET 
                        nilai_minimum = '$nilai_minimum',
                        nilai_maksimum = '$nilai_maksimum',
                        nilai_interval = '$nilai_interval',
                        bobot_nilai = '$bobot_nilai',
                        isi_pilihan = 'Range dari $nilai_minimum sampai $nilai_maksimum'
                     WHERE id_pilihan = '$id'";
        }
    } else {
        // Proses edit checkbox, radio, atau select
        $isi_pilihan = mysqli_real_escape_string($conn, $_POST['isi_pilihan']);
        $bobot_nilai = mysqli_real_escape_string($conn, $_POST['bobot_nilai']);
        $urutan = mysqli_real_escape_string($conn, $_POST['urutan']);

        // Validasi
        if (empty($isi_pilihan)) {
            $errors[] = "Isi pilihan tidak boleh kosong";
        }
        if ($bobot_nilai < 0 || $bobot_nilai > 1) {
            $errors[] = "Bobot nilai harus antara 0 dan 1";
        }

        if (empty($errors)) {
            $query = "UPDATE pilihan_jawaban SET 
                        isi_pilihan = '$isi_pilihan',
                        bobot_nilai = '$bobot_nilai',
                        urutan = '$urutan'
                     WHERE id_pilihan = '$id'";
        }
    }

    if (empty($errors)) {
        if (mysqli_query($conn, $query)) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Pilihan jawaban berhasil diperbarui.',
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
    <title>Edit Pilihan Jawaban - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-50">
    <?php renderAdminSidebar('pilihan_jawaban'); ?>

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
                                Pilihan Jawaban
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
                        <h2 class="text-2xl font-semibold text-gray-800">Edit Pilihan Jawaban</h2>
                        <p class="text-gray-600 mt-1">Edit pilihan jawaban untuk pertanyaan
                            <?= htmlspecialchars($pilihan['kode_pertanyaan']) ?>
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
                    <!-- Informasi Pertanyaan Section -->
                    <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                        <h3 class="font-medium text-gray-900 mb-2">
                            <i class="fas fa-question-circle text-blue-500 mr-2"></i>Informasi Pertanyaan
                        </h3>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="fas fa-file-alt text-gray-400"></i>
                            </div>
                            <input type="text"
                                value="<?= htmlspecialchars($pilihan['kode_pertanyaan'] . ' - ' . $pilihan['isi_pertanyaan']) ?>"
                                class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full pl-10 p-2.5"
                                readonly>
                        </div>
                    </div>

                    <?php if ($pilihan['jenis_input'] === 'range'): ?>
                    <!-- Range Input Section -->
                    <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                        <h3 class="font-medium text-gray-900 mb-2">
                            <i class="fas fa-sliders-h text-yellow-500 mr-2"></i>Pengaturan Range
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Nilai Minimum -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Nilai Minimum <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-arrow-down text-gray-400"></i>
                                    </div>
                                    <input type="number" name="nilai_minimum" step="any" required
                                        value="<?= htmlspecialchars($pilihan['nilai_minimum']) ?>"
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
                                        placeholder="Masukkan nilai minimum">
                                </div>
                            </div>

                            <!-- Nilai Maksimum -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Nilai Maksimum <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-arrow-up text-gray-400"></i>
                                    </div>
                                    <input type="number" name="nilai_maksimum" step="any" required
                                        value="<?= htmlspecialchars($pilihan['nilai_maksimum']) ?>"
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
                                        placeholder="Masukkan nilai maksimum">
                                </div>
                            </div>

                            <!-- Interval -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Interval <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-arrows-alt-h text-gray-400"></i>
                                    </div>
                                    <input type="number" name="nilai_interval" step="any" required
                                        value="<?= htmlspecialchars($pilihan['nilai_interval']) ?>"
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
                                        placeholder="Masukkan nilai interval">
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Normal Input Section -->
                    <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                        <h3 class="font-medium text-gray-900 mb-2">
                            <i class="fas fa-list text-yellow-500 mr-2"></i>Informasi Pilihan
                        </h3>

                        <!-- Isi Pilihan -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">
                                Isi Pilihan <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="fas fa-font text-gray-400"></i>
                                </div>
                                <input type="text" name="isi_pilihan" required
                                    value="<?= htmlspecialchars($pilihan['isi_pilihan']) ?>"
                                    class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
                                    placeholder="Masukkan isi pilihan">
                            </div>
                        </div>

                        <!-- Urutan -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">
                                Urutan
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="fas fa-sort-numeric-down text-gray-400"></i>
                                </div>
                                <input type="number" name="urutan" min="1"
                                    value="<?= htmlspecialchars($pilihan['urutan']) ?>"
                                    class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
                                    placeholder="Masukkan urutan pilihan">
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Bobot Section -->
                    <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                        <h3 class="font-medium text-gray-900 mb-2">
                            <i class="fas fa-percentage text-green-500 mr-2"></i>Bobot Nilai
                        </h3>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">
                                Bobot Nilai <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="fas fa-weight text-gray-400"></i>
                                </div>
                                <input type="number" name="bobot_nilai" required step="0.01" min="0" max="1"
                                    value="<?= htmlspecialchars($pilihan['bobot_nilai']) ?>"
                                    class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
                                    placeholder="Masukkan bobot nilai (0-1)">
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Nilai antara 0 dan 1</p>
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
                            Update Pilihan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>