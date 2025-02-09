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
    $kode_kasus = mysqli_real_escape_string($conn, $_POST['kode_kasus']);
    $id_penyakit = mysqli_real_escape_string($conn, $_POST['id_penyakit']);
    $deskripsi_kasus = mysqli_real_escape_string($conn, $_POST['deskripsi_kasus']);
    
    // Validasi input
    $errors = [];
    
    // Cek kode kasus unik
    $check_query = "SELECT kode_kasus FROM kasus WHERE kode_kasus = '$kode_kasus'";
    $check_result = mysqli_query($conn, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        $errors[] = "Kode kasus sudah digunakan!";
    }

    if (empty($kode_kasus) || empty($id_penyakit)) {
        $errors[] = "Kode kasus dan penyakit wajib diisi!";
    }

    if (empty($errors)) {
        $query = "INSERT INTO kasus (kode_kasus, id_penyakit, deskripsi_kasus, status_validasi) 
                  VALUES ('$kode_kasus', '$id_penyakit', '$deskripsi_kasus', 'pending')";
        
        if (mysqli_query($conn, $query)) {
            $id_kasus = mysqli_insert_id($conn);
            // Pindahkan script ke bagian bawah setelah load SweetAlert
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'id_kasus' => $id_kasus
            ];
            header("Location: create.php");
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
    <title>Tambah Kasus - Sistem Pakar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
</head>
<body class="bg-gray-50">
    <?php renderAdminSidebar('kasus'); ?>
    
    <!-- Main Content -->
    <div class="p-4 sm:ml-64">
        <div class="p-4">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Tambah Kasus</h2>
                    <a href="index.php" class="text-blue-500 hover:text-blue-600">
                        Kembali ke Daftar Kasus
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
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">
                            Kode Kasus <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="kode_kasus" required
                               value="<?= isset($_POST['kode_kasus']) ? htmlspecialchars($_POST['kode_kasus']) : '' ?>"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                               placeholder="Contoh: K001">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">
                            Penyakit <span class="text-red-500">*</span>
                        </label>
                        <select name="id_penyakit" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            <option value="">Pilih Penyakit</option>
                            <?php while ($penyakit = mysqli_fetch_assoc($result_penyakit)): ?>
                                <option value="<?= $penyakit['id_penyakit'] ?>"
                                        <?= (isset($_POST['id_penyakit']) && $_POST['id_penyakit'] == $penyakit['id_penyakit']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($penyakit['kode_penyakit'] . ' - ' . $penyakit['nama_penyakit']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">
                            Deskripsi Kasus
                        </label>
                        <textarea name="deskripsi_kasus" rows="4"
                                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                  placeholder="Masukkan deskripsi kasus (opsional)"><?= isset($_POST['deskripsi_kasus']) ? htmlspecialchars($_POST['deskripsi_kasus']) : '' ?></textarea>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="index.php" 
                           class="text-gray-500 bg-gray-200 hover:bg-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            Batal
                        </a>
                        <button type="submit"
                                class="text-white bg-blue-500 hover:bg-blue-600 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Kasus berhasil ditambahkan. Silakan tambahkan fitur kasus.',
            showConfirmButton: true,
            confirmButtonText: 'Tambah Fitur',
            showCancelButton: true,
            cancelButtonText: 'Kembali ke Daftar Kasus'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'fitur.php?id=<?= $_SESSION['flash_message']['id_kasus'] ?>';
            } else {
                window.location.href = 'index.php';
            }
        });
    </script>
    <?php 
    unset($_SESSION['flash_message']);
    endif; 
    ?>
</body>
</html>