<?php
require_once '../../config/database.php';
require_once '../../auth/functions.php';

// Cek login
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

$id_diagnosis = $_POST['id_diagnosis'] ?? 0;
$feedback_user = $_POST['feedback_user'] ?? '';
$id_user = $_SESSION['id_user'];

try {
    // Update feedback user
    $query = "UPDATE hasil_diagnosis 
              SET feedback_user = ?
              WHERE id_diagnosis = ? AND id_user = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sii", $feedback_user, $id_diagnosis, $id_user);
    
    if ($stmt->execute()) {
        $message = [
            'type' => 'success',
            'message' => 'Terima kasih atas feedback Anda!'
        ];
    } else {
        throw new Exception("Gagal menyimpan feedback");
    }

} catch (Exception $e) {
    $message = [
        'type' => 'error',
        'message' => 'Terjadi kesalahan saat menyimpan feedback: ' . $e->getMessage()
    ];
}

// Tampilkan SweetAlert sebelum redirect
?>
<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        window.onload = function() {
            Swal.fire({
                icon: '<?= $message['type'] ?>',
                title: '<?= $message['message'] ?>',
                showConfirmButton: false,
                timer: 2000
            }).then(function() {
                window.location.href = 'result.php?id=<?= $id_diagnosis ?>';
            });
        }
    </script>
</body>
</html>