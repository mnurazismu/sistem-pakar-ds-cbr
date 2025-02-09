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
    <title>Kelola Kasus - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-50">
    <?php renderAdminSidebar('kasus'); ?>

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
                            <span class="text-gray-500">Kasus</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white rounded-lg shadow-md p-6 md:p-8 mb-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800">Kelola Kasus</h2>
                    <a href="create.php"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm">
                        <i class="fas fa-plus mr-2"></i>
                        Tambah Kasus
                    </a>
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
                                    placeholder="Cari kasus...">
                            </div>
                        </div>
                    </div>
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
                                <th scope="col" class="px-6 py-3">
                                    <span class="sr-only">Aksi</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while ($kasus = mysqli_fetch_assoc($result)):
                                $status_class = match ($kasus['status_validasi']) {
                                    'valid' => 'bg-green-100 text-green-800',
                                    'invalid' => 'bg-red-100 text-red-800',
                                    default => 'bg-yellow-100 text-yellow-800'
                                };
                                ?>
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4"><?= $no++ ?></td>
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    <?= htmlspecialchars($kasus['kode_kasus']) ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900">
                                            <?= htmlspecialchars($kasus['kode_penyakit']) ?>
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            <?= htmlspecialchars($kasus['nama_penyakit']) ?>
                                        </span>
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
                                        <div class="space-y-1">
                                            <?php foreach (explode("\n", $kasus['detail_fitur']) as $fitur): ?>
                                            <div class="text-gray-600"><?= htmlspecialchars($fitur) ?></div>
                                            <?php endforeach; ?>
                                        </div>
                                        <?php else: ?>
                                        <span class="text-gray-500">Belum ada fitur</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-2">
                                        <span
                                            class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
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
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <a href="edit.php?id=<?= $kasus['id_kasus'] ?>"
                                            class="text-blue-600 hover:text-blue-900" title="Edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <button
                                            onclick="showValidasiModal(<?= $kasus['id_kasus'] ?>, '<?= $kasus['status_validasi'] ?>')"
                                            class="text-yellow-600 hover:text-yellow-900" title="Validasi">
                                            <i class="fas fa-check-circle"></i> Validasi
                                        </button>
                                        <button onclick="hapusKasus(<?= $kasus['id_kasus'] ?>)"
                                            class="text-red-600 hover:text-red-900" title="Hapus">
                                            <i class="fas fa-trash"></i> Hapus
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
    <div id="validasiModal" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-check-circle mr-2 text-yellow-600"></i>
                        Validasi Kasus
                    </h3>
                    <button type="button" onclick="closeValidasiModal()"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                        <i class="fas fa-times"></i>
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
                            <i class="fas fa-times mr-2"></i>Batal
                        </button>
                        <button type="submit"
                            class="text-white bg-blue-600 hover:bg-blue-700 font-medium rounded-lg text-sm px-5 py-2.5">
                            <i class="fas fa-save mr-2"></i>Simpan
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

    // Delete confirmation with improved UI
    function hapusKasus(id) {
        Swal.fire({
            title: 'Konfirmasi Hapus',
            html: `<div class="text-left">
                     <p class="mb-2">Apakah Anda yakin ingin menghapus kasus ini?</p>
                     <p class="text-sm text-gray-500">Kasus yang dihapus tidak dapat dikembalikan!</p>
                   </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<i class="fas fa-trash mr-2"></i>Ya, Hapus!',
            cancelButtonText: '<i class="fas fa-times mr-2"></i>Batal',
            customClass: {
                confirmButton: 'flex items-center',
                cancelButton: 'flex items-center'
            }
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