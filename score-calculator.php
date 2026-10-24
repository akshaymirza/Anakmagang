<?php
// ============================================================
// InternSpace — Score Calculator
// Menghitung skor performa peserta magang secara dinamis
// berdasarkan data presensi (attendance) dan data lainnya.
// ============================================================

if (!function_exists('calculate_intern_scores')) {
    /**
     * Hitung skor performa peserta magang berdasarkan data presensi.
     *
     * @param mysqli $conn   Koneksi database
     * @param int    $userId ID user (intern)
     * @return array ['score_technical'=>int, 'score_discipline'=>int, 'score_attitude'=>int, 'final_grade'=>string, 'detail'=>[...]]
     */
    function calculate_intern_scores(mysqli $conn, int $userId): array
    {
        // ── 1. Ambil data sertifikat untuk rentang tanggal ──
        $certStmt = mysqli_prepare($conn, "SELECT start_date, end_date, score_technical FROM certificates WHERE user_id = ? LIMIT 1");
        if (!$certStmt) {
            return fallback_scores();
        }
        mysqli_stmt_bind_param($certStmt, "i", $userId);
        mysqli_stmt_execute($certStmt);
        $certRow = mysqli_fetch_assoc(mysqli_stmt_get_result($certStmt));
        mysqli_stmt_close($certStmt);

        if (!$certRow) {
            return fallback_scores();
        }

        $startDate = $certRow['start_date'];
        $endDate   = $certRow['end_date'];
        // Jika endDate di masa depan, gunakan hari ini sebagai batas perhitungan
        $calcEnd = min($endDate, date('Y-m-d'));

        // ── 2. Ambil username dari user_id ──
        $userStmt = mysqli_prepare($conn, "SELECT username FROM users WHERE id = ? LIMIT 1");
        if (!$userStmt) {
            return fallback_scores();
        }
        mysqli_stmt_bind_param($userStmt, "i", $userId);
        mysqli_stmt_execute($userStmt);
        $userRow = mysqli_fetch_assoc(mysqli_stmt_get_result($userStmt));
        mysqli_stmt_close($userStmt);

        if (!$userRow) {
            return fallback_scores();
        }
        $username = $userRow['username'];

        // Cek jika user pernah absen sebelum start_date agar catatan tidak terpotong
        $minAttRes = @mysqli_query($conn, "SELECT MIN(date) AS min_d FROM attendance WHERE username = '" . mysqli_real_escape_string($conn, $username) . "'");
        if ($minAttRes && $minAtt = mysqli_fetch_assoc($minAttRes)) {
            if (!empty($minAtt['min_d']) && $minAtt['min_d'] < $startDate) {
                $startDate = $minAtt['min_d'];
            }
        }

        // ── 3. Ambil pengaturan hari kerja ──
        $workDaysStr = 'Senin,Selasa,Rabu,Kamis,Jumat,Sabtu';
        $settingsRes = @mysqli_query($conn, "SELECT attendance_days FROM attendance_settings WHERE id = 1 LIMIT 1");
        if ($settingsRes && $sRow = mysqli_fetch_assoc($settingsRes)) {
            $workDaysStr = $sRow['attendance_days'] ?? $workDaysStr;
        }
        $workDaysMap = array_flip(array_map('trim', explode(',', $workDaysStr)));
        // Map PHP dayOfWeek name (English) → Indonesian
        $dayMap = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu',
        ];

        // ── 4. Hitung jumlah hari kerja wajib dalam periode ──
        $totalWorkDays = 0;
        $current = new DateTime($startDate);
        $end     = new DateTime($calcEnd);
        while ($current <= $end) {
            $dayName = $dayMap[$current->format('l')] ?? '';
            if (isset($workDaysMap[$dayName])) {
                $totalWorkDays++;
            }
            $current->modify('+1 day');
        }

        if ($totalWorkDays === 0) {
            return fallback_scores();
        }

        // ── 5. Ambil data presensi dalam rentang ──
        $attStmt = mysqli_prepare($conn,
            "SELECT date, status, clock_in FROM attendance
             WHERE username = ? AND date >= ? AND date <= ?
             ORDER BY date ASC"
        );
        if (!$attStmt) {
            return fallback_scores();
        }
        mysqli_stmt_bind_param($attStmt, "sss", $username, $startDate, $calcEnd);
        mysqli_stmt_execute($attStmt);
        $attResult = mysqli_stmt_get_result($attStmt);

        $presentDays = 0;
        $lateDays    = 0;
        $absentDays  = 0;
        $permitDays  = 0;
        $totalRecords = 0;

        while ($att = mysqli_fetch_assoc($attResult)) {
            $totalRecords++;
            switch ($att['status']) {
                case 'present':
                    $presentDays++;
                    break;
                case 'late':
                    $lateDays++;
                    break;
                case 'absent':
                    $absentDays++;
                    break;
                case 'permit':
                    $permitDays++;
                    break;
            }
        }
        mysqli_stmt_close($attStmt);

        // Hari tanpa catatan presensi (yang seharusnya masuk) = alfa (absent)
        $daysWithoutRecord = max(0, $totalWorkDays - $totalRecords);
        $effectiveAbsent   = $absentDays + $daysWithoutRecord;

        // ── 6. Hitung Skor Kedisiplinan (0-100) ──
        // Formula:
        //   Base = ((present + late + permit) / totalWorkDays) * 100
        //   Penalty terlambat: -1 per hari terlambat
        //   Penalty alfa: sudah terhitung di base (tidak hadir = 0)
        //   Bonus: permit dihitung sebagai 0.8 kehadiran (izin tetap sedikit mengurangi)
        $attendedDays = $presentDays + $lateDays + ($permitDays * 0.8);
        $attendanceRate = ($attendedDays / $totalWorkDays) * 100;
        $latePenalty = $lateDays * 1.5; // setiap telat mengurangi 1.5 poin
        $scoreDiscipline = max(0, min(100, round($attendanceRate - $latePenalty)));

        // ── 7. Hitung Skor Sikap & Kerjasama (0-100) ──
        // Berdasarkan kedisiplinan dengan bobot & variasi
        // Intern yang hadir konsisten tanpa telat → sikap bagus
        // Rasio "hadir tepat waktu" vs total
        $onTimeRate = ($totalWorkDays > 0) ? ($presentDays / $totalWorkDays) * 100 : 0;
        $scoreAttitude = max(0, min(100, round(
            ($scoreDiscipline * 0.6) + ($onTimeRate * 0.4)
        )));

        // ── 8. Skor Teknis ──
        // Tetap manual dari admin, tapi jika masih default (85) → beri baseline cerdas
        $existingTech = (int)($certRow['score_technical'] ?? 0);
        $scoreTechnical = $existingTech;
        // Jika belum pernah diedit (masih 0 atau template 85) dan sudah ada data presensi
        // beri skor baseline berdasarkan kehadiran (asumsi: rajin hadir = rajin belajar)
        if (($existingTech === 0 || $existingTech === 85) && $totalRecords > 0) {
            // Baseline teknis: 70% dari kehadiran + 30% random seed
            $techBase = ($attendanceRate * 0.70) + 25; // range ~25-95
            $scoreTechnical = max(0, min(100, round($techBase)));
        }

        // ── 9. Tentukan final grade ──
        $avgScore = round(($scoreTechnical + $scoreDiscipline + $scoreAttitude) / 3);
        $finalGrade = determine_grade($avgScore);

        return [
            'score_technical'  => $scoreTechnical,
            'score_discipline' => $scoreDiscipline,
            'score_attitude'   => $scoreAttitude,
            'final_grade'      => $finalGrade,
            'avg_score'        => $avgScore,
            'detail' => [
                'total_work_days'   => $totalWorkDays,
                'present_days'      => $presentDays,
                'late_days'         => $lateDays,
                'absent_days'       => $effectiveAbsent,
                'permit_days'       => $permitDays,
                'attendance_rate'   => round($attendanceRate, 1),
                'total_records'     => $totalRecords,
            ],
        ];
    }
}

if (!function_exists('determine_grade')) {
    /**
     * Tentukan predikat/grade berdasarkan skor rata-rata.
     */
    function determine_grade(int $avg): string
    {
        if ($avg >= 90) return 'A+';
        if ($avg >= 85) return 'A';
        if ($avg >= 80) return 'A-';
        if ($avg >= 75) return 'B+';
        if ($avg >= 70) return 'B';
        if ($avg >= 65) return 'B-';
        if ($avg >= 60) return 'C+';
        if ($avg >= 55) return 'C';
        if ($avg >= 50) return 'C-';
        if ($avg >= 45) return 'D';
        return 'E';
    }
}

if (!function_exists('generate_sub_scores')) {
    /**
     * Generate variasi sub-skor dari skor utama agar tabel nilai tidak terlihat template.
     * Menggunakan seed deterministik dari user_id agar konsisten setiap kali dipanggil.
     *
     * @param int $mainScore  Skor utama (0-100)
     * @param int $count      Jumlah sub-kriteria
     * @param int $seed       Seed untuk variasi (misal user_id)
     * @return array          Array sub-skor
     */
    function generate_sub_scores(int $mainScore, int $count, int $seed): array
    {
        // Buat variasi ±5 poin, deterministik berdasarkan seed
        $subScores = [];
        $total = 0;

        for ($i = 0; $i < $count; $i++) {
            // Pseudo-random deterministik: hash seed+index, ambil beberapa digit
            $hash = crc32("sub_{$seed}_{$i}");
            $variation = ($hash % 11) - 5; // range -5 sampai +5
            $sub = max(0, min(100, $mainScore + $variation));
            $subScores[] = $sub;
            $total += $sub;
        }

        // Adjust agar rata-rata sub-skor ≈ mainScore
        $currentAvg = $total / $count;
        $diff = $mainScore - round($currentAvg);
        if ($diff !== 0) {
            // Tambahkan sisa ke sub-skor pertama
            $subScores[0] = max(0, min(100, $subScores[0] + $diff));
        }

        return $subScores;
    }
}

if (!function_exists('update_certificate_scores')) {
    /**
     * Hitung ulang dan simpan skor ke database untuk sertifikat milik user tertentu.
     *
     * @param mysqli $conn
     * @param int    $userId
     * @return array Skor yang sudah dihitung
     */
    function update_certificate_scores(mysqli $conn, int $userId): array
    {
        $scores = calculate_intern_scores($conn, $userId);

        $stmt = mysqli_prepare($conn,
            "UPDATE certificates SET
                score_technical = ?, score_discipline = ?, score_attitude = ?,
                final_grade = ?
             WHERE user_id = ?"
        );
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iiisi",
                $scores['score_technical'],
                $scores['score_discipline'],
                $scores['score_attitude'],
                $scores['final_grade'],
                $userId
            );
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        return $scores;
    }
}

if (!function_exists('fallback_scores')) {
    /**
     * Skor default jika data belum tersedia (belum ada presensi).
     */
    function fallback_scores(): array
    {
        return [
            'score_technical'  => 0,
            'score_discipline' => 0,
            'score_attitude'   => 0,
            'final_grade'      => '-',
            'avg_score'        => 0,
            'detail' => [
                'total_work_days'   => 0,
                'present_days'      => 0,
                'late_days'         => 0,
                'absent_days'       => 0,
                'permit_days'       => 0,
                'attendance_rate'   => 0,
                'total_records'     => 0,
            ],
        ];
    }
}
