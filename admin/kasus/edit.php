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

// Cek id kasus
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_kasus = mysqli_real_escape_string($conn, $_GET['id']);

// Ambil data kasus
$query_kasus = "SELECT * FROM kasus WHERE id_kasus = '$id_kasus'";
$result_kasus = mysqli_query($conn, $query_kasus);

if (mysqli_num_rows($result_kasus) == 0) {
    header("Location: index.php");
    exit;
}

$kasus = mysqli_fetch_assoc($result_kasus);

// Ambil data penyakit untuk dropdown
$query_penyakit = "SELECT * FROM penyakit ORDER BY kode_penyakit ASC";
$result_penyakit = mysqli_query($conn, $query_penyakit);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_kasus = mysqli_real_escape_string($conn, $_POST['kode_kasus']);
    $id_penyakit = mysqli_real_escape_string($conn, $_POST['id_penyakit']);
    $deskripsi_kasus = mysqli_real_escape_string($conn, $_POST['deskripsi_kasus']);

    // Validasi input
    $errors = [];

    // Cek kode kasus unik (kecuali untuk kasus yang sedang diedit)
    $check_query = "SELECT kode_kasus FROM kasus WHERE kode_kasus = '$kode_kasus' AND id_kasus != '$id_kasus'";
    $check_result = mysqli_query($conn, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        $errors[] = "Kode kasus sudah digunakan!";
    }

    if (empty($kode_kasus) || empty($id_penyakit)) {
        $errors[] = "Kode kasus dan penyakit wajib diisi!";
    }

    if (empty($errors)) {
        $query = "UPDATE kasus SET 
                  kode_kasus = '$kode_kasus',
                  id_penyakit = '$id_penyakit',
                  deskripsi_kasus = '$deskripsi_kasus'
                  WHERE id_kasus = '$id_kasus'";

        if (mysqli_query($conn, $query)) {
            $_SESSION['flash_message'] = true;
            header("Location: edit.php?id=" . $id_kasus);
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
    <title>Edit Kasus - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-50">
    <?php renderAdminSidebar('kasus'); ?>

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
                                Kasus
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
                        <h2 class="text-2xl font-semibold text-gray-800">Edit Kasus</h2>
                        <p class="text-gray-600 mt-1">Edit informasi kasus dengan kode
                            <?= htmlspecialchars($kasus['kode_kasus']) ?>
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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Informasi Dasar Section -->
                        <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                            <h3 class="font-medium text-gray-900 mb-2">
                                <i class="fas fa-info-circle text-blue-500 mr-2"></i>Informasi Dasar
                            </h3>

                            <!-- Kode Kasus -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Kode Kasus <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-code text-gray-400"></i>
                                    </div>
                                    <input type="text" name="kode_kasus" required
                                        value="<?= htmlspecialchars($kasus['kode_kasus']) ?>"
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
                                        placeholder="Contoh: K001">
                                </div>
                            </div>
                        </div>

                        <!-- Penyakit Section -->
                        <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                            <h3 class="font-medium text-gray-900 mb-2">
                                <i class="fas fa-virus text-red-500 mr-2"></i>Pilih Penyakit
                            </h3>

                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Penyakit <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-disease text-gray-400"></i>
                                    </div>
                                    <select name="id_penyakit" required
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5">
                                        <option value="">Pilih Penyakit</option>
                                        <?php while ($penyakit = mysqli_fetch_assoc($result_penyakit)): ?>
                                        <option value="<?= $penyakit['id_penyakit'] ?>"
                                            <?= $kasus['id_penyakit'] == $penyakit['id_penyakit'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($penyakit['kode_penyakit'] . ' - ' . $penyakit['nama_penyakit']) ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Deskripsi Section -->
                        <div class="md:col-span-2 bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-medium text-gray-900 mb-4">
                                <i class="fas fa-align-left text-purple-500 mr-2"></i>Deskripsi Kasus
                            </h3>
                            <textarea name="deskripsi_kasus" rows="4"
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                placeholder="Masukkan deskripsi atau penjelasan tentang kasus..."><?= htmlspecialchars($kasus['deskripsi_kasus']) ?></textarea>
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
                            Update Kasus
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
        text: 'Kasus berhasil diperbarui.',
        showConfirmButton: false,
        timer: 1500
    }).then(function() {
        window.location.href = 'index.php';
    });
    </script>
    <?php
        unset($_SESSION['flash_message']);
    endif;
    ?>
</body>

</html>