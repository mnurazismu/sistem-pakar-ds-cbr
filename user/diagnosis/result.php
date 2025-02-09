<?php
require_once '../../config/database.php';
require_once '../../auth/functions.php';
require_once '../components/sidebar.php';

// Cek login
if (!isLoggedIn()) {
    header("Location: ../../auth/login.php");
    exit;
} elseif (!isUser()) {
    header("Location: ../../admin/dashboard.php");
    exit;
}

// Cek parameter id
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_diagnosis = $_GET['id'];
$id_user = $_SESSION['id_user'];

// Ambil data hasil diagnosis
$query = "SELECT hd.*, 
          p1.nama_penyakit as ds_penyakit,
          p1.deskripsi as ds_deskripsi,
          p2.nama_penyakit as cbr_penyakit,
          p2.deskripsi as cbr_deskripsi
          FROM hasil_diagnosis hd
          LEFT JOIN penyakit p1 ON hd.ds_penyakit_id = p1.id_penyakit
          LEFT JOIN penyakit p2 ON hd.cbr_kasus_id = p2.id_penyakit
          WHERE hd.id_diagnosis = ? AND hd.id_user = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id_diagnosis, $id_user);
$stmt->execute();
$hasil = $stmt->get_result()->fetch_assoc();

if (!$hasil) {
    header("Location: index.php");
    exit;
}

// Ambil solusi untuk penyakit
$query_solusi = "SELECT s.* 
                 FROM solusi s
                 JOIN penyakit_solusi ps ON s.id_solusi = ps.id_solusi
                 WHERE ps.id_penyakit = ?
                 ORDER BY s.kode_solusi ASC";
$stmt = $conn->prepare($query_solusi);
$stmt->bind_param("i", $hasil['ds_penyakit_id']);
$stmt->execute();
$solusi_ds = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt->bind_param("i", $hasil['cbr_kasus_id']);
$stmt->execute();
$solusi_cbr = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Diagnosis - Sistem Pakar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50">
    <?php renderUserSidebar('diagnosis'); ?>

    <div class="p-4 sm:ml-64">
        <div class="p-4 border-2 border-gray-200 rounded-lg">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold">Hasil Diagnosis</h1>
                <div class="text-sm text-gray-600">
                    ID: #<?= str_pad($hasil['id_diagnosis'], 5, '0', STR_PAD_LEFT) ?> | 
                    <?= date('d F Y H:i', strtotime($hasil['tanggal_diagnosis'])) ?>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Dempster-Shafer Card -->
                <div class="bg-white p-6 rounded-lg shadow-lg border border-blue-100">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-blue-800">Hasil Dempster-Shafer</h2>
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                            <?= number_format($hasil['ds_nilai_kepercayaan'] * 100, 2) ?>% Kepercayaan
                        </span>
                    </div>
                    
                    <!-- Progress Bar DS -->
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4">
                        <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" 
                             style="width: <?= number_format($hasil['ds_nilai_kepercayaan'] * 100, 2) ?>%">
                        </div>
                    </div>

                    <h3 class="text-xl font-bold text-gray-800 mb-2">
                        <?= htmlspecialchars($hasil['ds_penyakit']) ?>
                    </h3>
                    <div class="text-gray-600 mb-4">
                        <?= nl2br(htmlspecialchars($hasil['ds_deskripsi'])) ?>
                    </div>
                    
                    <!-- Tingkat Keparahan DS -->
                    <?php
                    $severity = '';
                    $severity_color = '';
                    $nilai_ds = $hasil['ds_nilai_kepercayaan'];
                    
                    if ($nilai_ds >= 0.8) {
                        $severity = 'Sangat Tinggi';
                        $severity_color = 'red';
                    } elseif ($nilai_ds >= 0.6) {
                        $severity = 'Tinggi';
                        $severity_color = 'orange';
                    } elseif ($nilai_ds >= 0.4) {
                        $severity = 'Sedang';
                        $severity_color = 'yellow';
                    } else {
                        $severity = 'Rendah';
                        $severity_color = 'green';
                    }
                    ?>
                    <div class="flex items-center mt-4">
                        <span class="text-sm font-medium text-gray-700 mr-2">Tingkat Keparahan:</span>
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                            <?= "bg-{$severity_color}-100 text-{$severity_color}-800" ?>">
                            <?= $severity ?>
                        </span>
                    </div>
                </div>

                <!-- CBR Card -->
                <div class="bg-white p-6 rounded-lg shadow-lg border border-green-100">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-green-800">Hasil Case-Based Reasoning</h2>
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                            <?= number_format($hasil['cbr_similarity'] * 100, 2) ?>% Kemiripan
                        </span>
                    </div>

                    <!-- Progress Bar CBR -->
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4">
                        <div class="bg-green-600 h-2.5 rounded-full transition-all duration-500" 
                             style="width: <?= number_format($hasil['cbr_similarity'] * 100, 2) ?>%">
                        </div>
                    </div>

                    <h3 class="text-xl font-bold text-gray-800 mb-2">
                        <?= htmlspecialchars($hasil['cbr_penyakit']) ?>
                    </h3>
                    <div class="text-gray-600 mb-4">
                        <?= nl2br(htmlspecialchars($hasil['cbr_deskripsi'])) ?>
                    </div>

                    <!-- Tingkat Keparahan CBR -->
                    <?php
                    $cbr_severity = '';
                    $cbr_severity_color = '';
                    $nilai_cbr = $hasil['cbr_similarity'];
                    
                    if ($nilai_cbr >= 0.8) {
                        $cbr_severity = 'Sangat Tinggi';
                        $cbr_severity_color = 'red';
                    } elseif ($nilai_cbr >= 0.6) {
                        $cbr_severity = 'Tinggi';
                        $cbr_severity_color = 'orange';
                    } elseif ($nilai_cbr >= 0.4) {
                        $cbr_severity = 'Sedang';
                        $cbr_severity_color = 'yellow';
                    } else {
                        $cbr_severity = 'Rendah';
                        $cbr_severity_color = 'green';
                    }
                    ?>
                    <div class="flex items-center mt-4">
                        <span class="text-sm font-medium text-gray-700 mr-2">Tingkat Keparahan:</span>
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                            <?= "bg-{$cbr_severity_color}-100 text-{$cbr_severity_color}-800" ?>">
                            <?= $cbr_severity ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Detail Perhitungan Sections -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- DS Details -->
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <h2 class="text-lg font-semibold mb-4">Detail Perhitungan Dempster-Shafer</h2>
                    <?php 
                    $ds_detail = json_decode($hasil['ds_detail_perhitungan'], true);
                    if ($ds_detail && is_array($ds_detail)): 
                    ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                            Penyakit/Theta
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                                            Nilai Belief
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($ds_detail as $key => $nilai): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3">
                                                <?php if ($key === 'theta'): ?>
                                                    <span class="text-gray-600">Theta (Ketidakpastian)</span>
                                                <?php else:
                                                    $query = "SELECT nama_penyakit FROM penyakit WHERE id_penyakit = ?";
                                                    $stmt = $conn->prepare($query);
                                                    $stmt->bind_param("i", $key);
                                                    $stmt->execute();
                                                    $result = $stmt->get_result();
                                                    $nama_penyakit = $result->fetch_assoc();
                                                    echo htmlspecialchars($nama_penyakit ? $nama_penyakit['nama_penyakit'] : 'Penyakit #' . $key);
                                                endif; ?>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <span class="font-medium"><?= number_format($nilai * 100, 2) ?>%</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 italic">Tidak ada detail perhitungan yang tersedia.</p>
                    <?php endif; ?>
                </div>

                <!-- CBR Details -->
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <h2 class="text-lg font-semibold mb-4">Detail Perhitungan Case-Based Reasoning</h2>
                    <?php 
                    $cbr_detail = json_decode($hasil['cbr_detail_perhitungan'], true);
                    if ($cbr_detail): 
                    ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kasus</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Penyakit</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Similarity</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($cbr_detail as $kasus_id => $detail): 
                                        $query = "SELECT k.kode_kasus, p.nama_penyakit 
                                                 FROM kasus k 
                                                 JOIN penyakit p ON k.id_penyakit = p.id_penyakit 
                                                 WHERE k.id_kasus = ?";
                                        $stmt = $conn->prepare($query);
                                        $stmt->bind_param("i", $kasus_id);
                                        $stmt->execute();
                                        $kasus_data = $stmt->get_result()->fetch_assoc();
                                    ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3"><?= htmlspecialchars($kasus_data['kode_kasus']) ?></td>
                                            <td class="px-4 py-3"><?= htmlspecialchars($kasus_data['nama_penyakit']) ?></td>
                                            <td class="px-4 py-3 text-right">
                                                <span class="font-medium"><?= number_format($detail['similarity'] * 100, 2) ?>%</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Rekomendasi dan Feedback Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Rekomendasi Penanganan -->
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <h2 class="text-lg font-semibold mb-4">Rekomendasi Penanganan</h2>
                    <div class="space-y-4">
                        <div class="p-4 bg-yellow-50 rounded-lg">
                            <p class="text-yellow-800 text-sm">
                                <span class="font-medium">Catatan:</span> Rekomendasi ini bersifat umum dan sebaiknya 
                                dikonsultasikan dengan ahli pertanian untuk penanganan yang lebih spesifik.
                            </p>
                        </div>
                        
                        <div class="space-y-3">
                            <h3 class="font-medium text-gray-800">Tindakan Preventif:</h3>
                            <ul class="list-disc list-inside text-gray-600 space-y-2">
                                <li>Menjaga kebersihan area penanaman</li>
                                <li>Memastikan drainase yang baik</li>
                                <li>Melakukan rotasi tanaman</li>
                                <li>Menggunakan bibit yang sehat</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Feedback Form -->
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <h2 class="text-lg font-semibold mb-4">Feedback Diagnosis</h2>
                    <form action="feedback.php" method="POST" class="space-y-4" id="feedbackForm">
                        <input type="hidden" name="id_diagnosis" value="<?= $id_diagnosis ?>">
                        
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">
                                Apakah hasil diagnosis ini membantu?
                            </label>
                            <textarea name="feedback_user" 
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                    rows="3"
                                    placeholder="Berikan feedback Anda tentang hasil diagnosis ini..."
                                    required><?= htmlspecialchars($hasil['feedback_user'] ?? '') ?></textarea>
                        </div>

                        <div class="flex justify-end space-x-4">
                            <a href="index.php" 
                               class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                                Diagnosis Baru
                            </a>
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                                Kirim Feedback
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Status Validasi -->
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Status Validasi</h2>
                    <span class="px-4 py-2 rounded-full text-sm font-medium
                        <?php
                        switch($hasil['status_validasi']) {
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
                        <?= ucfirst($hasil['status_validasi']) ?>
                    </span>
                </div>
                <?php if ($hasil['status_validasi'] === 'pending'): ?>
                    <p class="text-gray-600 mt-2">
                        Hasil diagnosis Anda sedang menunggu validasi dari pakar. 
                        Silakan cek kembali nanti untuk melihat status terbaru.
                    </p>
                <?php elseif ($hasil['status_validasi'] === 'invalid'): ?>
                    <div class="mt-4 p-4 bg-red-50 rounded-lg">
                        <p class="text-red-800">
                            <span class="font-medium">Catatan dari Pakar:</span><br>
                            <?= nl2br(htmlspecialchars($hasil['catatan_pakar'] ?? 'Tidak ada catatan')) ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Handle feedback form submission
        document.getElementById('feedbackForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const feedback = this.querySelector('textarea[name="feedback_user"]').value.trim();
            
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