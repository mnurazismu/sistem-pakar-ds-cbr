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

// Ambil data gejala
$query = "SELECT g.*, 
          (SELECT COUNT(*) FROM rule_ds rd WHERE rd.id_gejala = g.id_gejala) as jumlah_rule,
          (SELECT COUNT(*) FROM fitur_kasus fk WHERE fk.id_gejala = g.id_gejala) as jumlah_kasus
          FROM gejala g 
          ORDER BY g.kode_gejala ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Gejala - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-50">
    <?php renderAdminSidebar('gejala'); ?>

    <div class="p-4 sm:ml-64">
        <div class="p-4 bg-white rounded-lg shadow-md">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Kelola Gejala</h1>
                <p class="text-gray-600">Manajemen data gejala untuk sistem pakar</p>
            </div>

            <!-- Search and Add Button -->
            <div class="mb-6 flex flex-col md:flex-row gap-4 justify-between items-center">
                <div class="w-full md:w-1/3">
                    <div class="relative">
                        <input type="text" id="searchInput" 
                               class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 pl-10"
                               placeholder="Cari gejala...">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>
                <button onclick="window.location.href='create.php'" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Gejala
                </button>
            </div>

            <!-- Gejala Table -->
            <div class="bg-white rounded-lg shadow-md overflow-x-auto">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Gejala</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Digunakan Pada</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?= htmlspecialchars($row['kode_gejala']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($row['nama_gejala']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <span class="mr-2">
                                            <i class="fas fa-book text-blue-600"></i> <?= $row['jumlah_rule'] ?> Rule
                                        </span>
                                        <span>
                                            <i class="fas fa-cases text-green-600"></i> <?= $row['jumlah_kasus'] ?> Kasus
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="edit.php?id=<?= $row['id_gejala'] ?>" 
                                       class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <button onclick="deleteGejala(<?= $row['id_gejala'] ?>, '<?= $row['kode_gejala'] ?>')" 
                                            class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i> Hapus
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
        document.getElementById('searchInput').addEventListener('input', function() {
            let filter = this.value.toLowerCase();
            let tableBody = document.querySelector('tbody');
            let rows = tableBody.getElementsByTagName('tr');
            
            for (let row of rows) {
                let kodeCell = row.cells[0].textContent;
                let namaCell = row.cells[1].textContent;
                
                if (kodeCell.toLowerCase().includes(filter) || 
                    namaCell.toLowerCase().includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });

        // Delete confirmation
        function deleteGejala(id, kode) {
            Swal.fire({
                title: 'Hapus Gejala?',
                text: `Anda akan menghapus gejala dengan kode ${kode}. Tindakan ini tidak dapat dibatalkan!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `delete.php?id=${id}`;
                }
            });
        }
    </script>
</body>

</html>