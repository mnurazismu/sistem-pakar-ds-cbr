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

// Ambil data solusi
$query = "SELECT * FROM solusi ORDER BY kode_solusi ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Solusi - Sistem Pakar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
</head>
<body class="bg-gray-50">
    <?php renderAdminSidebar('solusi'); ?>
    
    <!-- Main Content -->
    <div class="p-4 sm:ml-64">
        <div class="p-4">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Manajemen Solusi</h2>
                    <a href="create.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        Tambah Solusi
                    </a>
                </div>

                <!-- Search -->
                <div class="mb-4">
                    <input type="text" id="searchInput" placeholder="Cari solusi..." 
                           class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Table -->
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3">No</th>
                                <th scope="col" class="px-6 py-3">Kode</th>
                                <th scope="col" class="px-6 py-3">Nama Solusi</th>
                                <th scope="col" class="px-6 py-3">Deskripsi</th>
                                <th scope="col" class="px-6 py-3">Kategori</th>
                                <th scope="col" class="px-6 py-3">Penyakit Terkait</th>
                                <th scope="col" class="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while ($solusi = mysqli_fetch_assoc($result)): 
                                // Ambil data penyakit terkait
                                $id_solusi = $solusi['id_solusi'];
                                $query_penyakit = "SELECT p.kode_penyakit, p.nama_penyakit, ps.urutan_solusi 
                                                 FROM penyakit_solusi ps 
                                                 JOIN penyakit p ON ps.id_penyakit = p.id_penyakit 
                                                 WHERE ps.id_solusi = '$id_solusi' 
                                                 ORDER BY ps.urutan_solusi ASC";
                                $result_penyakit = mysqli_query($conn, $query_penyakit);
                            ?>
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4"><?= $no++ ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($solusi['kode_solusi']) ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($solusi['nama_solusi']) ?></td>
                                <td class="px-6 py-4">
                                    <div class="max-w-xs overflow-hidden">
                                        <?= nl2br(htmlspecialchars($solusi['deskripsi_solusi'])) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?= htmlspecialchars($solusi['kategori_solusi'] ?? '-') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <?php while ($penyakit = mysqli_fetch_assoc($result_penyakit)): ?>
                                            <div class="text-xs">
                                                <span class="font-medium"><?= htmlspecialchars($penyakit['kode_penyakit']) ?></span>
                                                <span class="text-gray-500">
                                                    (Urutan: <?= $penyakit['urutan_solusi'] ?>)
                                                </span>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 space-x-3">
                                    <a href="edit.php?id=<?= $solusi['id_solusi'] ?>" 
                                       class="font-medium text-blue-600 hover:text-blue-800">Edit</a>
                                    <button onclick="deleteSolusi(<?= $solusi['id_solusi'] ?>)" 
                                            class="font-medium text-red-600 hover:text-red-800">Hapus</button>
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

    // Delete confirmation
    function deleteSolusi(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Solusi akan dihapus beserta relasinya dengan penyakit!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `delete.php?id=${id}`;
            }
        });
    }

    // Tampilkan SweetAlert untuk flash message
    <?php if (isset($_SESSION['flash_message'])): ?>
        Swal.fire({
            icon: '<?= $_SESSION['flash_message']['type'] ?>',
            title: '<?= $_SESSION['flash_message']['type'] === 'success' ? 'Berhasil!' : 'Gagal!' ?>',
            text: '<?= $_SESSION['flash_message']['message'] ?>',
            showConfirmButton: true,
            timer: 3000
        });
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>
</html>