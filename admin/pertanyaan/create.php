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

// Ambil data gejala untuk dropdown
$query_gejala = "SELECT id_gejala, kode_gejala, nama_gejala FROM gejala ORDER BY kode_gejala ASC";
$result_gejala = mysqli_query($conn, $query_gejala);

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_pertanyaan = mysqli_real_escape_string($conn, $_POST['kode_pertanyaan']);
    $isi_pertanyaan = mysqli_real_escape_string($conn, $_POST['isi_pertanyaan']);
    $id_gejala = mysqli_real_escape_string($conn, $_POST['id_gejala']);
    $jenis_input = mysqli_real_escape_string($conn, $_POST['jenis_input']);
    $urutan = mysqli_real_escape_string($conn, $_POST['urutan']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

    // Validasi input
    $errors = [];

    // Cek kode pertanyaan unik
    $check_kode = mysqli_query($conn, "SELECT id_pertanyaan FROM pertanyaan WHERE kode_pertanyaan = '$kode_pertanyaan'");
    if (mysqli_num_rows($check_kode) > 0) {
        $errors[] = "Kode pertanyaan sudah digunakan!";
    }

    if (empty($errors)) {
        $query = "INSERT INTO pertanyaan (kode_pertanyaan, isi_pertanyaan, id_gejala, jenis_input, urutan, kategori, keterangan, status_aktif) 
                  VALUES ('$kode_pertanyaan', '$isi_pertanyaan', '$id_gejala', '$jenis_input', '$urutan', '$kategori', '$keterangan', 1)";

        if (mysqli_query($conn, $query)) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Pertanyaan berhasil ditambahkan.',
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
    <title>Tambah Pertanyaan - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-50">
    <?php renderAdminSidebar('pertanyaan'); ?>

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
                                Pertanyaan
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-300 mx-2"></i>
                            <span class="text-gray-500">Tambah</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white rounded-lg shadow-md p-6 md:p-8">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-800">Tambah Pertanyaan</h2>
                        <p class="text-gray-600 mt-1">Tambahkan pertanyaan baru ke sistem</p>
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
                        <!-- Kode & Urutan Section -->
                        <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                            <h3 class="font-medium text-gray-900 mb-2">
                                <i class="fas fa-info-circle text-blue-500 mr-2"></i>Informasi Dasar
                            </h3>

                            <!-- Kode Pertanyaan -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Kode Pertanyaan <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-code text-gray-400"></i>
                                    </div>
                                    <input type="text" name="kode_pertanyaan" required
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
                                        placeholder="Contoh: P001">
                                </div>
                            </div>

                            <!-- Urutan -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Urutan <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-sort-numeric-down text-gray-400"></i>
                                    </div>
                                    <input type="number" name="urutan" required min="1"
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5">
                                </div>
                            </div>
                        </div>

                        <!-- Input Type & Category Section -->
                        <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                            <h3 class="font-medium text-gray-900 mb-2">
                                <i class="fas fa-sliders-h text-green-500 mr-2"></i>Pengaturan Input
                            </h3>

                            <!-- Jenis Input -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Jenis Input <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-keyboard text-gray-400"></i>
                                    </div>
                                    <select name="jenis_input" required
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5">
                                        <option value="">Pilih Jenis Input</option>
                                        <option value="checkbox">Checkbox (Pilihan Ganda)</option>
                                        <option value="radio">Radio Button (Pilihan Tunggal)</option>
                                        <option value="range">Range/Slider (Nilai Rentang)</option>
                                        <option value="select">Select/Dropdown (Pilihan Dropdown)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Kategori -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Kategori
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-tag text-gray-400"></i>
                                    </div>
                                    <input type="text" name="kategori"
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
                                        placeholder="Kategori pertanyaan (opsional)">
                                </div>
                            </div>
                        </div>

                        <!-- Gejala Terkait -->
                        <div class="md:col-span-2 bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-medium text-gray-900 mb-4">
                                <i class="fas fa-link text-yellow-500 mr-2"></i>Gejala Terkait
                            </h3>
                            <select name="id_gejala" required
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="">Pilih Gejala</option>
                                <?php while ($gejala = mysqli_fetch_assoc($result_gejala)): ?>
                                <option value="<?= $gejala['id_gejala'] ?>">
                                    <?= htmlspecialchars($gejala['kode_gejala'] . ' - ' . $gejala['nama_gejala']) ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Isi Pertanyaan -->
                        <div class="md:col-span-2 bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-medium text-gray-900 mb-4">
                                <i class="fas fa-question-circle text-red-500 mr-2"></i>Isi Pertanyaan
                            </h3>
                            <textarea name="isi_pertanyaan" required rows="3"
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                placeholder="Masukkan pertanyaan yang akan ditampilkan ke pengguna..."></textarea>
                        </div>

                        <!-- Keterangan -->
                        <div class="md:col-span-2 bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-medium text-gray-900 mb-4">
                                <i class="fas fa-sticky-note text-purple-500 mr-2"></i>Keterangan Tambahan
                            </h3>
                            <textarea name="keterangan" rows="2"
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                placeholder="Tambahkan keterangan atau petunjuk tambahan (opsional)"></textarea>
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
                            Simpan Pertanyaan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>