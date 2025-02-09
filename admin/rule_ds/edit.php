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

// Ambil data rule yang akan diedit
$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : 0;
$query = "SELECT r.*, g.kode_gejala, g.nama_gejala, g.belief_value,
                 p.kode_penyakit, p.nama_penyakit, p.tingkat_keparahan
          FROM rule_ds r 
          JOIN gejala g ON r.id_gejala = g.id_gejala 
          JOIN penyakit p ON r.id_penyakit = p.id_penyakit 
          WHERE r.id_rule = '$id'";
$result = mysqli_query($conn, $query);
$rule = mysqli_fetch_assoc($result);

if (!$rule) {
    header("Location: index.php");
    exit;
}

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nilai_densitas = mysqli_real_escape_string($conn, $_POST['nilai_densitas']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    
    // Validasi input
    $errors = [];
    
    if ($nilai_densitas < 0 || $nilai_densitas > 1) {
        $errors[] = "Nilai densitas harus antara 0 dan 1";
    }
    
    if (empty($errors)) {
        $query = "UPDATE rule_ds SET 
                    nilai_densitas = '$nilai_densitas',
                    keterangan = '$keterangan'
                  WHERE id_rule = '$id'";
        
        if (mysqli_query($conn, $query)) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Rule berhasil diperbarui.',
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
    <title>Edit Rule DS - Sistem Pakar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
</head>
<body class="bg-gray-50">
    <?php renderAdminSidebar('rule_ds'); ?>
    
    <!-- Main Content -->
    <div class="p-4 sm:ml-64">
        <div class="p-4">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Edit Rule Dempster-Shafer</h2>
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
                    <!-- Gejala (readonly) -->
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">
                            Gejala
                        </label>
                        <input type="text" 
                               value="<?= htmlspecialchars($rule['kode_gejala'] . ' - ' . $rule['nama_gejala']) ?>"
                               class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5"
                               readonly>
                        <p class="mt-1 text-sm text-gray-500">
                            Belief value: <?= number_format($rule['belief_value'], 2) ?>
                        </p>
                    </div>

                    <!-- Penyakit (readonly) -->
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">
                            Penyakit
                        </label>
                        <input type="text" 
                               value="<?= htmlspecialchars($rule['kode_penyakit'] . ' - ' . $rule['nama_penyakit']) ?>"
                               class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5"
                               readonly>
                        <p class="mt-1 text-sm text-gray-500">
                            Tingkat keparahan: <?= $rule['tingkat_keparahan'] ?>
                        </p>
                    </div>

                    <!-- Nilai Densitas -->
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">
                            Nilai Densitas <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="nilai_densitas" required step="0.01" min="0" max="1"
                               value="<?= htmlspecialchars($rule['nilai_densitas']) ?>"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <p class="mt-1 text-sm text-gray-500">Nilai antara 0 dan 1</p>
                    </div>

                    <!-- Keterangan -->
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">
                            Keterangan
                        </label>
                        <textarea name="keterangan" rows="3"
                                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                  placeholder="Keterangan tambahan (opsional)"><?= htmlspecialchars($rule['keterangan']) ?></textarea>
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