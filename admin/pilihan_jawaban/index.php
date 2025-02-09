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

// Ambil data pilihan jawaban dengan join ke tabel pertanyaan
$query = "SELECT pj.*, p.kode_pertanyaan, p.isi_pertanyaan, p.jenis_input 
          FROM pilihan_jawaban pj 
          JOIN pertanyaan p ON pj.id_pertanyaan = p.id_pertanyaan 
          ORDER BY p.kode_pertanyaan ASC, pj.urutan ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pilihan Jawaban - Sistem Pakar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
</head>
<body class="bg-gray-50">
    <?php renderAdminSidebar('pilihan_jawaban'); ?>
    
    <!-- Main Content -->
    <div class="p-4 sm:ml-64">
        <div class="p-4">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Manajemen Pilihan Jawaban</h2>
                    <a href="create.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        Tambah Pilihan Jawaban
                    </a>
                </div>

                <!-- Search -->
                <div class="mb-4">
                    <input type="text" id="searchInput" placeholder="Cari pilihan jawaban..." 
                           class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Table -->
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3">No</th>
                                <th scope="col" class="px-6 py-3">Pertanyaan</th>
                                <th scope="col" class="px-6 py-3">Jenis Input</th>
                                <th scope="col" class="px-6 py-3">Isi Pilihan</th>
                                <th scope="col" class="px-6 py-3">Bobot Nilai</th>
                                <th scope="col" class="px-6 py-3">Urutan</th>
                                <th scope="col" class="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            $current_pertanyaan = '';
                            while ($pilihan = mysqli_fetch_assoc($result)): 
                                // Tambahkan header pertanyaan jika berbeda dari sebelumnya
                                if ($current_pertanyaan != $pilihan['kode_pertanyaan']):
                                    $current_pertanyaan = $pilihan['kode_pertanyaan'];
                            ?>
                                <tr class="bg-gray-100">
                                    <td colspan="7" class="px-6 py-3 font-medium">
                                        <?= htmlspecialchars($pilihan['kode_pertanyaan'] . ' - ' . $pilihan['isi_pertanyaan']) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4"><?= $no++ ?></td>
                                <td class="px-6 py-4">
                                    <?= htmlspecialchars($pilihan['kode_pertanyaan']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?= htmlspecialchars($pilihan['jenis_input']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4"><?= htmlspecialchars($pilihan['isi_pilihan']) ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($pilihan['bobot_nilai']) ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($pilihan['urutan']) ?></td>
                                <td class="px-6 py-4 space-x-3">
                                    <a href="edit.php?id=<?= $pilihan['id_pilihan'] ?>" 
                                       class="font-medium text-blue-600 hover:text-blue-800">Edit</a>
                                    <button onclick="hapusPilihan(<?= $pilihan['id_pilihan'] ?>)" 
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
    function hapusPilihan(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Pilihan jawaban akan dihapus permanen!",
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
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>
</html>