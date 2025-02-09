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

// Ambil data kasus untuk dropdown
$query_kasus = "SELECT * FROM kasus ORDER BY kode_kasus ASC";
$result_kasus = mysqli_query($conn, $query_kasus);

// Ambil data gejala untuk dropdown
$query_gejala = "SELECT * FROM gejala ORDER BY nama_gejala ASC";
$result_gejala = mysqli_query($conn, $query_gejala);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_kasus = mysqli_real_escape_string($conn, $_POST['id_kasus']);
    $id_gejala = mysqli_real_escape_string($conn, $_POST['id_gejala']);
    $nilai_fitur = mysqli_real_escape_string($conn, $_POST['nilai_fitur']);
    $bobot_fitur = mysqli_real_escape_string($conn, $_POST['bobot_fitur']);
    
    // Validasi input
    $errors = [];
    
    if (empty($id_kasus) || empty($id_gejala) || empty($nilai_fitur) || empty($bobot_fitur)) {
        $errors[] = "Semua field wajib diisi!";
    }

    // Validasi nilai dan bobot (harus berupa angka antara 0.1 dan 1)
    if (!is_numeric($nilai_fitur) || $nilai_fitur < 0.1 || $nilai_fitur > 1) {
        $errors[] = "Nilai fitur harus berupa angka antara 0.1 dan 1!";
    }

    if (!is_numeric($bobot_fitur) || $bobot_fitur < 0.1 || $bobot_fitur > 1) {
        $errors[] = "Bobot fitur harus berupa angka antara 0.1 dan 1!";
    }

    if (empty($errors)) {
        // Konversi ke format yang sesuai dengan tipe data di database
        $nilai_fitur = number_format((float)$nilai_fitur, 2, '.', '');
        $bobot_fitur = number_format((float)$bobot_fitur, 2, '.', '');

        $query = "INSERT INTO fitur_kasus (id_kasus, id_gejala, nilai_fitur, bobot_fitur, created_at, updated_at) 
                  VALUES (?, ?, ?, ?, NOW(), NOW())";
                  
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "iidd", $id_kasus, $id_gejala, $nilai_fitur, $bobot_fitur);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => 'Fitur kasus berhasil ditambahkan.'
            ];
            header("Location: index.php");
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
    <title>Tambah Fitur Kasus - Sistem Pakar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
</head>
<body class="bg-gray-50">
    <?php renderAdminSidebar('fitur'); ?>
    
    <!-- Main Content -->
    <div class="p-4 sm:ml-64">
        <div class="p-4">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Tambah Fitur Kasus</h2>
                    <a href="index.php" class="text-blue-500 hover:text-blue-600">
                        Kembali ke Daftar Fitur
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
                            Kasus <span class="text-red-500">*</span>
                        </label>
                        <select name="id_kasus" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            <option value="">Pilih Kasus</option>
                            <?php while ($kasus = mysqli_fetch_assoc($result_kasus)): ?>
                                <option value="<?= $kasus['id_kasus'] ?>"
                                        <?= (isset($_POST['id_kasus']) && $_POST['id_kasus'] == $kasus['id_kasus']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kasus['kode_kasus'] . ' - ' . $kasus['deskripsi_kasus']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">
                            Gejala <span class="text-red-500">*</span>
                        </label>
                        <select name="id_gejala" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            <option value="">Pilih Gejala</option>
                            <?php while ($gejala = mysqli_fetch_assoc($result_gejala)): ?>
                                <option value="<?= $gejala['id_gejala'] ?>"
                                        <?= (isset($_POST['id_gejala']) && $_POST['id_gejala'] == $gejala['id_gejala']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($gejala['nama_gejala']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">
                            Nilai Fitur <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="nilai_fitur" required step="0.1" min="0.1" max="1"
                               value="<?= isset($_POST['nilai_fitur']) ? htmlspecialchars($_POST['nilai_fitur']) : '' ?>"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                               placeholder="Masukkan nilai fitur (0.1-1)">
                        <p class="mt-1 text-sm text-gray-500">Nilai antara 0.1 dan 1, dimana 1 berarti sangat mirip</p>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">
                            Bobot Fitur <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="bobot_fitur" required step="0.1" min="0.1" max="1"
                               value="<?= isset($_POST['bobot_fitur']) ? htmlspecialchars($_POST['bobot_fitur']) : '' ?>"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                               placeholder="Masukkan bobot fitur (0.1-1)">
                        <p class="mt-1 text-sm text-gray-500">Nilai antara 0.1 dan 1, dimana 1 berarti sangat penting</p>
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
            icon: '<?= $_SESSION['flash_message']['type'] ?>',
            title: '<?= $_SESSION['flash_message']['type'] == 'success' ? 'Berhasil!' : 'Error!' ?>',
            text: '<?= $_SESSION['flash_message']['message'] ?>',
            showConfirmButton: false,
            timer: 1500
        });
    </script>
    <?php 
    unset($_SESSION['flash_message']);
    endif; 
    ?>
</body>
</html>