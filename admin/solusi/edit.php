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

// Ambil ID solusi dari parameter URL
$id_solusi = isset($_GET['id']) ? $_GET['id'] : null;
if (!$id_solusi) {
    header("Location: index.php");
    exit;
}

// Ambil data solusi yang akan diedit
$query_solusi = "SELECT * FROM solusi WHERE id_solusi = '$id_solusi'";
$result_solusi = mysqli_query($conn, $query_solusi);
$solusi = mysqli_fetch_assoc($result_solusi);

if (!$solusi) {
    header("Location: index.php");
    exit;
}

// Ambil data penyakit untuk dropdown
$query_penyakit = "SELECT * FROM penyakit ORDER BY kode_penyakit ASC";
$result_penyakit = mysqli_query($conn, $query_penyakit);
$penyakit_list = [];
while ($row = mysqli_fetch_assoc($result_penyakit)) {
    $penyakit_list[] = $row;
}

// Ambil data penyakit yang terkait dengan solusi ini
$query_penyakit_terkait = "SELECT * FROM penyakit_solusi WHERE id_solusi = '$id_solusi' ORDER BY urutan_solusi ASC";
$result_penyakit_terkait = mysqli_query($conn, $query_penyakit_terkait);
$penyakit_terkait = [];
while ($row = mysqli_fetch_assoc($result_penyakit_terkait)) {
    $penyakit_terkait[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_solusi = mysqli_real_escape_string($conn, $_POST['kode_solusi']);
    $nama_solusi = mysqli_real_escape_string($conn, $_POST['nama_solusi']);
    $deskripsi_solusi = mysqli_real_escape_string($conn, $_POST['deskripsi_solusi']);
    $kategori_solusi = mysqli_real_escape_string($conn, $_POST['kategori_solusi']);
    $penyakit_baru = isset($_POST['penyakit']) ? $_POST['penyakit'] : [];
    $urutan_baru = isset($_POST['urutan']) ? $_POST['urutan'] : [];

    // Validasi input
    $errors = [];
    
    // Cek kode solusi unik (kecuali untuk solusi yang sedang diedit)
    $check_query = "SELECT kode_solusi FROM solusi WHERE kode_solusi = '$kode_solusi' AND id_solusi != '$id_solusi'";
    $check_result = mysqli_query($conn, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        $errors[] = "Kode solusi sudah digunakan!";
    }

    if (empty($kode_solusi) || empty($nama_solusi) || empty($deskripsi_solusi)) {
        $errors[] = "Semua field wajib diisi!";
    }

    if (empty($errors)) {
        mysqli_begin_transaction($conn);
        try {
            // Update tabel solusi
            $query = "UPDATE solusi SET 
                     kode_solusi = '$kode_solusi',
                     nama_solusi = '$nama_solusi',
                     deskripsi_solusi = '$deskripsi_solusi',
                     kategori_solusi = '$kategori_solusi'
                     WHERE id_solusi = '$id_solusi'";
            mysqli_query($conn, $query);

            // Hapus relasi penyakit_solusi yang lama
            $query_delete = "DELETE FROM penyakit_solusi WHERE id_solusi = '$id_solusi'";
            mysqli_query($conn, $query_delete);

            // Insert relasi penyakit_solusi yang baru
            foreach ($penyakit_baru as $index => $id_penyakit) {
                $urutan = $urutan_baru[$index];
                $query_relasi = "INSERT INTO penyakit_solusi (id_penyakit, id_solusi, urutan_solusi) 
                                VALUES ('$id_penyakit', '$id_solusi', '$urutan')";
                mysqli_query($conn, $query_relasi);
            }

            mysqli_commit($conn);
            echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Solusi berhasil diperbarui.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function() {
                        window.location.href = 'index.php';
                    });
                  </script>";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $errors[] = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Solusi - Sistem Pakar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/sweetalert2.all.min.js"></script>
</head>
<body class="bg-gray-50">
    <?php renderAdminSidebar('solusi'); ?>
    
    <!-- Main Content -->
    <div class="p-4 sm:ml-64">
        <div class="p-4">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Edit Solusi</h2>
                    <a href="index.php" class="text-blue-500 hover:text-blue-600">
                        Kembali ke Daftar Solusi
                    </a>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                        <ul class="list-disc list-inside">
                            <?php foreach ($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">Kode Solusi</label>
                        <input type="text" name="kode_solusi" required
                               value="<?= htmlspecialchars($solusi['kode_solusi']) ?>"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                               placeholder="Masukkan kode solusi">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">Nama Solusi</label>
                        <input type="text" name="nama_solusi" required
                               value="<?= htmlspecialchars($solusi['nama_solusi']) ?>"
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                               placeholder="Masukkan nama solusi">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">Deskripsi Solusi</label>
                        <textarea name="deskripsi_solusi" required rows="4"
                                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                  placeholder="Masukkan deskripsi solusi"><?= htmlspecialchars($solusi['deskripsi_solusi']) ?></textarea>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">Kategori Solusi</label>
                        <select name="kategori_solusi" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            <option value="">Pilih Kategori</option>
                            <option value="Pencegahan" <?= $solusi['kategori_solusi'] == 'Pencegahan' ? 'selected' : '' ?>>Pencegahan</option>
                            <option value="Pengobatan" <?= $solusi['kategori_solusi'] == 'Pengobatan' ? 'selected' : '' ?>>Pengobatan</option>
                            <option value="Perawatan" <?= $solusi['kategori_solusi'] == 'Perawatan' ? 'selected' : '' ?>>Perawatan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">Penyakit Terkait</label>
                        <div id="penyakitContainer" class="space-y-4">
                            <?php foreach ($penyakit_terkait as $index => $pt): ?>
                            <div class="flex gap-4">
                                <select name="penyakit[]" required
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    <option value="">Pilih Penyakit</option>
                                    <?php foreach ($penyakit_list as $p): ?>
                                        <option value="<?= $p['id_penyakit'] ?>" <?= $p['id_penyakit'] == $pt['id_penyakit'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['kode_penyakit'] . ' - ' . $p['nama_penyakit']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="number" name="urutan[]" required min="1"
                                       value="<?= $pt['urutan_solusi'] ?>"
                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-32 p-2.5"
                                       placeholder="Urutan">
                                <?php if ($index === 0): ?>
                                <button type="button" onclick="tambahPenyakit()"
                                        class="text-white bg-blue-500 hover:bg-blue-600 font-medium rounded-lg text-sm px-4 py-2">
                                    +
                                </button>
                                <?php else: ?>
                                <button type="button" onclick="this.parentElement.remove()"
                                        class="text-white bg-red-500 hover:bg-red-600 font-medium rounded-lg text-sm px-4 py-2">
                                    -
                                </button>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                            
                            <?php if (empty($penyakit_terkait)): ?>
                            <div class="flex gap-4">
                                <select name="penyakit[]" required
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    <option value="">Pilih Penyakit</option>
                                    <?php foreach ($penyakit_list as $p): ?>
                                        <option value="<?= $p['id_penyakit'] ?>">
                                            <?= htmlspecialchars($p['kode_penyakit'] . ' - ' . $p['nama_penyakit']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="number" name="urutan[]" required min="1"
                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-32 p-2.5"
                                       placeholder="Urutan">
                                <button type="button" onclick="tambahPenyakit()"
                                        class="text-white bg-blue-500 hover:bg-blue-600 font-medium rounded-lg text-sm px-4 py-2">
                                    +
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="index.php" 
                           class="text-gray-500 bg-gray-200 hover:bg-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            Batal
                        </a>
                        <button type="submit"
                                class="text-white bg-blue-500 hover:bg-blue-600 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function tambahPenyakit() {
        const container = document.getElementById('penyakitContainer');
        const newRow = container.children[0].cloneNode(true);
        
        // Reset values
        newRow.querySelector('select').value = '';
        newRow.querySelector('input[type="number"]').value = '';
        
        // Add remove button for new rows
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'text-white bg-red-500 hover:bg-red-600 font-medium rounded-lg text-sm px-4 py-2';
        removeBtn.textContent = '-';
        removeBtn.onclick = function() {
            container.removeChild(newRow);
        };
        
        // Replace the add button with remove button
        const addBtn = newRow.querySelector('button');
        addBtn.parentNode.replaceChild(removeBtn, addBtn);
        
        container.appendChild(newRow);
    }
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
</body>
</html>