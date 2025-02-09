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

// Ambil data pertanyaan untuk dropdown
$query_pertanyaan = "SELECT id_pertanyaan, kode_pertanyaan, isi_pertanyaan, jenis_input 
                     FROM pertanyaan 
                     ORDER BY kode_pertanyaan ASC";
$result_pertanyaan = mysqli_query($conn, $query_pertanyaan);

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pertanyaan = mysqli_real_escape_string($conn, $_POST['id_pertanyaan']);
    $errors = [];

    // Ambil jenis input dari pertanyaan yang dipilih
    $query_jenis = "SELECT jenis_input FROM pertanyaan WHERE id_pertanyaan = '$id_pertanyaan'";
    $result_jenis = mysqli_query($conn, $query_jenis);
    $pertanyaan = mysqli_fetch_assoc($result_jenis);
    $jenis_input = $pertanyaan['jenis_input'];

    if ($jenis_input === 'range') {
        // Proses input range/slider
        $nilai_minimum = mysqli_real_escape_string($conn, $_POST['nilai_minimum']);
        $nilai_maksimum = mysqli_real_escape_string($conn, $_POST['nilai_maksimum']);
        $nilai_interval = mysqli_real_escape_string($conn, $_POST['nilai_interval']);
        $bobot_minimum = mysqli_real_escape_string($conn, $_POST['bobot_minimum']);
        $bobot_maksimum = mysqli_real_escape_string($conn, $_POST['bobot_maksimum']);

        // Validasi
        if ($nilai_minimum >= $nilai_maksimum) {
            $errors[] = "Nilai minimum harus lebih kecil dari nilai maksimum";
        }
        if ($bobot_minimum >= $bobot_maksimum) {
            $errors[] = "Bobot minimum harus lebih kecil dari bobot maksimum";
        }

        if (empty($errors)) {
            $query = "INSERT INTO pilihan_jawaban (id_pertanyaan, isi_pilihan, bobot_nilai, nilai_minimum, nilai_maksimum, nilai_interval) 
                      VALUES ('$id_pertanyaan', 'Range dari $nilai_minimum sampai $nilai_maksimum', '$bobot_maksimum', '$nilai_minimum', '$nilai_maksimum', '$nilai_interval')";
        }
    } else {
        // Proses input checkbox, radio, atau select
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
            $query = "INSERT INTO pilihan_jawaban (id_pertanyaan, isi_pilihan, bobot_nilai, urutan) 
                      VALUES ('$id_pertanyaan', '$isi_pilihan', '$bobot_nilai', '$urutan')";
        }
    }

    if (empty($errors)) {
        if (mysqli_query($conn, $query)) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Pilihan jawaban berhasil ditambahkan.',
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
    <title>Tambah Pilihan Jawaban - Sistem Pakar</title>
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
                    <h2 class="text-xl font-bold text-gray-800">Tambah Pilihan Jawaban</h2>
                    <a href="index.php" class="text-blue-500 hover:text-blue-600">
                        Kembali ke Daftar
                    </a>
                </div>

                <form method="POST" class="space-y-6">
                    <!-- Pertanyaan -->
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">
                            Pertanyaan <span class="text-red-500">*</span>
                        </label>
                        <select name="id_pertanyaan" required id="pertanyaan"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            <option value="">Pilih Pertanyaan</option>
                            <?php while ($pertanyaan = mysqli_fetch_assoc($result_pertanyaan)): ?>
                                <option value="<?= $pertanyaan['id_pertanyaan'] ?>" 
                                        data-jenis="<?= $pertanyaan['jenis_input'] ?>">
                                    <?= htmlspecialchars($pertanyaan['kode_pertanyaan'] . ' - ' . $pertanyaan['isi_pertanyaan']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Form untuk input biasa -->
                    <div id="normalInput">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">
                                Isi Pilihan <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="isi_pilihan" required
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">
                                Bobot Nilai <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="bobot_nilai" required step="0.01" min="0" max="1"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">
                                Urutan
                            </label>
                            <input type="number" name="urutan" min="1"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                    </div>

                    <!-- Form untuk input range -->
                    <div id="rangeInput" class="hidden">
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Nilai Minimum <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="nilai_minimum" step="any"
                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Nilai Maksimum <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="nilai_maksimum" step="any"
                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Interval <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="nilai_interval" step="any"
                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="index.php" 
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                            Batal
                        </a>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600">
                            Simpan Pilihan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Toggle form berdasarkan jenis input
    document.getElementById('pertanyaan').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const jenisInput = selectedOption.getAttribute('data-jenis');
        
        const normalInput = document.getElementById('normalInput');
        const rangeInput = document.getElementById('rangeInput');
        
        if (jenisInput === 'range') {
            normalInput.classList.add('hidden');
            rangeInput.classList.remove('hidden');
        } else {
            normalInput.classList.remove('hidden');
            rangeInput.classList.add('hidden');
        }
    });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>
</html>