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

// Ambil data pertanyaan yang akan diedit
$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : 0;
$query = "SELECT * FROM pertanyaan WHERE id_pertanyaan = '$id'";
$result = mysqli_query($conn, $query);
$pertanyaan = mysqli_fetch_assoc($result);

if (!$pertanyaan) {
    header("Location: index.php");
    exit;
}

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
    
    // Cek kode pertanyaan unik (kecuali untuk data ini sendiri)
    $check_kode = mysqli_query($conn, "SELECT id_pertanyaan FROM pertanyaan WHERE kode_pertanyaan = '$kode_pertanyaan' AND id_pertanyaan != '$id'");
    if (mysqli_num_rows($check_kode) > 0) {
        $errors[] = "Kode pertanyaan sudah digunakan!";
    }
    
    if (empty($errors)) {
        $query = "UPDATE pertanyaan SET 
                    kode_pertanyaan = '$kode_pertanyaan',
                    isi_pertanyaan = '$isi_pertanyaan',
                    id_gejala = '$id_gejala',
                    jenis_input = '$jenis_input',
                    urutan = '$urutan',
                    kategori = '$kategori',
                    keterangan = '$keterangan'
                  WHERE id_pertanyaan = '$id'";
        
        if (mysqli_query($conn, $query)) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Pertanyaan berhasil diperbarui.',
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
    <title>Edit Pertanyaan - Sistem Pakar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
</head>
<body class="bg-gray-50">
    <?php renderAdminSidebar('pertanyaan'); ?>
    
    <!-- Main Content -->
    <div class="p-4 sm:ml-64">
        <div class="p-4">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Edit Pertanyaan</h2>
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
                    <div class="grid grid-cols-2 gap-6">
                        <!-- Kode Pertanyaan -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">
                                Kode Pertanyaan <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="kode_pertanyaan" required
                                   value="<?= htmlspecialchars($pertanyaan['kode_pertanyaan']) ?>"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>

                        <!-- Urutan -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">
                                Urutan <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="urutan" required min="1"
                                   value="<?= htmlspecialchars($pertanyaan['urutan']) ?>"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>

                        <!-- Gejala Terkait -->
                        <div class="col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-900">
                                Gejala Terkait <span class="text-red-500">*</span>
                            </label>
                            <select name="id_gejala" required
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="">Pilih Gejala</option>
                                <?php 
                                mysqli_data_seek($result_gejala, 0);
                                while ($gejala = mysqli_fetch_assoc($result_gejala)): 
                                ?>
                                    <option value="<?= $gejala['id_gejala'] ?>" 
                                            <?= $gejala['id_gejala'] == $pertanyaan['id_gejala'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($gejala['kode_gejala'] . ' - ' . $gejala['nama_gejala']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Isi Pertanyaan -->
                        <div class="col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-900">
                                Isi Pertanyaan <span class="text-red-500">*</span>
                            </label>
                            <textarea name="isi_pertanyaan" required rows="3"
                                      class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"><?= htmlspecialchars($pertanyaan['isi_pertanyaan']) ?></textarea>
                        </div>

                        <!-- Jenis Input -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">
                                Jenis Input <span class="text-red-500">*</span>
                            </label>
                            <select name="jenis_input" required
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="">Pilih Jenis Input</option>
                                <option value="checkbox" <?= $pertanyaan['jenis_input'] == 'checkbox' ? 'selected' : '' ?>>Checkbox</option>
                                <option value="radio" <?= $pertanyaan['jenis_input'] == 'radio' ? 'selected' : '' ?>>Radio Button</option>
                                <option value="range" <?= $pertanyaan['jenis_input'] == 'range' ? 'selected' : '' ?>>Range/Slider</option>
                                <option value="select" <?= $pertanyaan['jenis_input'] == 'select' ? 'selected' : '' ?>>Select/Dropdown</option>
                            </select>
                        </div>

                        <!-- Kategori -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">
                                Kategori
                            </label>
                            <input type="text" name="kategori"
                                   value="<?= htmlspecialchars($pertanyaan['kategori']) ?>"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>

                        <!-- Keterangan -->
                        <div class="col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-900">
                                Keterangan
                            </label>
                            <textarea name="keterangan" rows="2"
                                      class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"><?= htmlspecialchars($pertanyaan['keterangan']) ?></textarea>
                        </div>
                    </div>

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