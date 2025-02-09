<?php
require_once '../../config/database.php';
require_once '../../auth/functions.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    header("Location: ../../auth/login.php");
    exit;
} elseif (!isUser()) {
    header("Location: ../../admin/dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$id_diagnosis = $_POST['id_diagnosis'] ?? null;
$feedback = $_POST['feedback'] ?? null;

if (!$id_diagnosis || !$feedback) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Data feedback tidak lengkap!'
    ];
    header("Location: index.php");
    exit;
}

try {
    // Update feedback user
    $query = "UPDATE hasil_diagnosis 
              SET feedback_user = ?, 
                  status_validasi = 'pending'
              WHERE id_diagnosis = ? 
              AND id_user = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sii", $feedback, $id_diagnosis, $_SESSION['id_user']);
    
    if ($stmt->execute()) {
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => 'Feedback berhasil dikirim!'
        ];
    } else {
        throw new Exception("Gagal menyimpan feedback");
    }

} catch (Exception $e) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ];
}

// Redirect dengan JavaScript untuk memastikan SweetAlert muncul
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.onload = function() {
        Swal.fire({
            icon: '<?= $_SESSION['flash_message']['type'] ?>',
            title: '<?= $_SESSION['flash_message']['message'] ?>',
            showConfirmButton: false,
            timer: 2000
        }).then(function() {
            window.location.href = 'index.php';
        });
    }
</script>