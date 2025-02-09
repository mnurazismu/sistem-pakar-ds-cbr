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

$id_gejala = $_GET['id'];
$query = "SELECT * FROM gejala WHERE id_gejala = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id_gejala);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$gejala = mysqli_fetch_assoc($result);

if (!$gejala) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['update'])) {
    $kode_gejala = trim($_POST['kode_gejala']);
    $nama_gejala = trim($_POST['nama_gejala']);
    $keterangan = trim($_POST['keterangan']);
    $belief_value = trim($_POST['belief_value']);
    
    $errors = [];
    
    // Validasi input
    if (empty($kode_gejala)) {
        $errors[] = "Kode gejala harus diisi!";
    }
    
    if (empty($nama_gejala)) {
        $errors[] = "Nama gejala harus diisi!";
    }

    if (!is_numeric($belief_value) || $belief_value < 0 || $belief_value > 1) {
        $errors[] = "Nilai kepercayaan harus berupa angka antara 0 dan 1!";
    }
    
    // Cek duplikasi kode gejala kecuali untuk gejala yang sedang diedit
    $query_check = "SELECT id_gejala FROM gejala WHERE kode_gejala = ? AND id_gejala != ?";
    $stmt_check = mysqli_prepare($conn, $query_check);
    mysqli_stmt_bind_param($stmt_check, "si", $kode_gejala, $id_gejala);
    mysqli_stmt_execute($stmt_check);
    if (mysqli_stmt_fetch($stmt_check)) {
        $errors[] = "Kode gejala sudah digunakan!";
    }
    
    if (empty($errors)) {
        $query = "UPDATE gejala SET kode_gejala = ?, nama_gejala = ?, keterangan = ?, belief_value = ? WHERE id_gejala = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssdi", $kode_gejala, $nama_gejala, $keterangan, $belief_value, $id_gejala);
        
        if (mysqli_stmt_execute($stmt)) {
            header("Location: index.php?status=success&message=Gejala berhasil diupdate");
            exit;
        } else {
            $errors[] = "Gagal mengupdate data gejala!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Gejala - Sistem Pakar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php renderAdminSidebar('gejala'); ?>
    
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
                                Gejala
                            </a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-300 mx-2"></i>
                            <span class="text-gray-500">Edit Gejala</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white rounded-lg shadow-md p-6 md:p-8 mb-8">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-800">Edit Gejala</h2>
                        <p class="text-sm text-gray-600 mt-1">Edit informasi gejala dengan kode <?= htmlspecialchars($gejala['kode_gejala']) ?></p>
                    </div>
                    <a href="index.php" class="text-gray-600 hover:text-gray-900">
                        <i class="fas fa-times"></i>
                    </a>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-500"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Terdapat beberapa kesalahan:</h3>
                                <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= $error ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="kode_gejala" class="block text-sm font-medium text-gray-700 mb-2">
                                Kode Gejala <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="kode_gejala" name="kode_gejala" 
                                   value="<?= htmlspecialchars($gejala['kode_gejala']) ?>"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   readonly>
                            <p class="mt-1 text-sm text-gray-500">Kode gejala tidak dapat diubah</p>
                        </div>

                        <div>
                            <label for="belief_value" class="block text-sm font-medium text-gray-700 mb-2">
                                Nilai Kepercayaan (0-1) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="belief_value" name="belief_value" 
                                   value="<?= htmlspecialchars($gejala['belief_value']) ?>"
                                   step="0.1" min="0" max="1"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-sm text-gray-500">Masukkan nilai antara 0 dan 1</p>
                        </div>
                    </div>

                    <div>
                        <label for="nama_gejala" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Gejala <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nama_gejala" name="nama_gejala"
                               value="<?= htmlspecialchars($gejala['nama_gejala']) ?>"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">
                            Keterangan
                        </label>
                        <textarea id="keterangan" name="keterangan" rows="4"
                                  class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Tambahkan keterangan atau penjelasan tambahan tentang gejala ini (opsional)"><?= htmlspecialchars($gejala['keterangan']) ?></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="index.php" 
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Batal
                        </a>
                        <button type="submit" name="update"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Update Gejala
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= $_GET['message'] ?>',
            showConfirmButton: false,
            timer: 1500
        });
    </script>
    <?php endif; ?>
</body>
</html>