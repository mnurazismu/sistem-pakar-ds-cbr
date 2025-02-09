<?php
require_once '../../config/database.php';
require_once '../../auth/functions.php';

function calculateCBR($conn, $jawaban) {
    // Array untuk menyimpan hasil perhitungan CBR
    $cbr_results = [];
    
    // 1. Retrieve semua kasus yang valid dari database
    $query = "SELECT k.id_kasus, k.id_penyakit, k.status_validasi,
              GROUP_CONCAT(CONCAT(fk.id_gejala, ':', fk.nilai_fitur, ':', COALESCE(fk.bobot_fitur, 1)) SEPARATOR ';') as fitur_kasus
              FROM kasus k
              LEFT JOIN fitur_kasus fk ON k.id_kasus = fk.id_kasus
              WHERE k.status_validasi = 'valid'
              GROUP BY k.id_kasus";
              
    $result = $conn->query($query);
    $kasus_data = $result->fetch_all(MYSQLI_ASSOC);

    // 2. Hitung similarity untuk setiap kasus
    foreach ($kasus_data as $kasus) {
        $similarity = 0;
        $total_bobot = 0;
        $fitur_details = [];
        
        // Parse fitur kasus
        $fitur_kasus = [];
        if ($kasus['fitur_kasus']) {
            foreach (explode(';', $kasus['fitur_kasus']) as $fitur) {
                list($id_gejala, $nilai_kasus, $bobot_fitur) = explode(':', $fitur);
                $fitur_kasus[$id_gejala] = [
                    'nilai' => $nilai_kasus,
                    'bobot' => $bobot_fitur
                ];
            }
        }

        // 3. Ambil nilai input user untuk setiap gejala
        foreach ($jawaban as $id_pertanyaan => $id_pilihan) {
            // Ambil data gejala dan bobot pilihan
            $query = "SELECT pj.bobot_nilai, p.id_gejala 
                     FROM pilihan_jawaban pj 
                     JOIN pertanyaan p ON p.id_pertanyaan = pj.id_pertanyaan 
                     WHERE pj.id_pilihan = ? AND p.id_pertanyaan = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $id_pilihan, $id_pertanyaan);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if ($result) {
                $id_gejala = $result['id_gejala'];
                $nilai_input = $result['bobot_nilai'];
                
                // Jika gejala ada di kasus
                if (isset($fitur_kasus[$id_gejala])) {
                    $nilai_kasus = $fitur_kasus[$id_gejala]['nilai'];
                    $bobot_fitur = $fitur_kasus[$id_gejala]['bobot'];
                    
                    // Hitung similarity lokal
                    $similarity_fitur = (1 - abs($nilai_kasus - $nilai_input)) * $bobot_fitur;
                    $similarity += $similarity_fitur;
                    $total_bobot += $bobot_fitur;
                    
                    // Simpan detail perhitungan
                    $fitur_details[$id_gejala] = [
                        'nilai_kasus' => $nilai_kasus,
                        'nilai_input' => $nilai_input,
                        'bobot' => $bobot_fitur,
                        'similarity_lokal' => $similarity_fitur
                    ];
                }
            }
        }
        
        // 4. Hitung similarity global
        $global_similarity = $total_bobot > 0 ? $similarity / $total_bobot : 0;
        
        // 5. Simpan hasil perhitungan
        $cbr_results[$kasus['id_kasus']] = [
            'similarity' => $global_similarity,
            'id_penyakit' => $kasus['id_penyakit'],
            'detail_fitur' => $fitur_details
        ];
    }
    
    // 6. Urutkan hasil berdasarkan similarity tertinggi
    uasort($cbr_results, function($a, $b) {
        return $b['similarity'] <=> $a['similarity'];
    });
    
    // 7. Ambil kasus dengan similarity tertinggi
    $best_case = array_key_first($cbr_results);
    $best_similarity = $cbr_results[$best_case]['similarity'];
    $best_penyakit = $cbr_results[$best_case]['id_penyakit'];
    
    return [
        'kasus_id' => $best_case,
        'similarity' => $best_similarity,
        'penyakit_id' => $best_penyakit,
        'detail_perhitungan' => $cbr_results
    ];
} 