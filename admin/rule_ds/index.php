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
    <title>Manajemen Rule Dempster-Shafer - Sistem Pakar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
</head>
<body class="bg-gray-50">
    <?php renderAdminSidebar('rule_ds'); ?>
    
    <!-- Main Content -->
    <div class="p-4 sm:ml-64">
        <div class="p-4">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Manajemen Rule Dempster-Shafer</h2>
                    <a href="create.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        Tambah Rule
                    </a>
                </div>

                <!-- Search -->
                <div class="mb-4">
                    <input type="text" id="searchInput" placeholder="Cari rule..." 
                           class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
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
                                <th scope="col" class="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            $current_penyakit = '';
                            while ($rule = mysqli_fetch_assoc($result)): 
                                // Tambahkan header penyakit jika berbeda dari sebelumnya
                                if ($current_penyakit != $rule['kode_penyakit']):
                                    $current_penyakit = $rule['kode_penyakit'];
                            ?>
                                <tr class="bg-gray-100">
                                    <td colspan="8" class="px-6 py-3 font-medium">
                                        <?= htmlspecialchars($rule['kode_penyakit'] . ' - ' . $rule['nama_penyakit']) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4"><?= $no++ ?></td>
                                <td class="px-6 py-4">
                                    <span class="font-medium"><?= htmlspecialchars($rule['kode_gejala']) ?></span>
                                    <span class="block text-sm text-gray-500"><?= htmlspecialchars($rule['nama_gejala']) ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <?= number_format($rule['belief_value'], 2) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?= htmlspecialchars($rule['kode_penyakit']) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $severity_class = match($rule['tingkat_keparahan']) {
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
                                    <?= number_format($rule['nilai_densitas'], 2) ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?= htmlspecialchars($rule['keterangan'] ?? '-') ?>
                                </td>
                                <td class="px-6 py-4 space-x-3">
                                    <a href="edit.php?id=<?= $rule['id_rule'] ?>" 
                                       class="font-medium text-blue-600 hover:text-blue-800">Edit</a>
                                    <button onclick="deleteRule(<?= $rule['id_rule'] ?>, 
                                                              '<?= htmlspecialchars($rule['kode_gejala'] . ' - ' . $rule['nama_gejala'], ENT_QUOTES) ?>', 
                                                              '<?= htmlspecialchars($rule['kode_penyakit'] . ' - ' . $rule['nama_penyakit'], ENT_QUOTES) ?>')"
                                            class="text-red-600 hover:text-red-900">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
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

    // Delete confirmation
    function deleteRule(id, gejala, penyakit) {
        Swal.fire({
            title: 'Konfirmasi Hapus',
            html: `Apakah Anda yakin ingin menghapus rule ini?<br><br>
                   <b>Gejala:</b> ${gejala}<br>
                   <b>Penyakit:</b> ${penyakit}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Kirim request delete ke server
                $.ajax({
                    url: 'delete.php',
                    type: 'POST',
                    data: { id: id },
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
                                // Reload halaman setelah berhasil hapus
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