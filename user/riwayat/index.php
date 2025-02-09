<?php
require_once '../../config/database.php';
require_once '../../auth/functions.php';
require_once '../components/sidebar.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    header("Location: ../../auth/login.php");
    exit;
} elseif (!isUser()) {
    header("Location: ../../admin/dashboard.php");
    exit;
}

// Ambil riwayat diagnosis untuk user yang sedang login
$id_user = $_SESSION['id_user'];
$query = "SELECT 
            hd.id_diagnosis,
            hd.tanggal_diagnosis,
            p_ds.nama_penyakit as ds_penyakit,
            hd.ds_nilai_kepercayaan,
            p_cbr.nama_penyakit as cbr_penyakit,
            hd.cbr_similarity,
            hd.status_validasi,
            hd.feedback_user,
            hd.keterangan_admin
          FROM hasil_diagnosis hd
          LEFT JOIN penyakit p_ds ON p_ds.id_penyakit = hd.ds_penyakit_id
          LEFT JOIN kasus k ON k.id_kasus = hd.cbr_kasus_id
          LEFT JOIN penyakit p_cbr ON p_cbr.id_penyakit = k.id_penyakit
          WHERE hd.id_user = ?
          ORDER BY hd.tanggal_diagnosis DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$riwayat = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Diagnosis - Sistem Pakar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-50">
    <?php renderUserSidebar('riwayat'); ?>

    <div class="p-4 sm:ml-64">
        <div class="p-4 border-2 border-gray-200 rounded-lg">
            <!-- Header Section -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">Riwayat Diagnosis</h1>
                    <p class="text-gray-600 mt-1">Daftar riwayat diagnosis yang telah Anda lakukan</p>
                </div>
                <a href="../diagnosis/index.php" 
                   class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Diagnosis Baru
                </a>
            </div>

            <!-- Filter & Search Section -->
            <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <select id="statusFilter" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="">Semua Status</option>
                                <option value="valid">Valid</option>
                                <option value="invalid">Invalid</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <!-- <div class="relative">
                            <input type="date" id="dateFilter" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div> -->
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" id="searchInput" 
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5" 
                               placeholder="Cari Riwayat Diagnosis">
                    </div>
                </div>
            </div>

            <!-- Riwayat Table -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="relative overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-4">Tanggal</th>
                                <th scope="col" class="px-6 py-4">Hasil DS</th>
                                <th scope="col" class="px-6 py-4">Nilai DS</th>
                                <th scope="col" class="px-6 py-4">Hasil CBR</th>
                                <th scope="col" class="px-6 py-4">Nilai CBR</th>
                                <th scope="col" class="px-6 py-4">Status</th>
                                <th scope="col" class="px-6 py-4">Feedback</th>
                                <th scope="col" class="px-6 py-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($riwayat)): ?>
                                <tr class="bg-white border-b">
                                    <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-history text-4xl mb-2 text-gray-400"></i>
                                            <p>Belum ada riwayat diagnosis</p>
                                            <a href="../diagnosis/index.php" class="text-blue-600 hover:underline mt-2">
                                                Mulai Diagnosis Sekarang
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($riwayat as $r): ?>
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-col">
                                                <span class="font-medium text-gray-900">
                                                    <?= date('d/m/Y', strtotime($r['tanggal_diagnosis'])) ?>
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    <?= date('H:i', strtotime($r['tanggal_diagnosis'])) ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-gray-900">
                                                <?= htmlspecialchars($r['ds_penyakit'] ?? '-') ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <?= number_format($r['ds_nilai_kepercayaan'] * 100, 2) ?>%
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-gray-900">
                                                <?= htmlspecialchars($r['cbr_penyakit'] ?? '-') ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <?= number_format($r['cbr_similarity'] * 100, 2) ?>%
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 rounded-full text-xs font-medium
                                                <?php
                                                switch ($r['status_validasi']) {
                                                    case 'valid':
                                                        echo 'bg-green-100 text-green-800';
                                                        break;
                                                    case 'invalid':
                                                        echo 'bg-red-100 text-red-800';
                                                        break;
                                                    default:
                                                        echo 'bg-yellow-100 text-yellow-800';
                                                }
                                                ?>">
                                                <?= ucfirst($r['status_validasi']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if ($r['feedback_user']): ?>
                                                <span class="inline-flex items-center text-xs text-gray-600">
                                                    <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                                    Sudah diberi feedback
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center text-xs text-gray-400">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    Belum ada feedback
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center space-x-3">
                                                <a href="../diagnosis/result.php?id=<?= $r['id_diagnosis'] ?>" 
                                                   class="text-blue-600 hover:text-blue-900">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if (!$r['feedback_user']): ?>
                                                    <button onclick="showFeedbackModal(<?= $r['id_diagnosis'] ?>)"
                                                            class="text-green-600 hover:text-green-900">
                                                        <i class="fas fa-comment"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Feedback -->
    <div id="feedbackModal" tabindex="-1" aria-hidden="true" 
         class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow">
                <div class="flex items-center justify-between p-4 border-b rounded-t">
                    <h3 class="text-xl font-semibold text-gray-900">
                        Berikan Feedback
                    </h3>
                    <button type="button" onclick="hideFeedbackModal()"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form id="feedbackForm" action="process_feedback.php" method="POST" class="p-6">
                    <input type="hidden" name="id_diagnosis" id="feedbackDiagnosisId">
                    <div class="mb-4">
                        <label for="feedback" class="block mb-2 text-sm font-medium text-gray-900">
                            Feedback Anda
                        </label>
                        <textarea id="feedback" name="feedback" rows="4" 
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                required></textarea>
                    </div>
                    <button type="submit" 
                            class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Kirim Feedback
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/flowbite.min.js"></script>
    <script>
        // Check for flash message
        <?php if (isset($_SESSION['flash_message'])): ?>
            Swal.fire({
                icon: '<?= $_SESSION['flash_message']['type'] === 'success' ? 'success' : 'error' ?>',
                title: '<?= $_SESSION['flash_message']['message'] ?>',
                showConfirmButton: false,
                timer: 2000
            });
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        // Fungsi untuk modal feedback
        function showFeedbackModal(diagnosisId) {
            document.getElementById('feedbackDiagnosisId').value = diagnosisId;
            const modal = document.getElementById('feedbackModal');
            modal.classList.remove('hidden');
        }

        function hideFeedbackModal() {
            const modal = document.getElementById('feedbackModal');
            modal.classList.add('hidden');
        }

        // Filter dan Search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const statusFilter = document.getElementById('statusFilter');
            const dateFilter = document.getElementById('dateFilter');
            const searchInput = document.getElementById('searchInput');
            const tableRows = document.querySelectorAll('tbody tr');

            function filterTable() {
                const status = statusFilter.value.toLowerCase();
                const date = dateFilter.value;
                const searchTerm = searchInput.value.toLowerCase();

                tableRows.forEach(row => {
                    if (row.classList.contains('empty-state')) return; // Skip empty state row

                    const statusCell = row.querySelector('td:nth-child(6)').textContent.toLowerCase();
                    const dateCell = row.querySelector('td:nth-child(1)').textContent;
                    const rowText = row.textContent.toLowerCase();
                    
                    // Convert date format from DD/MM/YYYY to YYYY-MM-DD for comparison
                    const dateParts = dateCell.split('/');
                    const rowDate = dateParts.length > 2 ? 
                        `${dateParts[2].split(' ')[0]}-${dateParts[1]}-${dateParts[0]}` : '';

                    const matchesStatus = status === '' || statusCell.includes(status);
                    const matchesDate = date === '' || rowDate === date;
                    const matchesSearch = searchTerm === '' || rowText.includes(searchTerm);

                    row.style.display = 
                        matchesStatus && matchesDate && matchesSearch ? '' : 'none';
                });

                // Show/hide empty state
                const visibleRows = Array.from(tableRows).filter(row => 
                    row.style.display !== 'none' && !row.classList.contains('empty-state')
                ).length;

                const emptyStateRow = document.querySelector('.empty-state');
                if (emptyStateRow) {
                    emptyStateRow.style.display = visibleRows === 0 ? '' : 'none';
                }
            }

            // Event listeners for filters
            statusFilter.addEventListener('change', filterTable);
            dateFilter.addEventListener('change', filterTable);
            
            // Debounce search input to improve performance
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(filterTable, 300);
            });

            // Initialize datepicker if using a datepicker library
            // You might want to add a datepicker library like flatpickr for better date selection
            
            // Clear filters button functionality
            const clearFilters = () => {
                statusFilter.value = '';
                dateFilter.value = '';
                searchInput.value = '';
                filterTable();
            };

            // Add clear filters button if needed
            // const clearButton = document.getElementById('clearFilters');
            // if (clearButton) {
            //     clearButton.addEventListener('click', clearFilters);
            // }
        });

        // Handle feedback form submission
        document.getElementById('feedbackForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const feedback = document.getElementById('feedback').value.trim();
            if (feedback.length < 10) {
                Swal.fire({
                    icon: 'error',
                    title: 'Feedback terlalu pendek',
                    text: 'Mohon berikan feedback minimal 10 karakter'
                });
                return;
            }

            Swal.fire({
                title: 'Kirim Feedback?',
                text: 'Feedback tidak dapat diubah setelah dikirim',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Kirim',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    </script>
</body>
</html>