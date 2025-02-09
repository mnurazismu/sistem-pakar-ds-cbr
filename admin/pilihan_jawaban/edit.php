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
    <title>Edit Pilihan Jawaban - Sistem Pakar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
</head>
<body class="bg-gray-50">
    <?php renderAdminSidebar('pilihan_jawaban'); ?>
    
    <!-- Main Content -->
    <div class="p-4 sm:ml-64">
        <div class="p-4">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Edit Pilihan Jawaban</h2>
                    <a href="index.php" class="text-blue-500 hover:text-blue-600">
                        Kembali ke Daftar
                    </a>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                        <ul class="list-disc list-inside">
                            <?php foreach ($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <!-- Informasi Pertanyaan (readonly) -->
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">
                            Pertanyaan
                        </label>
                        <input type="text" value="<?= htmlspecialchars($pilihan['kode_pertanyaan'] . ' - ' . $pilihan['isi_pertanyaan']) ?>" 
                               class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5" 
                               readonly>
                    </div>

                    <?php if ($pilihan['jenis_input'] === 'range'): ?>
                    <!-- Form untuk input range -->
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">
                                Nilai Minimum <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="nilai_minimum" step="any" required
                                   value="<?= htmlspecialchars($pilihan['nilai_minimum']) ?>"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">
                                Nilai Maksimum <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="nilai_maksimum" step="any" required
                                   value="<?= htmlspecialchars($pilihan['nilai_maksimum']) ?>"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">
                                Interval <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="nilai_interval" step="any" required
                                   value="<?= htmlspecialchars($pilihan['nilai_interval']) ?>"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">
                            Bobot Nilai <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="bobot_nilai" required step="0.01" min="0" max="1"
                               value="<?= htmlspecialchars($pilihan['bobot_nilai']) ?>"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    </div>
                    <?php else: ?>
                    <!-- Form untuk input biasa -->
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">
                            Isi Pilihan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="isi_pilihan" required
                               value="<?= htmlspecialchars($pilihan['isi_pilihan']) ?>"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">
                            Bobot Nilai <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="bobot_nilai" required step="0.01" min="0" max="1"
                               value="<?= htmlspecialchars($pilihan['bobot_nilai']) ?>"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">
                            Urutan
                        </label>
                        <input type="number" name="urutan" min="1"
                               value="<?= htmlspecialchars($pilihan['urutan']) ?>"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    </div>
                    <?php endif; ?>

                    <div class="flex justify-end space-x-3">
                        <a href="index.php" 
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                            Batal
                        </a>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>
</html>