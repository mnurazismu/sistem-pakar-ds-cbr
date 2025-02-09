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

// Ambil data kasus dengan join ke tabel penyakit
$query = "SELECT k.*, p.kode_penyakit, p.nama_penyakit, 
          (SELECT COUNT(*) FROM fitur_kasus fk WHERE fk.id_kasus = k.id_kasus) as jumlah_fitur,
          (SELECT GROUP_CONCAT(
              CONCAT('Fitur ', fk.id_fitur, ': ', fk.nilai_fitur) 
              SEPARATOR '\n'
           ) 
           FROM fitur_kasus fk 
           WHERE fk.id_kasus = k.id_kasus
           ORDER BY fk.id_fitur ASC
          ) as detail_fitur
          FROM kasus k 
          JOIN penyakit p ON k.id_penyakit = p.id_penyakit 
          ORDER BY k.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kasus - Sistem Pakar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
</head>
<body class="bg-gray-50">
    <?php renderAdminSidebar('kasus'); ?>
    
    <!-- Main Content -->
    <div class="p-4 sm:ml-64">
        <div class="p-4">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Manajemen Kasus</h2>
                    <a href="create.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        Tambah Kasus
                    </a>
                </div>

                <!-- Search -->
                <div class="mb-4">
                    <input type="text" id="searchInput" placeholder="Cari kasus..." 
                           class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>

                <!-- Table -->
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3">No</th>
                                <th scope="col" class="px-6 py-3">Kode Kasus</th>
                                <th scope="col" class="px-6 py-3">Penyakit</th>
                                <th scope="col" class="px-6 py-3">Deskripsi</th>
                                <th scope="col" class="px-6 py-3">Detail Fitur</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                                <th scope="col" class="px-6 py-3">Tanggal Validasi</th>
                                <th scope="col" class="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while ($kasus = mysqli_fetch_assoc($result)): 
                                $status_class = match($kasus['status_validasi']) {
                                    'valid' => 'bg-green-100 text-green-800',
                                    'invalid' => 'bg-red-100 text-red-800',
                                    default => 'bg-yellow-100 text-yellow-800'
                                };
                            ?>
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4"><?= $no++ ?></td>
                                <td class="px-6 py-4 font-medium"><?= htmlspecialchars($kasus['kode_kasus']) ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-medium"><?= htmlspecialchars($kasus['kode_penyakit']) ?></span>
                                        <span class="text-sm text-gray-500"><?= htmlspecialchars($kasus['nama_penyakit']) ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="max-w-xs overflow-hidden text-ellipsis">
                                        <?= nl2br(htmlspecialchars($kasus['deskripsi_kasus'])) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm">
                                        <?php if ($kasus['detail_fitur']): ?>
                                            <?= htmlspecialchars($kasus['detail_fitur']) ?>
                                        <?php else: ?>
                                            <span class="text-gray-500">Belum ada fitur</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-1">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <?= $kasus['jumlah_fitur'] ?> fitur
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium <?= $status_class ?>">
                                        <?= ucfirst($kasus['status_validasi']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?= $kasus['tanggal_validasi'] ? date('d/m/Y H:i', strtotime($kasus['tanggal_validasi'])) : '-' ?>
                                </td>
                                <td class="px-6 py-4 space-x-3">
                                    <a href="edit.php?id=<?= $kasus['id_kasus'] ?>" 
                                       class="font-medium text-blue-600 hover:text-blue-800">Edit</a>
                                    <a href="fitur.php?id=<?= $kasus['id_kasus'] ?>" 
                                       class="font-medium text-green-600 hover:text-green-800">Fitur</a>
                                    <button onclick="showValidasiModal(<?= $kasus['id_kasus'] ?>, '<?= $kasus['status_validasi'] ?>')" 
                                            class="font-medium <?= $kasus['status_validasi'] == 'pending' ? 'text-yellow-600 hover:text-yellow-800' : ($kasus['status_validasi'] == 'valid' ? 'text-green-600 hover:text-green-800' : 'text-red-600 hover:text-red-800') ?>">
                                        Validasi
                                    </button>
                                    <button onclick="hapusKasus(<?= $kasus['id_kasus'] ?>)" 
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

    <!-- Modal Validasi -->
    <div id="validasiModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Validasi Kasus
                    </h3>
                    <button type="button" onclick="closeValidasiModal()" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                    </button>
                </div>
                <form id="validasiForm" action="validasi.php" method="POST" class="p-4 md:p-5">
                    <input type="hidden" name="id_kasus" id="validasi_id_kasus">
                    <div class="mb-4">
                        <label class="block mb-2 text-sm font-medium text-gray-900">
                            Status Validasi
                        </label>
                        <select name="status_validasi" id="status_validasi" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            <option value="pending">Pending</option>
                            <option value="valid">Valid</option>
                            <option value="tidak_valid">Tidak Valid</option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" onclick="closeValidasiModal()"
                                class="text-gray-500 bg-gray-200 hover:bg-gray-300 font-medium rounded-lg text-sm px-5 py-2.5">
                            Batal
                        </button>
                        <button type="submit"
                                class="text-white bg-blue-500 hover:bg-blue-600 font-medium rounded-lg text-sm px-5 py-2.5">
                            Simpan
                        </button>
                    </div>
                </form>
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
    function hapusKasus(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Kasus yang dihapus tidak dapat dikembalikan!",
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

    function showValidasiModal(id_kasus, current_status) {
        document.getElementById('validasi_id_kasus').value = id_kasus;
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>
</html>