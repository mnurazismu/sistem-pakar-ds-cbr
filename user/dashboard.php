<?php
require_once '../config/database.php';
require_once '../auth/functions.php';
require_once 'components/sidebar.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    header("Location: ../auth/login.php");
    exit;
} elseif (!isUser()) {
    header("Location: ../admin/dashboard.php");
    exit;
}

// Ambil data user
$user_id = $_SESSION['id_user'];
$query_user = "SELECT * FROM users WHERE id_user = ?";
$stmt = $conn->prepare($query_user);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

// Statistik Diagnosis
$query_stats = "SELECT 
    COUNT(*) as total_diagnosis,
    COUNT(CASE WHEN status_validasi = 'valid' THEN 1 END) as valid_diagnosis,
    COUNT(CASE WHEN status_validasi = 'invalid' THEN 1 END) as invalid_diagnosis,
    COUNT(CASE WHEN status_validasi = 'pending' THEN 1 END) as pending_diagnosis,
    MAX(tanggal_diagnosis) as last_diagnosis_date
    FROM hasil_diagnosis 
    WHERE id_user = ?";
$stmt = $conn->prepare($query_stats);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Ambil 5 diagnosis terakhir
$query_recent = "SELECT hd.*, 
                 p1.nama_penyakit as ds_nama_penyakit,
                 p2.nama_penyakit as cbr_nama_penyakit
                 FROM hasil_diagnosis hd 
                 LEFT JOIN penyakit p1 ON hd.ds_penyakit_id = p1.id_penyakit
                 LEFT JOIN kasus k ON hd.cbr_kasus_id = k.id_kasus
                 LEFT JOIN penyakit p2 ON k.id_penyakit = p2.id_penyakit
                 WHERE hd.id_user = ? 
                 ORDER BY hd.tanggal_diagnosis DESC 
                 LIMIT 5";
$stmt = $conn->prepare($query_recent);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_diagnoses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Ambil penyakit yang paling sering didiagnosis
$query_common = "SELECT 
                 p.nama_penyakit,
                 COUNT(*) as diagnosis_count
                 FROM hasil_diagnosis hd
                 JOIN penyakit p ON hd.ds_penyakit_id = p.id_penyakit
                 WHERE hd.id_user = ? AND hd.status_validasi = 'valid'
                 GROUP BY p.id_penyakit
                 ORDER BY diagnosis_count DESC
                 LIMIT 3";
$stmt = $conn->prepare($query_common);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$common_diseases = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - Sistem Pakar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50">
    <?php renderUserSidebar('dashboard'); ?>

    <div class="p-4 sm:ml-64">
        <div class="p-4 border-2 border-gray-200 rounded-lg">
            <!-- Welcome Section -->
            <div class="bg-white p-6 rounded-lg shadow mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold">Selamat Datang, <?= htmlspecialchars($user_data['nama_lengkap']) ?>!</h1>
                        <p class="text-gray-600">
                            <?php if ($stats['last_diagnosis_date']): ?>
                                Diagnosis terakhir: <?= date('d F Y H:i', strtotime($stats['last_diagnosis_date'])) ?>
                            <?php else: ?>
                                Belum ada diagnosis yang dilakukan
                            <?php endif; ?>
                        </p>
                    </div>
                    <div>
                        <a href="diagnosis/index.php" 
                           class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition duration-300">
                            Mulai Diagnosis Baru
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Statistik Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-600">Total Diagnosis</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $stats['total_diagnosis'] ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-600">Diagnosis Valid</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $stats['valid_diagnosis'] ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-600">Diagnosis Invalid</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $stats['invalid_diagnosis'] ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-600">Menunggu Validasi</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $stats['pending_diagnosis'] ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Diagnoses & Common Diseases -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Recent Diagnoses -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-semibold mb-4">Diagnosis Terakhir</h2>
                    <?php if ($recent_diagnoses): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Hasil</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($recent_diagnoses as $diagnosis): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-2 text-sm">
                                                <?= date('d/m/Y', strtotime($diagnosis['tanggal_diagnosis'])) ?>
                                            </td>
                                            <td class="px-4 py-2 text-sm">
                                                DS: <?= htmlspecialchars($diagnosis['ds_nama_penyakit']) ?><br>
                                                CBR: <?= htmlspecialchars($diagnosis['cbr_nama_penyakit']) ?>
                                            </td>
                                            <td class="px-4 py-2">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                                    <?php
                                                    switch($diagnosis['status_validasi']) {
                                                        case 'valid':
                                                            echo 'bg-green-100 text-green-800';
                                                            break;
                                                        case 'invalid':
                                                            echo 'bg-red-100 text-red-800';
                                                            break;
                                                        default:
                                                            echo 'bg-yellow-100 text-yellow-800';
                                                    }
                                                    ?>">
                                                    <?= ucfirst($diagnosis['status_validasi']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 text-right">
                            <a href="riwayat/index.php" class="text-blue-500 hover:underline">
                                Lihat Semua Riwayat →
                            </a>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-4">Belum ada diagnosis yang dilakukan</p>
                    <?php endif; ?>
                </div>

                <!-- Common Diseases -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-semibold mb-4">Penyakit yang Sering Didiagnosis</h2>
                    <?php if ($common_diseases): ?>
                        <div class="space-y-4">
                            <?php foreach ($common_diseases as $disease): ?>
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <span class="font-medium"><?= htmlspecialchars($disease['nama_penyakit']) ?></span>
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                        <?= $disease['diagnosis_count'] ?> kali
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-4">Belum ada diagnosis yang tervalidasi</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Informasi Sistem -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-semibold mb-4">Informasi Sistem</h2>
                <div class="prose max-w-none">
                    <p class="text-gray-600">
                        Sistem Pakar Diagnosis Penyakit Tanaman Cabai ini menggunakan dua metode diagnosis:
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-blue-800 mb-2">Dempster-Shafer (DS)</h3>
                            <p class="text-sm text-gray-600">
                                Metode ini menghitung tingkat kepercayaan diagnosis berdasarkan kombinasi 
                                gejala-gejala yang dipilih. Semakin tinggi nilai DS, semakin kuat indikasi 
                                penyakit tersebut.
                            </p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="font-semibold text-green-800 mb-2">Case-Based Reasoning (CBR)</h3>
                            <p class="text-sm text-gray-600">
                                Metode ini membandingkan kasus baru dengan kasus-kasus sebelumnya yang sudah 
                                tervalidasi. Semakin tinggi similarity, semakin mirip dengan kasus yang ada.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../src/jquery-3.6.3.min.js"></script>
    <script src="../src/flowbite.min.js"></script>
</body>
</html>