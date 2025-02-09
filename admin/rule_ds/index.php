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

// Ambil data rule dengan join ke tabel gejala dan penyakit
$query = "SELECT r.*, g.kode_gejala, g.nama_gejala, g.belief_value, 
                 p.kode_penyakit, p.nama_penyakit, p.tingkat_keparahan
          FROM rule_ds r 
          JOIN gejala g ON r.id_gejala = g.id_gejala 
          JOIN penyakit p ON r.id_penyakit = p.id_penyakit 
          ORDER BY p.kode_penyakit ASC, g.kode_gejala ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Rule Dempster-Shafer - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-50">
    <?php renderAdminSidebar('rule_ds'); ?>

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
                            <span class="text-gray-500">Rule Dempster-Shafer</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white rounded-lg shadow-md p-6 md:p-8 mb-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800">Kelola Rule Dempster-Shafer</h2>
                    <a href="create.php"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm">
                        <i class="fas fa-plus mr-2"></i>
                        Tambah Rule
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
                                    placeholder="Cari rule...">
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
                                <th scope="col" class="px-6 py-3">Gejala</th>
                                <th scope="col" class="px-6 py-3">Belief (Gejala)</th>
                                <th scope="col" class="px-6 py-3">Penyakit</th>
                                <th scope="col" class="px-6 py-3">Tingkat Keparahan</th>
                                <th scope="col" class="px-6 py-3">Nilai Densitas</th>
                                <th scope="col" class="px-6 py-3">Keterangan</th>
                                <th scope="col" class="px-6 py-3">
                                    <span class="sr-only">Aksi</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $current_penyakit = '';
                            while ($rule = mysqli_fetch_assoc($result)):
                                if ($current_penyakit != $rule['kode_penyakit']):
                                    $current_penyakit = $rule['kode_penyakit'];
                                    ?>
                            <tr class="bg-gray-100">
                                <td colspan="8" class="px-6 py-3 font-medium text-gray-900">
                                    <div class="flex items-center">
                                        <i class="fas fa-virus mr-2 text-red-600"></i>
                                        <?= htmlspecialchars($rule['kode_penyakit'] . ' - ' . $rule['nama_penyakit']) ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4"><?= $no++ ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900">
                                            <?= htmlspecialchars($rule['kode_gejala']) ?>
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            <?= htmlspecialchars($rule['nama_gejala']) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?= number_format($rule['belief_value'], 2) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-medium">
                                    <?= htmlspecialchars($rule['kode_penyakit']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                        $severity_class = match ($rule['tingkat_keparahan']) {
                                            'Ringan' => 'bg-green-100 text-green-800',
                                            'Sedang' => 'bg-yellow-100 text-yellow-800',
                                            'Berat' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                        ?>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium <?= $severity_class ?>">
                                        <?= htmlspecialchars($rule['tingkat_keparahan']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        <?= number_format($rule['nilai_densitas'], 2) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?= htmlspecialchars($rule['keterangan'] ?? '-') ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <a href="edit.php?id=<?= $rule['id_rule'] ?>"
                                            class="text-blue-600 hover:text-blue-900" title="Edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <button
                                            onclick="deleteRule(<?= $rule['id_rule'] ?>, 
                                                                  '<?= htmlspecialchars($rule['kode_gejala'] . ' - ' . $rule['nama_gejala'], ENT_QUOTES) ?>', 
                                                                  '<?= htmlspecialchars($rule['kode_penyakit'] . ' - ' . $rule['nama_penyakit'], ENT_QUOTES) ?>')"
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
    function deleteRule(id, gejala, penyakit) {
        Swal.fire({
            title: 'Konfirmasi Hapus',
            html: `<div class="text-left">
                     <p class="mb-2">Apakah Anda yakin ingin menghapus rule ini?</p>
                     <div class="bg-gray-50 p-3 rounded-lg">
                       <p class="mb-1"><b>Gejala:</b> ${gejala}</p>
                       <p><b>Penyakit:</b> ${penyakit}</p>
                     </div>
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
                $.ajax({
                    url: 'delete.php',
                    type: 'POST',
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Terjadi kesalahan saat menghubungi server'
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