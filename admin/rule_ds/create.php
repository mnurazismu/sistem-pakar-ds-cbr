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

// Ambil data gejala dan penyakit untuk dropdown
$query_gejala = "SELECT id_gejala, kode_gejala, nama_gejala, belief_value 
                 FROM gejala 
                 ORDER BY kode_gejala ASC";
$result_gejala = mysqli_query($conn, $query_gejala);

$query_penyakit = "SELECT id_penyakit, kode_penyakit, nama_penyakit, tingkat_keparahan 
                   FROM penyakit 
                   ORDER BY kode_penyakit ASC";
$result_penyakit = mysqli_query($conn, $query_penyakit);

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_gejala = mysqli_real_escape_string($conn, $_POST['id_gejala']);
    $id_penyakit = mysqli_real_escape_string($conn, $_POST['id_penyakit']);
    $nilai_densitas = mysqli_real_escape_string($conn, $_POST['nilai_densitas']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

    // Validasi input
    $errors = [];

    // Cek apakah kombinasi gejala dan penyakit sudah ada
    $check_query = "SELECT id_rule FROM rule_ds 
                   WHERE id_gejala = '$id_gejala' AND id_penyakit = '$id_penyakit'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $errors[] = "Rule untuk gejala dan penyakit ini sudah ada!";
    }

    if ($nilai_densitas < 0 || $nilai_densitas > 1) {
        $errors[] = "Nilai densitas harus antara 0 dan 1";
    }

    if (empty($errors)) {
        $query = "INSERT INTO rule_ds (id_gejala, id_penyakit, nilai_densitas, keterangan) 
                  VALUES ('$id_gejala', '$id_penyakit', '$nilai_densitas', '$keterangan')";

        if (mysqli_query($conn, $query)) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Rule berhasil ditambahkan.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.href = 'index.php';
                });
            </script>";
        } else {
            $errors[] = "Terjadi kesalahan: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Rule DS - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-50">
    <?php renderAdminSidebar('rule_ds'); ?>

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
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-300 mx-2"></i>
                            <a href="index.php" class="text-gray-700 hover:text-blue-600">
                                Rule DS
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-300 mx-2"></i>
                            <span class="text-gray-500">Tambah</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white rounded-lg shadow-md p-6 md:p-8">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-800">Tambah Rule Dempster-Shafer</h2>
                        <p class="text-gray-600 mt-1">Tambahkan aturan baru untuk perhitungan Dempster-Shafer</p>
                    </div>
                    <a href="index.php" class="inline-flex items-center text-blue-600 hover:text-blue-700">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                </div>

                <?php if (!empty($errors)): ?>
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                        <h3 class="text-red-800 font-medium">Terdapat beberapa kesalahan:</h3>
                    </div>
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                        <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Gejala Section -->
                        <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                            <h3 class="font-medium text-gray-900 mb-2">
                                <i class="fas fa-file-medical text-blue-500 mr-2"></i>Pilih Gejala
                            </h3>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Gejala <span class="text-red-500">*</span>
                                </label>
                                <select name="id_gejala" required id="gejala"
                                    class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    <option value="">Pilih Gejala</option>
                                    <?php
                                    mysqli_data_seek($result_gejala, 0);
                                    while ($gejala = mysqli_fetch_assoc($result_gejala)):
                                        ?>
                                    <option value="<?= $gejala['id_gejala'] ?>"
                                        data-belief="<?= $gejala['belief_value'] ?>">
                                        <?= htmlspecialchars($gejala['kode_gejala'] . ' - ' . $gejala['nama_gejala']) ?>
                                        (Belief: <?= number_format($gejala['belief_value'], 2) ?>)
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Penyakit Section -->
                        <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                            <h3 class="font-medium text-gray-900 mb-2">
                                <i class="fas fa-virus text-red-500 mr-2"></i>Pilih Penyakit
                            </h3>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Penyakit <span class="text-red-500">*</span>
                                </label>
                                <select name="id_penyakit" required
                                    class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    <option value="">Pilih Penyakit</option>
                                    <?php
                                    mysqli_data_seek($result_penyakit, 0);
                                    while ($penyakit = mysqli_fetch_assoc($result_penyakit)):
                                        $severity_class = match ($penyakit['tingkat_keparahan']) {
                                            'Ringan' => 'text-green-600',
                                            'Sedang' => 'text-yellow-600',
                                            'Berat' => 'text-red-600',
                                            default => ''
                                        };
                                        ?>
                                    <option value="<?= $penyakit['id_penyakit'] ?>" class="<?= $severity_class ?>">
                                        <?= htmlspecialchars($penyakit['kode_penyakit'] . ' - ' . $penyakit['nama_penyakit']) ?>
                                        (<?= $penyakit['tingkat_keparahan'] ?>)
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Nilai Densitas Section -->
                        <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                            <h3 class="font-medium text-gray-900 mb-2">
                                <i class="fas fa-chart-line text-green-500 mr-2"></i>Nilai Densitas
                            </h3>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Nilai Densitas <span class="text-red-500">*</span>
                                </label>
                                <div class="flex items-center space-x-4">
                                    <div class="relative flex-1">
                                        <div
                                            class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                            <i class="fas fa-percentage text-gray-400"></i>
                                        </div>
                                        <input type="number" name="nilai_densitas" required step="0.01" min="0" max="1"
                                            class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5"
                                            placeholder="Masukkan nilai densitas (0-1)">
                                    </div>
                                    <span class="text-sm text-gray-500 whitespace-nowrap">
                                        Belief gejala: <span id="beliefValue" class="font-medium">-</span>
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Nilai antara 0 dan 1</p>
                            </div>
                        </div>

                        <!-- Keterangan Section -->
                        <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                            <h3 class="font-medium text-gray-900 mb-2">
                                <i class="fas fa-sticky-note text-purple-500 mr-2"></i>Keterangan
                            </h3>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">
                                    Keterangan Tambahan
                                </label>
                                <textarea name="keterangan" rows="3"
                                    class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder="Tambahkan keterangan atau catatan (opsional)"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-6">
                        <a href="index.php"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:ring-2 focus:ring-offset-2 focus:ring-gray-200">
                            <i class="fas fa-times mr-2"></i>
                            Batal
                        </a>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-save mr-2"></i>
                            Simpan Rule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Update belief value when gejala is selected
    document.getElementById('gejala').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const beliefValue = selectedOption.getAttribute('data-belief');
        document.getElementById('beliefValue').textContent = beliefValue ? Number(beliefValue).toFixed(2) : '-';
    });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>

</html>