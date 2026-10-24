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
    function calculate_intern_scores(mysqli $conn, int $userId, int $certId = 0): array
    {
        // ── 1. Ambil data sertifikat untuk rentang tanggal ──
        if ($certId > 0) {
            $certStmt = mysqli_prepare($conn, "SELECT id, user_id, start_date, end_date, score_technical, score_discipline, score_attitude, is_score_manual FROM certificates WHERE id = ? LIMIT 1");
            mysqli_stmt_bind_param($certStmt, "i", $certId);
        } else {
            $certStmt = mysqli_prepare($conn, "SELECT id, user_id, start_date, end_date, score_technical, score_discipline, score_attitude, is_score_manual FROM certificates WHERE user_id = ? LIMIT 1");
            mysqli_stmt_bind_param($certStmt, "i", $userId);
        }
        if (!$certStmt) {
            return fallback_scores();
        }
        mysqli_stmt_execute($certStmt);
        $certRow = mysqli_fetch_assoc(mysqli_stmt_get_result($certStmt));
        mysqli_stmt_close($certStmt);

        if (!$certRow) {
            return fallback_scores();
        }

        if ($userId <= 0 && !empty($certRow['user_id'])) {
            $userId = (int)$certRow['user_id'];
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
        // Kedisiplinan dihitung murni dari data presensi:
        //   - Rasio kehadiran (present + late + permit)
        //   - Rasio ketepatan waktu (hadir tepat waktu tanpa telat)
        //   - Penalti keterlambatan (-1.5 per hari telat)
        $attendedDays = $presentDays + $lateDays + ($permitDays * 0.8);
        $attendanceRate = ($totalWorkDays > 0) ? ($attendedDays / $totalWorkDays) * 100 : 0;
        $onTimeRate = ($totalWorkDays > 0) ? ($presentDays / $totalWorkDays) * 100 : 0;
        $latePenalty = $lateDays * 1.5;

        $isManual = !empty($certRow['is_score_manual']);
        $existingAttitude = (int)($certRow['score_attitude'] ?? 0);
        $existingTech     = (int)($certRow['score_technical'] ?? 0);

        if ($isManual) {
            // Skor dikunci manual oleh admin
            $scoreDiscipline = (int)($certRow['score_discipline'] ?? 0);
            $scoreAttitude   = $existingAttitude;
            $scoreTechnical  = $existingTech;
        } else {
            // Scoring dinamis murni untuk SEMUA intern:
            if ($totalRecords > 0) {
                // Ada catatan presensi: hitung dinamis
                $scoreDiscipline = max(0, min(100, round(
                    ($attendanceRate * 0.6) + ($onTimeRate * 0.4) - $latePenalty
                )));
                // Sikap dinamis dari konsistensi kehadiran dan ketepatan waktu
                $scoreAttitude = max(0, min(100, round(($attendanceRate * 0.65) + ($onTimeRate * 0.35))));
                // Teknis dinamis dari keaktifan belajar
                $scoreTechnical = max(0, min(100, round(($attendanceRate * 0.70) + 25)));
            } else {
                // SAMA SEKALI BELUM PERNAH HADIR (0 catatan presensi / alpa total):
                // Konsisten dinamis: skor adalah 0 (Grade E)
                $scoreDiscipline = 0;
                $scoreAttitude   = 0;
                $scoreTechnical  = 0;
            }
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
     * @param bool   $force Jika false dan is_score_manual=1, tidak akan menimpa nilai editan admin
     * @return array Skor yang sudah dihitung
     */
    function update_certificate_scores(mysqli $conn, int $userId, bool $force = false, int $certId = 0): array
    {
        // Cek apakah skor dikunci manual oleh admin
        if ($certId > 0) {
            $chk = mysqli_prepare($conn, "SELECT id, user_id, is_score_manual, score_technical, score_discipline, score_attitude, final_grade FROM certificates WHERE id = ? LIMIT 1");
            mysqli_stmt_bind_param($chk, "i", $certId);
        } else {
            $chk = mysqli_prepare($conn, "SELECT id, user_id, is_score_manual, score_technical, score_discipline, score_attitude, final_grade FROM certificates WHERE user_id = ? LIMIT 1");
            mysqli_stmt_bind_param($chk, "i", $userId);
        }
        $cRow = null;
        if ($chk) {
            mysqli_stmt_execute($chk);
            $cRow = mysqli_fetch_assoc(mysqli_stmt_get_result($chk));
            mysqli_stmt_close($chk);
            if ($cRow) {
                if ($certId <= 0) $certId = (int)$cRow['id'];
                if ($userId <= 0) $userId = (int)$cRow['user_id'];
            }
            if ($cRow && !empty($cRow['is_score_manual']) && !$force) {
                // Jangan timpa editan admin jika tidak dipaksa
                $avg = round(((int)$cRow['score_technical'] + (int)$cRow['score_discipline'] + (int)$cRow['score_attitude']) / 3);
                return [
                    'score_technical'  => (int)$cRow['score_technical'],
                    'score_discipline' => (int)$cRow['score_discipline'],
                    'score_attitude'   => (int)$cRow['score_attitude'],
                    'final_grade'      => $cRow['final_grade'] ?: determine_grade($avg),
                    'avg_score'        => $avg,
                    'is_manual'        => true,
                    'detail'           => ['manual' => true],
                ];
            }
        }

        $scores = calculate_intern_scores($conn, $userId, $certId);

        if ($certId > 0) {
            $stmt = mysqli_prepare($conn,
                "UPDATE certificates SET
                    score_technical = ?, score_discipline = ?, score_attitude = ?,
                    final_grade = ?
                 WHERE id = ?"
            );
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "iiisi",
                    $scores['score_technical'],
                    $scores['score_discipline'],
                    $scores['score_attitude'],
                    $scores['final_grade'],
                    $certId
                );
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        } elseif ($userId > 0) {
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
        }

        return $scores;
    }
}

if (!function_exists('seed_intern_attendance')) {
    /**
     * Generate data presensi realistis untuk keperluan simulasi scoring dinamis
     */
    function seed_intern_attendance(mysqli $conn, array $plans): int
    {
        $inserted = 0;
        foreach ($plans as $username => $plan) {
            // Ambil rentang tanggal dari sertifikat
            $q = mysqli_query($conn, "SELECT c.start_date, c.end_date FROM certificates c JOIN users u ON c.user_id = u.id WHERE u.username = '" . mysqli_real_escape_string($conn, $username) . "' LIMIT 1");
            if (!$q || !($cRow = mysqli_fetch_assoc($q))) continue;

            $startDate = $cRow['start_date'];
            $endDate   = min($cRow['end_date'], date('Y-m-d'));

            // Hapus presensi lama user ini agar fresh
            mysqli_query($conn, "DELETE FROM attendance WHERE username = '" . mysqli_real_escape_string($conn, $username) . "'");

            $curr = new DateTime($startDate);
            $end  = new DateTime($endDate);
            $pRatio = $plan['present_ratio'] ?? 0.8;
            $lRatio = $plan['late_ratio'] ?? 0.05;

            $dayIdx = 0;
            while ($curr <= $end && $dayIdx < 50) {
                $dayOfWeek = $curr->format('N'); // 1 (Mon) - 7 (Sun)
                if ($dayOfWeek <= 6) { // Senin - Sabtu
                    $r = (crc32($username . '_' . $dayIdx) % 100) / 100;
                    $status = null;
                    $clockIn = null;
                    if ($r < $pRatio) {
                        // Hadir
                        if ($r < $lRatio) {
                            $status = 'late';
                            $clockIn = '09:' . sprintf('%02d', 10 + ($dayIdx % 45));
                        } else {
                            $status = 'present';
                            $clockIn = '07:' . sprintf('%02d', 45 + ($dayIdx % 15));
                        }
                    } elseif ($r < $pRatio + 0.04) {
                        $status = 'permit';
                    }

                    if ($status) {
                        $dStr = $curr->format('Y-m-d');
                        $ins = mysqli_query($conn, "INSERT INTO attendance (username, date, status, clock_in, location_in, created_at) VALUES ('" . mysqli_real_escape_string($conn, $username) . "', '$dStr', '$status', " . ($clockIn ? "'$clockIn'" : "NULL") . ", 'Kantor Kedayweb', '$dStr 08:00:00')");
                        if ($ins) $inserted++;
                    }
                    $dayIdx++;
                }
                $curr->modify('+1 day');
            }
        }
        return $inserted;
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
