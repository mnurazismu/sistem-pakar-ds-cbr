<?php
require_once '../../config/database.php';
require_once '../../auth/functions.php';
require_once '../components/sidebar.php';

// Cek login
if (!isLoggedIn()) {
    header("Location: ../../auth/login.php");
    exit;
} elseif (!isUser()) {
    header("Location: ../../admin/dashboard.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// Ambil data user
$query = "SELECT * FROM users WHERE id_user = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Ambil statistik diagnosis
$query_stats = "SELECT 
                COUNT(*) as total_diagnosis,
                COUNT(CASE WHEN status_validasi = 'valid' THEN 1 END) as valid_diagnosis,
                COUNT(CASE WHEN status_validasi = 'invalid' THEN 1 END) as invalid_diagnosis,
                COUNT(CASE WHEN status_validasi = 'pending' THEN 1 END) as pending_diagnosis
                FROM hasil_diagnosis 
                WHERE id_user = ?";
$stmt = $conn->prepare($query_stats);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Sistem Pakar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Tambahan Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php renderUserSidebar('profil'); ?>

    <div class="p-4 sm:ml-64">
        <div class="p-4 border-2 border-gray-200 rounded-lg">
            <!-- Header Section -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">Profil Saya</h1>
                    <p class="text-gray-600 mt-1">Kelola informasi profil dan preferensi akun Anda</p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500">ID: #<?= str_pad($user['id_user'], 5, '0', STR_PAD_LEFT) ?></span>
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">User</span>
                </div>
            </div>

            <!-- Statistik Diagnosis dengan Ikon -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                            <i class="fas fa-chart-line text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Diagnosis</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?= $stats['total_diagnosis'] ?></h3>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                            <div class="bg-blue-600 h-1.5 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Diagnosis Valid</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?= $stats['valid_diagnosis'] ?></h3>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                            <div class="bg-green-600 h-1.5 rounded-full" 
                                 style="width: <?= ($stats['total_diagnosis'] > 0 ? ($stats['valid_diagnosis']/$stats['total_diagnosis']*100) : 0) ?>%">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-600 mr-4">
                            <i class="fas fa-times-circle text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Diagnosis Invalid</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?= $stats['invalid_diagnosis'] ?></h3>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                            <div class="bg-red-600 h-1.5 rounded-full" 
                                 style="width: <?= ($stats['total_diagnosis'] > 0 ? ($stats['invalid_diagnosis']/$stats['total_diagnosis']*100) : 0) ?>%">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                            <i class="fas fa-clock text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Menunggu Validasi</p>
                            <h3 class="text-2xl font-bold text-gray-800"><?= $stats['pending_diagnosis'] ?></h3>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                            <div class="bg-yellow-600 h-1.5 rounded-full" 
                                 style="width: <?= ($stats['total_diagnosis'] > 0 ? ($stats['pending_diagnosis']/$stats['total_diagnosis']*100) : 0) ?>%">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Profil dengan Ikon -->
            <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100 mb-8">
                <div class="flex items-center mb-6">
                    <i class="fas fa-user-circle text-2xl text-blue-600 mr-3"></i>
                    <h2 class="text-xl font-semibold text-gray-800">Informasi Profil</h2>
                </div>
                <form id="profileForm" action="update_profile.php" method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-user text-gray-400 mr-2"></i>
                                Nama Lengkap
                            </label>
                            <input type="text" 
                                   name="nama" 
                                   value="<?= htmlspecialchars($user['nama_lengkap']) ?>" 
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                   required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-envelope text-gray-400 mr-2"></i>
                                Email
                            </label>
                            <input type="email" 
                                   name="email" 
                                   value="<?= htmlspecialchars($user['email']) ?>" 
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                   required>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" 
                                class="flex items-center px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Form Password dengan Ikon -->
            <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100">
                <div class="flex items-center mb-6">
                    <i class="fas fa-lock text-2xl text-blue-600 mr-3"></i>
                    <h2 class="text-xl font-semibold text-gray-800">Ganti Password</h2>
                </div>
                <form id="passwordForm" action="update_password.php" method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-key text-gray-400 mr-2"></i>
                                Password Lama
                            </label>
                            <input type="password" 
                                   name="old_password" 
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                   required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-lock text-gray-400 mr-2"></i>
                                Password Baru
                            </label>
                            <input type="password" 
                                   name="new_password" 
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                   required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-check-circle text-gray-400 mr-2"></i>
                                Konfirmasi Password Baru
                            </label>
                            <input type="password" 
                                   name="confirm_password" 
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                   required>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" 
                                class="flex items-center px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-key mr-2"></i>
                            Ganti Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/flowbite.min.js"></script>
    <script>
        // Handle profile form submission
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Simpan Perubahan?',
                text: 'Pastikan data yang dimasukkan sudah benar',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });

        // Handle password form submission
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const newPass = this.querySelector('[name="new_password"]').value;
            const confirmPass = this.querySelector('[name="confirm_password"]').value;
            
            if (newPass !== confirmPass) {
                Swal.fire({
                    icon: 'error',
                    title: 'Password Tidak Cocok',
                    text: 'Password baru dan konfirmasi password harus sama'
                });
                return;
            }
            
            if (newPass.length < 6) {
                Swal.fire({
                    icon: 'error',
                    title: 'Password Terlalu Pendek',
                    text: 'Password minimal 6 karakter'
                });
                return;
            }
            
            Swal.fire({
                title: 'Ganti Password?',
                text: 'Anda yakin ingin mengganti password?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Ganti',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });

        // Check for flash message
        <?php if (isset($_SESSION['flash_message'])): ?>
            Swal.fire({
                icon: '<?= $_SESSION['flash_message']['type'] === 'success' ? 'success' : 'error' ?>',
                title: '<?= $_SESSION['flash_message']['message'] ?>',
                showConfirmButton: false,
                timer: 2000
            });
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>
    </script>
</body>
</html>