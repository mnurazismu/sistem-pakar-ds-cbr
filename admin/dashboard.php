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

// Statistik Diagnosis
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
    <title>Dashboard - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-50">
    <?php renderAdminSidebar('dashboard'); ?>

    <!-- Main Content -->
    <div class="p-4 sm:ml-64">
        <div class="p-4">
            <!-- Welcome Section with Gradient Background -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 md:p-8 mb-6 text-white">
                <h1 class="text-3xl font-bold mb-2">Selamat Datang, <?= htmlspecialchars($nama_lengkap) ?>!</h1>
                <p class="text-blue-100">Panel administrasi sistem pakar diagnosis penyakit</p>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Users Card -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Users</p>
                            <div class="flex items-center">
                                <h3 class="text-2xl font-bold text-gray-900"><?= $total_users ?></h3>
                                <span class="text-xs text-gray-500 ml-2">pengguna</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Diagnosis Card -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-stethoscope text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Diagnosis</p>
                            <div class="flex items-center">
                                <h3 class="text-2xl font-bold text-gray-900"><?= $stats['total_diagnosis'] ?></h3>
                                <span class="text-xs text-gray-500 ml-2">diagnosis</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Validation Card -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <i class="fas fa-clock text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Menunggu Validasi</p>
                            <div class="flex items-center">
                                <h3 class="text-2xl font-bold text-gray-900"><?= $stats['pending_diagnosis'] ?></h3>
                                <span class="text-xs text-gray-500 ml-2">pending</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Rules Card -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <i class="fas fa-brain text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Rules</p>
                            <div class="flex items-center">
                                <h3 class="text-2xl font-bold text-gray-900"><?= $total_rules ?></h3>
                                <span class="text-xs text-gray-500 ml-2">aturan</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Activity -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-800">
                                <i class="fas fa-history text-blue-500 mr-2"></i>Diagnosis Terbaru
                            </h3>
                            <a href="hasil_diagnosis/index.php"
                                class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                Lihat Semua
                                <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        User</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Hasil</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while ($row = mysqli_fetch_assoc($result_recent)): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div
                                                    class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <span class="text-blue-600 font-semibold text-sm">
                                                        <?= strtoupper(substr($row['nama_lengkap'], 0, 1)) ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">
                                                    <?= htmlspecialchars($row['nama_lengkap']) ?></p>
                                                <p class="text-xs text-gray-500">
                                                    <?= date('d/m/Y H:i', strtotime($row['tanggal_diagnosis'])) ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm">
                                            <p class="text-gray-900">DS:
                                                <?= htmlspecialchars($row['ds_nama_penyakit']) ?></p>
                                            <p class="text-gray-500">CBR:
                                                <?= htmlspecialchars($row['cbr_nama_penyakit']) ?></p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                            $status_class = match ($row['status_validasi']) {
                                                'valid' => 'bg-green-100 text-green-800',
                                                'invalid' => 'bg-red-100 text-red-800',
                                                default => 'bg-yellow-100 text-yellow-800'
                                            };
                                            $status_icon = match ($row['status_validasi']) {
                                                'valid' => 'fa-check-circle',
                                                'invalid' => 'fa-times-circle',
                                                default => 'fa-clock'
                                            };
                                            ?>
                                        <span
                                            class="px-2 py-1 rounded-full text-xs font-medium <?= $status_class ?> inline-flex items-center">
                                            <i class="fas <?= $status_icon ?> mr-1"></i>
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
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-6">
                        <i class="fas fa-chart-bar text-purple-500 mr-2"></i>Penyakit Terbanyak
                    </h3>
                    <div class="space-y-4">
                        <?php while ($row = mysqli_fetch_assoc($result_top_diseases)): ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-virus text-purple-500 mr-3"></i>
                                <span
                                    class="text-sm font-medium text-gray-700"><?= htmlspecialchars($row['nama_penyakit']) ?></span>
                            </div>
                            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-purple-100 text-purple-800">
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