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
    <title>Kelola Pertanyaan - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-50">
    <?php renderAdminSidebar('pertanyaan'); ?>

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
                            <span class="text-gray-500">Pertanyaan</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white rounded-lg shadow-md p-6 md:p-8 mb-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800">Kelola Pertanyaan</h2>
                    <a href="create.php"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm">
                        <i class="fas fa-plus mr-2"></i>
                        Tambah Pertanyaan
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
                                    placeholder="Cari pertanyaan...">
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
                                <th scope="col" class="px-6 py-3">Kode</th>
                                <th scope="col" class="px-6 py-3">Urutan</th>
                                <th scope="col" class="px-6 py-3">Pertanyaan</th>
                                <th scope="col" class="px-6 py-3">Gejala Terkait</th>
                                <th scope="col" class="px-6 py-3">Jenis Input</th>
                                <th scope="col" class="px-6 py-3">Kategori</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                                <th scope="col" class="px-6 py-3">
                                    <span class="sr-only">Aksi</span>
                                </th>
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
                                <td class="px-6 py-4 font-medium">
                                    <?= htmlspecialchars($pertanyaan['kode_pertanyaan']) ?>
                                </td>
                                <td class="px-6 py-4"><?= htmlspecialchars($pertanyaan['urutan']) ?></td>
                                <td class="px-6 py-4"><?= htmlspecialchars($pertanyaan['isi_pertanyaan']) ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span
                                            class="font-medium text-gray-900"><?= htmlspecialchars($pertanyaan['kode_gejala']) ?></span>
                                        <span
                                            class="text-sm text-gray-500"><?= htmlspecialchars($pertanyaan['nama_gejala']) ?></span>
                                    </div>
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
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <a href="edit.php?id=<?= $pertanyaan['id_pertanyaan'] ?>"
                                            class="text-blue-600 hover:text-blue-900 mr-3" title="Edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <button
                                            onclick="toggleStatus(<?= $pertanyaan['id_pertanyaan'] ?>, <?= $pertanyaan['status_aktif'] ?>)"
                                            class="<?= $pertanyaan['status_aktif'] ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' ?>"
                                            title="<?= $pertanyaan['status_aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                            <i
                                                class="fas <?= $pertanyaan['status_aktif'] ? 'fa-toggle-off' : 'fa-toggle-on' ?>"></i>
                                            <?= $pertanyaan['status_aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?>
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