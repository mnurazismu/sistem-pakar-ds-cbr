<?php
require_once '../../config/database.php';
require_once '../../auth/functions.php';
require_once '../components/sidebar.php';  // Include sidebar component

// Cek login dan role
if (!isLoggedIn()) {
    header("Location: ../../auth/login.php");
    exit;
} elseif (!isAdmin()) {
    header("Location: ../../user/dashboard.php");
    exit;
}

// $email = $_SESSION['email'];
$nama_lengkap = $_SESSION['nama_lengkap'];

// Ambil data users dengan statistik diagnosis
$query = "SELECT u.*, 
          COUNT(hd.id_diagnosis) as total_diagnosis,
          COUNT(CASE WHEN hd.status_validasi = 'valid' THEN 1 END) as valid_diagnosis,
          COUNT(CASE WHEN hd.status_validasi = 'invalid' THEN 1 END) as invalid_diagnosis,
          COUNT(CASE WHEN hd.status_validasi = 'pending' THEN 1 END) as pending_diagnosis,
          MAX(hd.tanggal_diagnosis) as last_diagnosis
          FROM users u 
          LEFT JOIN hasil_diagnosis hd ON u.id_user = hd.id_user
          WHERE u.tipe_user = 'User'
          GROUP BY u.id_user
          ORDER BY u.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Users - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-50">
    <?php renderAdminSidebar('users'); // Render sidebar dengan halaman aktif ?>
    
    <!-- Main Content -->
    <div class="p-4 sm:ml-64">
        <div class="p-4 bg-white rounded-lg shadow-md">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Kelola Users</h1>
                <p class="text-gray-600">Manajemen data pengguna sistem pakar</p>
            </div>

            <!-- Search and Filter -->
            <div class="mb-6 flex flex-col md:flex-row gap-4 justify-between items-center">
                <div class="w-full md:w-1/3">
                    <div class="relative">
                        <input type="text" id="searchInput" 
                               class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 pl-10"
                               placeholder="Cari user...">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="bg-white rounded-lg shadow-md overflow-x-auto">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Diagnosis</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diagnosis Terakhir</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span class="text-blue-600 font-semibold text-lg">
                                                    <?= strtoupper(substr($row['nama_lengkap'], 0, 1)) ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($row['nama_lengkap']) ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Bergabung: <?= date('d/m/Y', strtotime($row['created_at'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($row['email']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?= $row['total_diagnosis'] ?> Diagnosis</div>
                                    <div class="text-xs text-gray-500">
                                        <span class="text-green-600"><?= $row['valid_diagnosis'] ?> Valid</span> •
                                        <span class="text-red-600"><?= $row['invalid_diagnosis'] ?> Invalid</span> •
                                        <span class="text-yellow-600"><?= $row['pending_diagnosis'] ?> Pending</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($row['status_aktif']): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Nonaktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= $row['last_diagnosis'] ? date('d/m/Y H:i', strtotime($row['last_diagnosis'])) : '-' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button onclick="toggleUserStatus(<?= $row['id_user'] ?>, <?= $row['status_aktif'] ?>)" 
                                            class="text-blue-600 hover:text-blue-900">
                                        <?= $row['status_aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                let nameCell = row.querySelector('td:first-child');
                let emailCell = row.querySelector('td:nth-child(2)');
                let name = nameCell.textContent.toLowerCase();
                let email = emailCell.textContent.toLowerCase();
                
                if (name.includes(filter) || email.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Toggle user status
        function toggleUserStatus(userId, currentStatus) {
            const newStatus = !currentStatus;
            const action = newStatus ? 'mengaktifkan' : 'menonaktifkan';
            
            Swal.fire({
                title: `Konfirmasi ${action} user`,
                text: `Apakah Anda yakin ingin ${action} user ini?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send AJAX request to update status
                    fetch('toggle_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id_user=${userId}&status=${newStatus ? 1 : 0}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire(
                                'Berhasil!',
                                `User berhasil ${action}!`,
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Gagal!',
                                'Terjadi kesalahan saat mengubah status user.',
                                'error'
                            );
                        }
                    });
                }
            });
        }
    </script>
</body>

</html>