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

// Ambil semua pertanyaan yang aktif
$query_pertanyaan = "SELECT * FROM pertanyaan 
                    WHERE status_aktif = 1 
                    ORDER BY urutan ASC";
$result_pertanyaan = $conn->query($query_pertanyaan);
$pertanyaan = $result_pertanyaan->fetch_all(MYSQLI_ASSOC);

// Hitung total langkah dari pertanyaan yang aktif
$total_steps = count($pertanyaan);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnosis Penyakit - Sistem Pakar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Progress bar animation -->
    <style>
    .step-animation {
        transition: all 0.3s ease-in-out;
    }

    .question-card {
        transition: all 0.5s ease-in-out;
    }

    .question-card.active {
        transform: translateX(0);
        opacity: 1;
    }

    .question-card.inactive {
        display: none;
    }
    </style>
</head>

<body class="bg-gray-50">
    <?php renderUserSidebar('diagnosis'); ?>

    <div class="p-4 sm:ml-64">
        <div class="p-4 border-2 border-gray-200 rounded-lg">
            <!-- Header Section -->
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Diagnosis Penyakit Tanaman Cabai</h1>
                <p class="text-gray-600 mt-2">
                    Silakan jawab pertanyaan berikut untuk mendiagnosis penyakit pada tanaman cabai Anda.
                </p>
            </div>

            <!-- Progress Indicator -->
            <div class="mb-8">
                <div class="flex justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">
                        Progress Diagnosis
                    </span>
                    <span class="text-sm font-medium text-blue-600" id="progressText">
                        Langkah <span id="currentStep">1</span> dari <?= $total_steps ?>
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-blue-600 h-2.5 rounded-full step-animation" id="progressBar"
                        style="width: <?= (1 / $total_steps) * 100 ?>%">
                    </div>
                </div>
            </div>

            <!-- Flash Message -->
            <?php if (isset($_SESSION['flash_message'])): ?>
            <div
                class="mb-4 p-4 rounded-lg <?= $_SESSION['flash_message']['type'] === 'error' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>">
                <p class="font-bold"><?= $_SESSION['flash_message']['message'] ?></p>

                <?php if (isset($_SESSION['flash_message']['detail']) && $_SESSION['flash_message']['type'] === 'error'): ?>
                <div class="mt-2 text-sm">
                    <p>Error Code: <?= $_SESSION['flash_message']['detail']['error_code'] ?></p>
                    <p>File: <?= $_SESSION['flash_message']['detail']['file'] ?></p>
                    <p>Line: <?= $_SESSION['flash_message']['detail']['line'] ?></p>
                    <?php if (isset($_SESSION['flash_message']['detail']['sql_error'])): ?>
                    <p>SQL Error: <?= $_SESSION['flash_message']['detail']['sql_error'] ?></p>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['flash_message']['detail']['conn_error'])): ?>
                    <p>Connection Error: <?= $_SESSION['flash_message']['detail']['conn_error'] ?></p>
                    <?php endif; ?>
                    <details>
                        <summary>Stack Trace</summary>
                        <pre class="mt-2 whitespace-pre-wrap"><?= $_SESSION['flash_message']['detail']['trace'] ?></pre>
                    </details>
                </div>
                <?php endif; ?>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
            <?php endif; ?>

            <!-- Diagnosis Form -->
            <form id="diagnosisForm" action="process.php" method="POST" class="space-y-6">
                <?php foreach ($pertanyaan as $index => $p): ?>
                <div class="question-card <?= $index === 0 ? 'active' : 'inactive' ?>" data-step="<?= $index + 1 ?>">
                    <div class="bg-white p-6 rounded-lg shadow-lg border border-gray-100">
                        <div class="flex items-center mb-4">
                            <span
                                class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800 font-semibold mr-3">
                                <?= $index + 1 ?>
                            </span>
                            <h2 class="text-lg font-semibold text-gray-800">
                                <?= htmlspecialchars($p['isi_pertanyaan']) ?>
                            </h2>
                        </div>

                        <?php
                            // Ambil pilihan jawaban
                            $query_pilihan = "SELECT * FROM pilihan_jawaban WHERE id_pertanyaan = ? ORDER BY urutan ASC";
                            $stmt = $conn->prepare($query_pilihan);
                            $stmt->bind_param("i", $p['id_pertanyaan']);
                            $stmt->execute();
                            $pilihan = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                            ?>

                        <div class="space-y-3 pl-11">
                            <?php if ($p['jenis_input'] === 'radio'): ?>
                            <?php foreach ($pilihan as $pil): ?>
                            <div class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                <input type="radio" name="jawaban[<?= $p['id_pertanyaan'] ?>]"
                                    value="<?= $pil['id_pilihan'] ?>" id="pilihan_<?= $pil['id_pilihan'] ?>"
                                    class="w-4 h-4 text-blue-600 focus:ring-blue-500 cursor-pointer" required>
                                <label for="pilihan_<?= $pil['id_pilihan'] ?>"
                                    class="ml-3 text-gray-700 cursor-pointer flex-1">
                                    <?= htmlspecialchars($pil['isi_pilihan']) ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="flex justify-between mt-6 pt-4 border-t">
                            <?php if ($index > 0): ?>
                            <button type="button"
                                class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none"
                                onclick="prevStep(<?= $index + 1 ?>)">
                                ← Sebelumnya
                            </button>
                            <?php else: ?>
                            <div></div>
                            <?php endif; ?>

                            <?php if ($index < $total_steps - 1): ?>
                            <button type="button"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                onclick="nextStep(<?= $index + 1 ?>)">
                                Selanjutnya →
                            </button>
                            <?php else: ?>
                            <button type="submit"
                                class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                Proses Diagnosis
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </form>

            <!-- Informasi Bantuan -->
            <div class="mt-8 bg-white p-6 rounded-lg shadow-lg border border-gray-100">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Petunjuk Pengisian</h3>
                <div class="prose max-w-none text-gray-600">
                    <ul class="list-disc pl-5 space-y-2">
                        <li>Pilih gejala yang sesuai dengan kondisi tanaman cabai Anda</li>
                        <li>Pastikan untuk menjawab semua pertanyaan yang ada</li>
                        <li>Jika ragu, Anda dapat kembali ke pertanyaan sebelumnya</li>
                        <li>Hasil diagnosis akan menggunakan dua metode: Dempster-Shafer dan CBR</li>
                        <li>Hasil diagnosis akan divalidasi oleh pakar untuk memastikan akurasi</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="../../src/jquery-3.6.3.min.js"></script>
    <script src="../../src/flowbite.min.js"></script>
    <script>
    const totalSteps = <?= $total_steps ?>;

    function updateProgress(step) {
        const progress = (step / totalSteps) * 100;
        document.getElementById('progressBar').style.width = `${progress}%`;
        document.getElementById('currentStep').textContent = step;
    }

    function showStep(step) {
        document.querySelectorAll('.question-card').forEach(card => {
            if (parseInt(card.dataset.step) === step) {
                card.classList.remove('inactive');
                card.classList.add('active');
            } else {
                card.classList.remove('active');
                card.classList.add('inactive');
            }
        });
        updateProgress(step);
    }

    function nextStep(currentStep) {
        // Validate current step
        const currentCard = document.querySelector(`[data-step="${currentStep}"]`);
        const radios = currentCard.querySelectorAll('input[type="radio"]');
        let isValid = false;
        radios.forEach(radio => {
            if (radio.checked) isValid = true;
        });

        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Mohon pilih salah satu jawaban sebelum melanjutkan'
            });
            return;
        }

        showStep(currentStep + 1);
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    function prevStep(currentStep) {
        showStep(currentStep - 1);
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    // Form submission handler
    document.getElementById('diagnosisForm').onsubmit = function(e) {
        e.preventDefault();

        // Final validation
        let isValid = true;
        let message = '';

        const radios = document.querySelectorAll('input[type="radio"]');
        let radioGroups = {};

        radios.forEach(radio => {
            let name = radio.name;
            if (!radioGroups[name]) radioGroups[name] = false;
            if (radio.checked) radioGroups[name] = true;
        });

        for (let group in radioGroups) {
            if (!radioGroups[group]) {
                isValid = false;
                message = 'Mohon lengkapi semua pertanyaan sebelum mengirim diagnosis.';
                break;
            }
        }

        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: message
            });
            return false;
        }

        // Konfirmasi sebelum submit
        Swal.fire({
            title: 'Konfirmasi Diagnosis',
            text: 'Apakah Anda yakin dengan jawaban yang diberikan?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Proses',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        });
    };
    </script>
</body>

</html>