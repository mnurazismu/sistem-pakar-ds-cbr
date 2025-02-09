<?php
require_once '../config/database.php';
require_once '../auth/functions.php';
require_once 'components/sidebar.php';

// Cek login dan role
if (!isLoggedIn()) {
    header("Location: ../auth/login.php");
    exit;
} elseif (!isAdmin()) {
    header("Location: ../user/dashboard.php");
    exit;
}

// Ambil data untuk dashboard
$nama_lengkap = $_SESSION['nama_lengkap'];

// Hitung total data
$query_total_users = "SELECT COUNT(*) as total FROM users WHERE tipe_user = 'User'";
$result_users = mysqli_query($conn, $query_total_users);
$total_users = mysqli_fetch_assoc($result_users)['total'];

// Statistik Diagnosis (menggunakan query seperti di user)
$query_stats = "SELECT 
    COUNT(*) as total_diagnosis,
    COUNT(CASE WHEN status_validasi = 'valid' THEN 1 END) as valid_diagnosis,
    COUNT(CASE WHEN status_validasi = 'invalid' THEN 1 END) as invalid_diagnosis,
    COUNT(CASE WHEN status_validasi = 'pending' THEN 1 END) as pending_diagnosis
    FROM hasil_diagnosis";
$result_stats = mysqli_query($conn, $query_stats);
$stats = mysqli_fetch_assoc($result_stats);

$query_total_rules = "SELECT COUNT(*) as total FROM rule_ds";
$result_rules = mysqli_query($conn, $query_total_rules);
$total_rules = mysqli_fetch_assoc($result_rules)['total'];

// Ambil 5 diagnosis terbaru (menggunakan query seperti di user)
$query_recent = "SELECT hd.*, u.nama_lengkap,
                 p1.nama_penyakit as ds_nama_penyakit,
                 p2.nama_penyakit as cbr_nama_penyakit
                 FROM hasil_diagnosis hd 
                 JOIN users u ON hd.id_user = u.id_user
                 LEFT JOIN penyakit p1 ON hd.ds_penyakit_id = p1.id_penyakit
                 LEFT JOIN kasus k ON hd.cbr_kasus_id = k.id_kasus
                 LEFT JOIN penyakit p2 ON k.id_penyakit = p2.id_penyakit
                 ORDER BY hd.tanggal_diagnosis DESC 
                 LIMIT 5";
$result_recent = mysqli_query($conn, $query_recent);

// Ambil statistik penyakit terbanyak (menggunakan query seperti di user)
$query_top_diseases = "SELECT 
                     p.nama_penyakit,
                     COUNT(*) as total
                     FROM hasil_diagnosis hd
                     JOIN penyakit p ON hd.ds_penyakit_id = p.id_penyakit
                     WHERE hd.status_validasi = 'valid'
                     GROUP BY p.id_penyakit
                     ORDER BY total DESC
                     LIMIT 5";
$result_top_diseases = mysqli_query($conn, $query_top_diseases);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Pakar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php renderAdminSidebar('dashboard'); ?>
    
    <!-- Main Content -->
    <div class="p-4 sm:ml-64">
        <div class="p-4">
            <!-- Welcome Section -->
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Selamat Datang, <?= htmlspecialchars($nama_lengkap) ?>!</h1>
                <p class="text-gray-600">Panel administrasi sistem pakar diagnosis penyakit</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Total Users Card -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-600 text-sm">Total Users</h2>
                            <p class="text-2xl font-semibold text-gray-800"><?= $total_users ?></p>
                            <p class="text-xs text-gray-500">Pengguna terdaftar</p>
                        </div>
                    </div>
                </div>

                <!-- Total Diagnosis Card -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-500">
                            <i class="fas fa-stethoscope text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-600 text-sm">Total Diagnosis</h2>
                            <p class="text-2xl font-semibold text-gray-800"><?= $stats['total_diagnosis'] ?></p>
                            <p class="text-xs text-gray-500">Diagnosis dilakukan</p>
                        </div>
                    </div>
                </div>

                <!-- Pending Validation Card -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                            <i class="fas fa-clock text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-600 text-sm">Menunggu Validasi</h2>
                            <p class="text-2xl font-semibold text-gray-800"><?= $stats['pending_diagnosis'] ?></p>
                            <p class="text-xs text-gray-500">Perlu ditinjau</p>
                        </div>
                    </div>
                </div>

                <!-- Total Rules Card -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-500">
                            <i class="fas fa-brain text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-gray-600 text-sm">Total Rules</h2>
                            <p class="text-2xl font-semibold text-gray-800"><?= $total_rules ?></p>
                            <p class="text-xs text-gray-500">Basis pengetahuan</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Activity -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Diagnosis Terbaru</h3>
                        <a href="hasil_diagnosis/index.php" class="text-blue-600 hover:text-blue-800 text-sm">Lihat Semua</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Penyakit</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php while ($row = mysqli_fetch_assoc($result_recent)): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($row['nama_lengkap']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            DS: <?= htmlspecialchars($row['ds_nama_penyakit']) ?><br>
                                            CBR: <?= htmlspecialchars($row['cbr_nama_penyakit']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= date('d/m/Y H:i', strtotime($row['tanggal_diagnosis'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                            $status_class = [
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'valid' => 'bg-green-100 text-green-800',
                                                'invalid' => 'bg-red-100 text-red-800'
                                            ][$row['status_validasi']];
                                            ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_class ?>">
                                                <?= ucfirst($row['status_validasi']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Diseases -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Penyakit Terbanyak Didiagnosis</h3>
                    <div class="space-y-4">
                        <?php while ($row = mysqli_fetch_assoc($result_top_diseases)): ?>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600"><?= htmlspecialchars($row['nama_penyakit']) ?></span>
                                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?= $row['total'] ?> kasus
                                </span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>
</html>