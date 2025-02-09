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
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-50">
    <?php renderAdminSidebar('users'); ?>

    <!-- Main Content -->
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
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-300 mx-2"></i>
                            <span class="text-gray-500">Users</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white rounded-lg shadow-md p-6 md:p-8 mb-8">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-800">Kelola Users</h2>
                        <p class="text-gray-600 mt-1">Manajemen data pengguna sistem pakar</p>
                    </div>
                </div>

                <!-- Search and Filter Section -->
                <div class="mb-6">
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <label for="searchInput" class="sr-only">Search</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input type="text" id="searchInput"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
                                    placeholder="Cari user...">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">User</th>
                                <th class="px-6 py-3">Email</th>
                                <th class="px-6 py-3">Total Diagnosis</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Diagnosis Terakhir</th>
                                <th class="px-6 py-3">
                                    <span class="sr-only">Aksi</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div
                                                class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
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
                                <td class="px-6 py-4">
                                    <?= htmlspecialchars($row['email']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col space-y-1">
                                        <span class="text-sm text-gray-900"><?= $row['total_diagnosis'] ?>
                                            Diagnosis</span>
                                        <div class="flex items-center space-x-2">
                                            <span
                                                class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <?= $row['valid_diagnosis'] ?> Valid
                                            </span>
                                            <span
                                                class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <?= $row['invalid_diagnosis'] ?> Invalid
                                            </span>
                                            <span
                                                class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <?= $row['pending_diagnosis'] ?> Pending
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($row['status_aktif']): ?>
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Aktif
                                    </span>
                                    <?php else: ?>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Nonaktif
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?= $row['last_diagnosis'] ? date('d/m/Y H:i', strtotime($row['last_diagnosis'])) : '-' ?>
                                </td>
                                <td class="px-6 py-4">
                                    <button
                                        onclick="toggleUserStatus(<?= $row['id_user'] ?>, <?= $row['status_aktif'] ?>)"
                                        class="text-blue-600 hover:text-blue-900">
                                        <i
                                            class="fas <?= $row['status_aktif'] ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
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

    <script>
    // Search functionality with improved filtering
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

    // Toggle user status with improved UI
    function toggleUserStatus(userId, currentStatus) {
        const newStatus = !currentStatus;
        const action = newStatus ? 'mengaktifkan' : 'menonaktifkan';

        Swal.fire({
            title: 'Konfirmasi Status',
            html: `<div class="text-left">
                     <p class="mb-2">Apakah Anda yakin ingin ${action} user ini?</p>
                     <p class="text-sm text-gray-500">User ${newStatus ? 'akan dapat' : 'tidak akan dapat'} mengakses sistem.</p>
                   </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: `<i class="fas ${newStatus ? 'fa-user-check' : 'fa-user-slash'} mr-2"></i>Ya, ${action}!`,
            cancelButtonText: '<i class="fas fa-times mr-2"></i>Batal',
            customClass: {
                confirmButton: 'flex items-center',
                cancelButton: 'flex items-center'
            }
        }).then((result) => {
            if (result.isConfirmed) {
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
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: `User berhasil ${action}!`,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: 'Terjadi kesalahan saat mengubah status user.',
                            });
                        }
                    });
            }
        });
    }
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>