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

// Ambil data penyakit untuk dropdown
$query_penyakit = "SELECT * FROM penyakit ORDER BY kode_penyakit ASC";
$result_penyakit = mysqli_query($conn, $query_penyakit);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_solusi = mysqli_real_escape_string($conn, $_POST['kode_solusi']);
    $nama_solusi = mysqli_real_escape_string($conn, $_POST['nama_solusi']);
    $deskripsi_solusi = mysqli_real_escape_string($conn, $_POST['deskripsi_solusi']);
    $kategori_solusi = mysqli_real_escape_string($conn, $_POST['kategori_solusi']);
    $penyakit_terkait = isset($_POST['penyakit']) ? $_POST['penyakit'] : [];
    $urutan_solusi = isset($_POST['urutan']) ? $_POST['urutan'] : [];

    // Validasi input
    $errors = [];

    // Cek kode solusi unik
    $check_query = "SELECT kode_solusi FROM solusi WHERE kode_solusi = '$kode_solusi'";
    $check_result = mysqli_query($conn, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        $errors[] = "Kode solusi sudah digunakan!";
    }

    if (empty($kode_solusi) || empty($nama_solusi) || empty($deskripsi_solusi)) {
        $errors[] = "Semua field wajib diisi!";
    }

    if (empty($errors)) {
        mysqli_begin_transaction($conn);
        try {
            // Insert ke tabel solusi
            $query = "INSERT INTO solusi (kode_solusi, nama_solusi, deskripsi_solusi, kategori_solusi) 
                     VALUES ('$kode_solusi', '$nama_solusi', '$deskripsi_solusi', '$kategori_solusi')";
            mysqli_query($conn, $query);
            $id_solusi = mysqli_insert_id($conn);

            // Insert ke tabel penyakit_solusi
            foreach ($penyakit_terkait as $index => $id_penyakit) {
                $urutan = $urutan_solusi[$index];
                $query_relasi = "INSERT INTO penyakit_solusi (id_penyakit, id_solusi, urutan_solusi) 
                                VALUES ('$id_penyakit', '$id_solusi', '$urutan')";
                mysqli_query($conn, $query_relasi);
            }

            mysqli_commit($conn);
            echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Solusi berhasil ditambahkan.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function() {
                        window.location.href = 'index.php';
                    });
                  </script>";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $errors[] = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Solusi - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-50">
    <?php renderAdminSidebar('solusi'); ?>

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
                                Solusi
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
                        <h2 class="text-2xl font-semibold text-gray-800">Tambah Solusi</h2>
                        <p class="text-gray-600 mt-1">Tambahkan solusi baru untuk penanganan penyakit</p>
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

                            <!-- Kode Solusi -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Kode Solusi <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-code text-gray-400"></i>
                                    </div>
                                    <input type="text" name="kode_solusi" required
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
                                        placeholder="Contoh: S001">
                                </div>
                            </div>

                            <!-- Nama Solusi -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Nama Solusi <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-lightbulb text-gray-400"></i>
                                    </div>
                                    <input type="text" name="nama_solusi" required
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
                                        placeholder="Masukkan nama solusi">
                                </div>
                            </div>
                        </div>

                        <!-- Kategori Section -->
                        <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                            <h3 class="font-medium text-gray-900 mb-2">
                                <i class="fas fa-tag text-yellow-500 mr-2"></i>Kategori
                            </h3>

                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Kategori Solusi <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-list text-gray-400"></i>
                                    </div>
                                    <select name="kategori_solusi" required
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5">
                                        <option value="">Pilih Kategori</option>
                                        <option value="Pencegahan">Pencegahan</option>
                                        <option value="Pengobatan">Pengobatan</option>
                                        <option value="Perawatan">Perawatan</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Deskripsi Section -->
                        <div class="md:col-span-2 bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-medium text-gray-900 mb-4">
                                <i class="fas fa-align-left text-purple-500 mr-2"></i>Deskripsi Solusi
                            </h3>
                            <textarea name="deskripsi_solusi" required rows="4"
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                placeholder="Masukkan deskripsi atau penjelasan tentang solusi..."></textarea>
                        </div>

                        <!-- Penyakit Terkait Section -->
                        <div class="md:col-span-2 bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-medium text-gray-900 mb-4">
                                <i class="fas fa-virus text-red-500 mr-2"></i>Penyakit Terkait
                            </h3>
                            <div id="penyakitContainer" class="space-y-4">
                                <div class="flex gap-4">
                                    <div class="relative flex-1">
                                        <div
                                            class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                            <i class="fas fa-virus text-gray-400"></i>
                                        </div>
                                        <select name="penyakit[]" required
                                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5">
                                            <option value="">Pilih Penyakit</option>
                                            <?php while ($penyakit = mysqli_fetch_assoc($result_penyakit)): ?>
                                            <option value="<?= $penyakit['id_penyakit'] ?>">
                                                <?= htmlspecialchars($penyakit['kode_penyakit'] . ' - ' . $penyakit['nama_penyakit']) ?>
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="relative w-32">
                                        <div
                                            class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                            <i class="fas fa-sort-numeric-down text-gray-400"></i>
                                        </div>
                                        <input type="number" name="urutan[]" required min="1"
                                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
                                            placeholder="Urutan">
                                    </div>
                                    <button type="button" onclick="tambahPenyakit()"
                                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                                        <i class="fas fa-plus mr-2"></i>
                                        Tambah
                                    </button>
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
                            Simpan Solusi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function tambahPenyakit() {
        const container = document.getElementById('penyakitContainer');
        const newRow = container.children[0].cloneNode(true);

        // Reset values
        newRow.querySelector('select').value = '';
        newRow.querySelector('input[type="number"]').value = '';

        // Replace add button with remove button
        const addBtn = newRow.querySelector('button');
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className =
            'inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700';
        removeBtn.innerHTML = '<i class="fas fa-minus mr-2"></i>Hapus';
        removeBtn.onclick = function() {
            container.removeChild(newRow);
        };
        addBtn.parentNode.replaceChild(removeBtn, addBtn);

        container.appendChild(newRow);
    }
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>