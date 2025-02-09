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

// Cek id diagnosis
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_diagnosis = mysqli_real_escape_string($conn, $_GET['id']);

// Ambil data diagnosis dengan detail lebih lengkap
$query = "SELECT hd.*, 
          u.nama_lengkap as nama_user, 
          p1.kode_penyakit as ds_kode_penyakit, 
          p1.nama_penyakit as ds_nama_penyakit,
          k.kode_kasus,
          k.deskripsi_kasus,
          p2.kode_penyakit as cbr_kode_penyakit,
          p2.nama_penyakit as cbr_nama_penyakit
          FROM hasil_diagnosis hd
          JOIN users u ON hd.id_user = u.id_user
          JOIN penyakit p1 ON hd.ds_penyakit_id = p1.id_penyakit
          JOIN kasus k ON hd.cbr_kasus_id = k.id_kasus
          JOIN penyakit p2 ON k.id_penyakit = p2.id_penyakit
          WHERE hd.id_diagnosis = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id_diagnosis);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit;
}

$diagnosis = mysqli_fetch_assoc($result);

// Format status badge
$status_badge = match($diagnosis['status_validasi']) {
    'valid' => '<span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Valid</span>',
    'invalid' => '<span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">Invalid</span>',
    default => '<span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded">Pending</span>'
};
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Hasil Diagnosis - Sistem Pakar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php renderAdminSidebar('hasil_diagnosis'); ?>
    
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
                                Hasil Diagnosis
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-300 mx-2"></i>
                            <span class="text-gray-500">Detail</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-800">Detail Hasil Diagnosis</h2>
                        <p class="text-sm text-gray-600 mt-1">Detail hasil diagnosis user <?= htmlspecialchars($diagnosis['nama_user']) ?></p>
                    </div>
                    <div class="flex items-center gap-3">
                        <?= $status_badge ?>
                        <button onclick="showValidasiModal(<?= $diagnosis['id_diagnosis'] ?>, '<?= $diagnosis['status_validasi'] ?>')" 
                                class="text-blue-600 hover:text-blue-700">
                            <i class="fas fa-edit mr-1"></i> Update Status
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Informasi Umum -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-semibold mb-4">Informasi Umum</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-600">Tanggal Diagnosis</p>
                                <p class="font-medium"><?= date('d/m/Y H:i', strtotime($diagnosis['tanggal_diagnosis'])) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Nama User</p>
                                <p class="font-medium"><?= htmlspecialchars($diagnosis['nama_user']) ?></p>
                            </div>
                        </div>
                    </div>
                     <!-- Feedback & Validasi -->
                     <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-semibold mb-4">Feedback & Validasi</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-600">Feedback User</p>
                                <p class="font-medium"><?= $diagnosis['feedback_user'] ? htmlspecialchars($diagnosis['feedback_user']) : '-' ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Keterangan Admin</p>
                                <p class="font-medium"><?= $diagnosis['keterangan_admin'] ? htmlspecialchars($diagnosis['keterangan_admin']) : '-' ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Hasil DS -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-semibold mb-4">Hasil Dempster-Shafer</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-600">Penyakit Terdeteksi</p>
                                <p class="font-medium">
                                    <?= htmlspecialchars($diagnosis['ds_kode_penyakit']) ?> - 
                                    <?= htmlspecialchars($diagnosis['ds_nama_penyakit']) ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Nilai Kepercayaan</p>
                                <p class="font-medium"><?= number_format($diagnosis['ds_nilai_kepercayaan'] * 100, 2) ?>%</p>
                            </div>
                        </div>
                    </div>

                    <!-- Hasil CBR -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-semibold mb-4">Hasil Case-Based Reasoning</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-600">Kasus Terdekat</p>
                                <p class="font-medium"><?= htmlspecialchars($diagnosis['kode_kasus']) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Penyakit</p>
                                <p class="font-medium">
                                    <?= htmlspecialchars($diagnosis['cbr_kode_penyakit']) ?> - 
                                    <?= htmlspecialchars($diagnosis['cbr_nama_penyakit']) ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Nilai Similarity</p>
                                <p class="font-medium"><?= number_format($diagnosis['cbr_similarity'] * 100, 2) ?>%</p>
                            </div>
                        </div>
                    </div>

                   
                </div>

                <!-- Detail Perhitungan -->
                <div class="mt-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold">Detail Perhitungan DS</h3>
                        <pre class="bg-gray-50 p-4 rounded-lg overflow-x-auto mt-2 text-sm">
<?= json_encode(json_decode($diagnosis['ds_detail_perhitungan']), JSON_PRETTY_PRINT) ?>
                        </pre>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Detail Perhitungan CBR</h3>
                        <pre class="bg-gray-50 p-4 rounded-lg overflow-x-auto mt-2 text-sm">
<?= json_encode(json_decode($diagnosis['cbr_detail_perhitungan']), JSON_PRETTY_PRINT) ?>
                        </pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

     <!-- Modal Validasi -->
     <div id="validasiModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Validasi Hasil Diagnosis
                    </h3>
                    <button type="button" onclick="closeValidasiModal()" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <form id="validasiForm" action="validasi.php" method="POST" class="p-4 md:p-5">
                    <input type="hidden" name="id_diagnosis" id="validasi_id_diagnosis">
                    <div class="grid gap-4 mb-4">
                        <div>
                            <label for="status_validasi" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Status Validasi</label>
                            <select id="status_validasi" name="status_validasi" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="pending">Pending</option>
                                <option value="valid">Valid</option>
                                <option value="invalid">Invalid</option>
                            </select>
                        </div>
                        <div>
                            <label for="keterangan_admin" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Keterangan</label>
                            <textarea id="keterangan_admin" name="keterangan_admin" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500" placeholder="Tambahkan keterangan validasi..."></textarea>
                        </div>
                    </div>
                    <div class="flex items-center justify-end space-x-3">
                        <button type="button" onclick="closeValidasiModal()" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">
                            Batal
                        </button>
                        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function showValidasiModal(id_diagnosis, current_status) {
            document.getElementById('validasi_id_diagnosis').value = id_diagnosis;
            document.getElementById('status_validasi').value = current_status;
            document.getElementById('validasiModal').classList.remove('hidden');
            document.getElementById('validasiModal').classList.add('flex');
        }

        function closeValidasiModal() {
            document.getElementById('validasiModal').classList.add('hidden');
            document.getElementById('validasiModal').classList.remove('flex');
        }
    </script>
</body>
</html>