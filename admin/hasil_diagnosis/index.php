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

// Ambil data hasil diagnosis dengan join ke tabel terkait
$query = "SELECT hd.*, 
          u.nama_lengkap as nama_user, 
          p1.kode_penyakit as ds_kode_penyakit, 
          p1.nama_penyakit as ds_nama_penyakit,
          k.kode_kasus,
          k.deskripsi_kasus,
          p2.nama_penyakit as cbr_nama_penyakit
          FROM hasil_diagnosis hd
          JOIN users u ON hd.id_user = u.id_user
          JOIN penyakit p1 ON hd.ds_penyakit_id = p1.id_penyakit
          JOIN kasus k ON hd.cbr_kasus_id = k.id_kasus
          JOIN penyakit p2 ON k.id_penyakit = p2.id_penyakit
          ORDER BY hd.tanggal_diagnosis DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Diagnosis - Sistem Pakar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php renderAdminSidebar('hasil_diagnosis'); ?>
    
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
                            <span class="text-gray-500">Hasil Diagnosis</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-800">Hasil Diagnosis</h2>
                        <p class="text-sm text-gray-600 mt-1">Kelola dan validasi hasil diagnosis pengguna</p>
                    </div>
                </div>

                <!-- Search and Filter -->
                <div class="mb-6 flex flex-col md:flex-row gap-4 justify-between items-center">
                    <div class="w-full md:w-1/3">
                        <div class="relative">
                            <input type="text" id="searchInput" 
                                   class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 pl-10"
                                   placeholder="Cari hasil diagnosis...">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <select id="statusFilter" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="valid">Valid</option>
                            <option value="invalid">Invalid</option>
                        </select>
                    </div>
                </div>

                <!-- Table -->
                <div class="relative overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">No</th>
                                <th class="px-6 py-3">Tanggal</th>
                                <th class="px-6 py-3">User</th>
                                <th class="px-6 py-3">Hasil DS</th>
                                <th class="px-6 py-3">Hasil CBR</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)): 
                                $status_class = match($row['status_validasi']) {
                                    'valid' => 'bg-green-100 text-green-800',
                                    'invalid' => 'bg-red-100 text-red-800',
                                    default => 'bg-yellow-100 text-yellow-800'
                                };
                            ?>
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4"><?= $no++ ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= date('d/m/Y', strtotime($row['tanggal_diagnosis'])) ?>
                                    <div class="text-xs text-gray-500">
                                        <?= date('H:i', strtotime($row['tanggal_diagnosis'])) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4"><?= htmlspecialchars($row['nama_user']) ?></td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">
                                        <?= htmlspecialchars($row['ds_kode_penyakit']) ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?= htmlspecialchars($row['ds_nama_penyakit']) ?>
                                        <span class="text-xs">(<?= number_format($row['ds_nilai_kepercayaan'] * 100, 1) ?>%)</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">
                                        <?= htmlspecialchars($row['kode_kasus']) ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?= htmlspecialchars($row['cbr_nama_penyakit']) ?>
                                        <span class="text-xs">(<?= number_format($row['cbr_similarity'] * 100, 1) ?>%)</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium <?= $status_class ?>">
                                        <?= ucfirst($row['status_validasi']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <a href="detail.php?id=<?= $row['id_diagnosis'] ?>" 
                                           class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button onclick="showValidasiModal(<?= $row['id_diagnosis'] ?>, '<?= $row['status_validasi'] ?>')" 
                                                class="text-yellow-600 hover:text-yellow-900">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Validasi -->
    <div id="validasiModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Validasi Hasil Diagnosis
                    </h3>
                    <button type="button" onclick="closeValidasiModal()" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <!-- Modal body -->
                <form id="validasiForm" action="validasi.php" method="POST" class="p-4 md:p-5">
                    <input type="hidden" name="id_diagnosis" id="validasi_id_diagnosis">
                    <div class="grid gap-4 mb-4">
                        <div>
                            <label for="status_validasi" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Status Validasi</label>
                            <select id="status_validasi" name="status_validasi" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="pending">Pending</option>
                                <option value="valid">Valid</option>
                                <option value="invalid">Invalid</option>
                            </select>
                        </div>
                        <div>
                            <label for="keterangan_admin" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Keterangan</label>
                            <textarea id="keterangan_admin" name="keterangan_admin" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500" placeholder="Tambahkan keterangan validasi..."></textarea>
                        </div>
                    </div>
                    <div class="flex items-center justify-end space-x-3">
                        <button type="button" onclick="closeValidasiModal()" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">
                            Batal
                        </button>
                        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Search and filter functionality
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const tableRows = document.querySelectorAll('tbody tr');

        function filterTable() {
            const searchQuery = searchInput.value.toLowerCase();
            const statusQuery = statusFilter.value.toLowerCase();

            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const status = row.querySelector('td:nth-child(6)').textContent.toLowerCase();
                
                const matchesSearch = text.includes(searchQuery);
                const matchesStatus = statusQuery === '' || status.includes(statusQuery);

                row.style.display = matchesSearch && matchesStatus ? '' : 'none';
            });
        }

        searchInput.addEventListener('input', filterTable);
        statusFilter.addEventListener('change', filterTable);

        function showValidasiModal(id_diagnosis, current_status) {
            document.getElementById('validasi_id_diagnosis').value = id_diagnosis;
            document.getElementById('status_validasi').value = current_status;
            document.getElementById('validasiModal').classList.remove('hidden');
            document.getElementById('validasiModal').classList.add('flex');
        }

        function closeValidasiModal() {
            document.getElementById('validasiModal').classList.add('hidden');
            document.getElementById('validasiModal').classList.remove('flex');
        }
    </script>

    <?php if (isset($_SESSION['flash_message'])): ?>
    <script>
        Swal.fire({
            icon: '<?= $_SESSION['flash_message']['type'] ?>',
            title: '<?= $_SESSION['flash_message']['type'] == 'success' ? 'Berhasil!' : 'Error!' ?>',
            text: '<?= $_SESSION['flash_message']['message'] ?>',
            showConfirmButton: false,
            timer: 1500
        });
    </script>
    <?php 
    unset($_SESSION['flash_message']);
    endif; 
    ?>
</body>
</html>