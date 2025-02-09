<?php
require_once '../../config/database.php';
require_once '../../auth/functions.php';
require_once 'process_cbr.php';

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

// Fungsi untuk menghitung Dempster-Shafer
function calculateDempsterShafer($conn, $jawaban)
{
    // Array untuk menyimpan mass function dari setiap gejala
    $mass_functions = [];

    // 1. Hitung mass function untuk setiap gejala yang dipilih
    foreach ($jawaban as $id_pertanyaan => $id_pilihan) {
        // Verifikasi status aktif pertanyaan
        $query_check = "SELECT status_aktif FROM pertanyaan WHERE id_pertanyaan = ?";
        $stmt = $conn->prepare($query_check);
        $stmt->bind_param("i", $id_pertanyaan);
        $stmt->execute();
        $result = $stmt->get_result();
        $pertanyaan = $result->fetch_assoc();

        // Hanya proses jika pertanyaan aktif
        if ($pertanyaan && $pertanyaan['status_aktif'] == 1) {
            // Ambil data gejala dan bobot pilihan
            $query = "SELECT pj.bobot_nilai, p.id_gejala, g.belief_value 
                     FROM pilihan_jawaban pj 
                     JOIN pertanyaan p ON p.id_pertanyaan = pj.id_pertanyaan 
                     JOIN gejala g ON g.id_gejala = p.id_gejala 
                     WHERE pj.id_pilihan = ? AND p.id_pertanyaan = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $id_pilihan, $id_pertanyaan);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if (!$result)
                continue;

            // Ambil penyakit yang terkait dengan gejala ini
            $query = "SELECT id_penyakit, nilai_densitas 
                     FROM rule_ds 
                     WHERE id_gejala = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $result['id_gejala']);
            $stmt->execute();
            $rules = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // Hitung mass function
            $belief = $result['belief_value'] * $result['bobot_nilai'];

            // Debug: Print nilai belief untuk setiap gejala
            error_log("Gejala ID: " . $result['id_gejala'] .
                ", Belief Value: " . $result['belief_value'] .
                ", Bobot Nilai: " . $result['bobot_nilai'] .
                ", Final Belief: " . $belief);

            // Buat focal element untuk gejala ini
            $focal_elements = [];
            $total_densitas = 0;

            foreach ($rules as $rule) {
                $m_value = $belief * $rule['nilai_densitas'];
                $focal_elements[$rule['id_penyakit']] = $m_value;
                $total_densitas += $m_value;

                // Debug: Print nilai densitas untuk setiap rule
                error_log("Rule untuk Penyakit ID: " . $rule['id_penyakit'] .
                    ", Nilai Densitas: " . $rule['nilai_densitas'] .
                    ", M-Value: " . $m_value);
            }

            // Tambahkan nilai theta (ketidakpastian)
            $focal_elements['theta'] = 1 - $total_densitas;

            // Debug: Print focal elements untuk gejala ini
            error_log("Focal Elements untuk Gejala " . $result['id_gejala'] . ": " .
                print_r($focal_elements, true));

            // Tambahkan ke array mass functions jika ada focal elements
            if (!empty($focal_elements)) {
                $mass_functions[] = $focal_elements;
            }
        }
    }

    // Debug: Print semua mass functions sebelum kombinasi
    error_log("All Mass Functions before combination: " . print_r($mass_functions, true));

    // 2. Kombinasikan semua mass function menggunakan Dempster's Rule of Combination
    $combined_mass = null;
    foreach ($mass_functions as $index => $mass) {
        if ($combined_mass === null) {
            $combined_mass = $mass;
            continue;
        }

        // Debug: Print state sebelum kombinasi
        error_log("Combining step " . $index . ":");
        error_log("Current combined mass: " . print_r($combined_mass, true));
        error_log("Mass to combine with: " . print_r($mass, true));

        // Hitung matriks kombinasi
        $new_mass = [];
        $conflict = 0;

        // Kombinasikan setiap focal element
        foreach ($combined_mass as $k1 => $m1) {
            foreach ($mass as $k2 => $m2) {
                if ($k1 === 'theta' || $k2 === 'theta') {
                    // Jika salah satu adalah theta, intersection adalah focal element lainnya
                    $key = ($k1 === 'theta') ? $k2 : $k1;
                } else {
                    // Jika keduanya adalah penyakit yang sama
                    if ($k1 === $k2) {
                        $key = $k1;
                    } else {
                        // Jika berbeda penyakit, terjadi konflik
                        $conflict += $m1 * $m2;
                        continue;
                    }
                }

                if (!isset($new_mass[$key])) {
                    $new_mass[$key] = 0;
                }
                $new_mass[$key] += $m1 * $m2;
            }
        }

        // Debug: Print conflict dan new mass sebelum normalisasi
        error_log("Conflict (K): " . $conflict);
        error_log("New mass before normalization: " . print_r($new_mass, true));

        // Normalisasi dengan (1 - K)
        if ($conflict < 1) {
            foreach ($new_mass as $key => $value) {
                $new_mass[$key] = $value / (1 - $conflict);
            }
        }

        // Debug: Print hasil setelah normalisasi
        error_log("New mass after normalization: " . print_r($new_mass, true));

        $combined_mass = $new_mass;
    }

    // Debug: Print mass functions dan kombinasi
    error_log("Final Combined Mass: " . print_r($combined_mass, true));

    // 3. Tentukan penyakit dengan nilai kepercayaan tertinggi
    $max_belief = 0;
    $selected_penyakit = null;

    if ($combined_mass) {
        foreach ($combined_mass as $penyakit_id => $belief) {
            if ($penyakit_id !== 'theta' && $belief > $max_belief) {
                $max_belief = $belief;
                $selected_penyakit = $penyakit_id;
            }
        }
    }

    return [
        'penyakit_id' => $selected_penyakit,
        'nilai_kepercayaan' => $max_belief,
        'detail_perhitungan' => $combined_mass
    ];
}

// Ambil jawaban user
$jawaban = $_POST['jawaban'] ?? [];
if (empty($jawaban)) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Mohon isi semua pertanyaan yang ada!'
    ];
    header("Location: index.php");
    exit;
}

try {
    // Mulai transaction
    $conn->begin_transaction();

    // 1. Simpan hasil diagnosis awal dengan nilai default
    $id_user = $_SESSION['id_user'];
    $tanggal_diagnosis = date('Y-m-d H:i:s');
    $status_validasi = 'pending';

    $query = "INSERT INTO hasil_diagnosis (
                id_user, 
                tanggal_diagnosis,
                ds_penyakit_id,
                ds_nilai_kepercayaan,
                ds_detail_perhitungan,
                cbr_kasus_id,
                cbr_similarity,
                cbr_detail_perhitungan,
                status_validasi,
                feedback_user,
                keterangan_admin
              ) VALUES (?, ?, NULL, 0, NULL, NULL, 0, NULL, ?, NULL, NULL)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $id_user, $tanggal_diagnosis, $status_validasi);
    $stmt->execute();
    $id_diagnosis = $conn->insert_id;

    // 2. Hitung Dempster-Shafer
    $ds_result = calculateDempsterShafer($conn, $jawaban);
    $ds_penyakit_id = $ds_result['penyakit_id'];
    $ds_nilai_kepercayaan = $ds_result['nilai_kepercayaan'];
    $ds_detail = json_encode($ds_result['detail_perhitungan']);

    // Debug: Print hasil DS
    error_log("DS Result: " . print_r($ds_result, true));

    // 3. Hitung Case-Based Reasoning
    $cbr_result = calculateCBR($conn, $jawaban);
    $cbr_kasus_id = $cbr_result['kasus_id'];
    $cbr_similarity = $cbr_result['similarity'];  // Ini akan menjadi similarity global
    $cbr_detail = json_encode($cbr_result['detail_perhitungan']);
    // Debug: Print hasil CBR
    error_log("CBR Result: " . print_r($cbr_result, true));

    // 4. Update hasil diagnosis dengan semua nilai yang sudah dihitung
    $query = "UPDATE hasil_diagnosis 
              SET ds_penyakit_id = ?, 
                  ds_nilai_kepercayaan = ?,
                  ds_detail_perhitungan = ?,
                  cbr_kasus_id = ?,
                  cbr_similarity = ?,
                  cbr_detail_perhitungan = ?
              WHERE id_diagnosis = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        "idsidsi",
        $ds_penyakit_id,
        $ds_nilai_kepercayaan,
        $ds_detail,
        $cbr_kasus_id,
        $cbr_similarity,
        $cbr_detail,
        $id_diagnosis
    );

    if (!$stmt->execute()) {
        throw new Exception("Gagal menyimpan hasil diagnosis");
    }

    // Commit transaction
    $conn->commit();

    // Redirect ke halaman hasil dengan pesan sukses
    // $_SESSION['flash_message'] = [
    //     'type' => 'success',
    //     'message' => 'Diagnosis berhasil diproses!'
    // ];
    header("Location: result.php?id=" . $id_diagnosis);
    exit;
} catch (Exception $e) {
    // Rollback jika terjadi error
    $conn->rollback();

    // Tambahkan error log untuk debugging
    error_log("Error in diagnosis process: " . $e->getMessage() . "\n" .
        "Stack trace: " . $e->getTraceAsString() . "\n" .
        "POST data: " . print_r($_POST, true));

    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'Terjadi kesalahan saat memproses diagnosis: ' . $e->getMessage()
    ];

    header("Location: index.php");
    exit;
}