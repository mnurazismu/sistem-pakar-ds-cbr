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

// Ambil data fitur
$query = "SELECT fk.*, k.kode_kasus, k.deskripsi_kasus, g.nama_gejala 
          FROM fitur_kasus fk
          JOIN kasus k ON fk.id_kasus = k.id_kasus
          JOIN gejala g ON fk.id_gejala = g.id_gejala
          ORDER BY k.kode_kasus ASC, fk.nilai_fitur DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Fitur Kasus - Sistem Pakar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
</head>
<body class="bg-gray-50">
    <?php renderAdminSidebar('fitur_kasus'); ?>
    
    <!-- Main Content -->
    <div class="p-4 sm:ml-64">
        <div class="p-4">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Daftar Fitur Kasus</h2>
                    <a href="create.php" class="bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg text-sm px-5 py-2.5">
                        Tambah Fitur
                    </a>
                </div>

                <!-- Search -->
                <div class="mb-4">
                    <input type="text" id="searchInput" 
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                           placeholder="Cari fitur...">
                </div>

                <!-- Table -->
                <div class="relative overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3">No</th>
                                <th scope="col" class="px-6 py-3">Kode Kasus</th>
                                <th scope="col" class="px-6 py-3">Gejala</th>
                                <th scope="col" class="px-6 py-3">Nilai Fitur</th>
                                <th scope="col" class="px-6 py-3">Bobot Fitur</th>
                                <th scope="col" class="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)): 
                            ?>
                            <tr class="bg-white border-b">
                                <td class="px-6 py-4"><?= $no++ ?></td>
                                <td class="px-6 py-4">
                                    <?= htmlspecialchars($row['kode_kasus']) ?>
                                    <div class="text-xs text-gray-500">
                                        <?= htmlspecialchars($row['deskripsi_kasus']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4"><?= htmlspecialchars($row['nama_gejala']) ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($row['nilai_fitur']) ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($row['bobot_fitur']) ?></td>
                                <td class="px-6 py-4">
                                    <a href="edit.php?id=<?= $row['id_fitur'] ?>" 
                                       class="font-medium text-blue-600 hover:text-blue-800 mr-3">Edit</a>
                                    <button onclick="hapusFitur(<?= $row['id_fitur'] ?>)" 
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    
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
        function hapusFitur(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Fitur kasus yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'delete.php?id=' + id;
                }
            });
        }

        // Flash message handling
        <?php if (isset($_SESSION['flash_message'])): ?>
        Swal.fire({
            icon: '<?= $_SESSION['flash_message']['type'] ?>',
            title: '<?= $_SESSION['flash_message']['type'] == 'success' ? 'Berhasil!' : 'Error!' ?>',
            text: '<?= $_SESSION['flash_message']['message'] ?>',
            showConfirmButton: false,
            timer: 1500
        });
        <?php 
        unset($_SESSION['flash_message']);
        endif; 
        ?>
    </script>
</body>
</html>