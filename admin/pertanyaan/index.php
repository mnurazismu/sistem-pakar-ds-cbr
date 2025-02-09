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

// Ambil data pertanyaan dengan join ke tabel gejala
$query = "SELECT p.*, g.kode_gejala, g.nama_gejala 
          FROM pertanyaan p 
          JOIN gejala g ON p.id_gejala = g.id_gejala 
          ORDER BY p.urutan ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pertanyaan - Sistem Pakar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
</head>
<body class="bg-gray-50">
    <?php renderAdminSidebar('pertanyaan'); ?>
    
    <!-- Main Content -->
    <div class="p-4 sm:ml-64">
        <div class="p-4">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Manajemen Pertanyaan</h2>
                    <a href="create.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        Tambah Pertanyaan
                    </a>
                </div>

                <!-- Search -->
                <div class="mb-4">
                    <input type="text" id="searchInput" placeholder="Cari pertanyaan..." 
                           class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Table -->
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3">No</th>
                                <th scope="col" class="px-6 py-3">Kode</th>
                                <th scope="col" class="px-6 py-3">Urutan</th>
                                <th scope="col" class="px-6 py-3">Pertanyaan</th>
                                <th scope="col" class="px-6 py-3">Gejala Terkait</th>
                                <th scope="col" class="px-6 py-3">Jenis Input</th>
                                <th scope="col" class="px-6 py-3">Kategori</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                                <th scope="col" class="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while ($pertanyaan = mysqli_fetch_assoc($result)): 
                                $status_class = $pertanyaan['status_aktif'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                $status_text = $pertanyaan['status_aktif'] ? 'Aktif' : 'Nonaktif';
                            ?>
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4"><?= $no++ ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($pertanyaan['kode_pertanyaan']) ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($pertanyaan['urutan']) ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($pertanyaan['isi_pertanyaan']) ?></td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center">
                                        <span class="font-medium"><?= htmlspecialchars($pertanyaan['kode_gejala']) ?></span>
                                        <span class="mx-1">-</span>
                                        <span><?= htmlspecialchars($pertanyaan['nama_gejala']) ?></span>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?= htmlspecialchars($pertanyaan['jenis_input']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4"><?= htmlspecialchars($pertanyaan['kategori'] ?? '-') ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium <?= $status_class ?>">
                                        <?= $status_text ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 space-x-3">
                                    <a href="edit.php?id=<?= $pertanyaan['id_pertanyaan'] ?>" 
                                       class="font-medium text-blue-600 hover:text-blue-800">Edit</a>
                                    <button onclick="toggleStatus(<?= $pertanyaan['id_pertanyaan'] ?>, <?= $pertanyaan['status_aktif'] ?>)" 
                                            class="font-medium <?= $pertanyaan['status_aktif'] ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' ?>">
                                        <?= $pertanyaan['status_aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?>
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
    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let searchQuery = this.value.toLowerCase();
        let tableRows = document.querySelectorAll('tbody tr');
        
        tableRows.forEach(row => {
            let text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchQuery) ? '' : 'none';
        });
    });

    // Toggle status
    function toggleStatus(id, currentStatus) {
        let action = currentStatus ? 'menonaktifkan' : 'mengaktifkan';
        
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: `Anda akan ${action} pertanyaan ini!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, lanjutkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `toggle_status.php?id=${id}`;
            }
        });
    }
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>
</html>