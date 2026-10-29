<?php
/**
 * legal_helper.php
 * Helper to get, set, and reset Privacy Policy (Kebijakan Privasi)
 * and Terms & Conditions (Syarat & Ketentuan) for Kedayweb Platform.
 */

if (!function_exists('get_legal_file_path')) {
    function get_legal_file_path(string $type = 'privacy'): string {
        $clean_type = ($type === 'terms') ? 'terms' : 'privacy';
        return __DIR__ . '/uploads/' . $clean_type . '_data.json';
    }
}

if (!function_exists('get_default_legal_data')) {
    function get_default_legal_data(string $type = 'privacy'): array {
        if ($type === 'terms') {
            return [
                'type' => 'terms',
                'badge' => 'Ketentuan Layanan',
                'title' => 'Syarat & Ketentuan Kedayweb Platform',
                'intro' => 'Ketentuan Penggunaan Kedayweb Platform bagi seluruh peserta magang, mentor, dan pengguna:',
                'updated_at' => date('Y-m-d H:i:s'),
                'sections' => [
                    [
                        'id' => 'term-1',
                        'title' => '1. Kode Etik & Disiplin Peserta Magang',
                        'icon' => 'badge',
                        'content' => "Seluruh peserta magang wajib mematuhi SOP kerja yang berlaku, menjaga etika profesionalisme, menghormati sesama rekan dan mentor, serta memenuhi jadwal kerja dan presensi harian sesuai ketentuan."
                    ],
                    [
                        'id' => 'term-2',
                        'title' => '2. Hak Cipta & Kepemilikan Hasil Kerja',
                        'icon' => 'copyright',
                        'content' => "Seluruh hasil karya, kode program, dokumentasi teknis, dan aset proyek yang dibuat selama masa magang merupakan hak milik intelektual Kedayweb Platform dan mitra terkait."
                    ],
                    [
                        'id' => 'term-3',
                        'title' => '3. Kerahasiaan Informasi & Non-Disclosure (NDA)',
                        'icon' => 'lock',
                        'content' => "Peserta magang dilarang membocorkan atau menyebarkan informasi sensitif, data klien, kredensial server, atau kode sumber privat milik Kedayweb kepada pihak luar tanpa izin tertulis."
                    ],
                    [
                        'id' => 'term-4',
                        'title' => '4. Penggunaan Fasilitas & Akun Platform',
                        'icon' => 'devices',
                        'content' => "Akun login dan fasilitas kerja hanya boleh digunakan untuk keperluan tugas kedinasan magang. Dilarang menyalahgunakan hak akses atau meminjamkan akun kepada pihak lain."
                    ],
                    [
                        'id' => 'term-5',
                        'title' => '5. Evaluasi, Sertifikasi, & Sanksi Pelanggaran',
                        'icon' => 'gavel',
                        'content' => "Pelanggaran terhadap tata tertib dapat berakibat pada pemberian surat peringatan, pembatalan hak penerbitan sertifikat magang, hingga penghentian program magang secara sepihak oleh manajemen."
                    ]
                ]
            ];
        }

        // Default Privacy Policy (Kebijakan Privasi)
        return [
            'type' => 'privacy',
            'badge' => 'Privasi & Keamanan Data',
            'title' => 'Kebijakan Privasi Kedayweb Platform',
            'intro' => 'Privasi Anda sangat penting bagi Kedayweb Platform. Kebijakan ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi data pribadi Anda.',
            'updated_at' => date('Y-m-d H:i:s'),
            'sections' => [
                [
                    'id' => 'priv-1',
                    'title' => '1. Pengumpulan Data Pribadi',
                    'icon' => 'person_search',
                    'content' => "Data yang dikumpulkan pada platform ini (seperti nama, institusi asal, kontak, dan foto identitas) hanya digunakan untuk kepentingan administrasi program magang, verifikasi sertifikat, dan presensi harian."
                ],
                [
                    'id' => 'priv-2',
                    'title' => '2. Keamanan & Kerahasiaan Data',
                    'icon' => 'security',
                    'content' => "Kedayweb berkomitmen menjaga kerahasiaan informasi personal peserta intern dan tidak membagikan atau menjual data kepada pihak ketiga tanpa izin resmi dari pengguna yang bersangkutan."
                ],
                [
                    'id' => 'priv-3',
                    'title' => '3. Sertifikasi Digital & Verifikasi Publik',
                    'icon' => 'verified_user',
                    'content' => "Nomor ID sertifikat dan nama peserta yang tercantum pada sertifikat kelulusan dapat diverifikasi secara publik oleh pihak perekrut atau kampus mitra untuk memastikan keabsahan dokumen resmi."
                ],
                [
                    'id' => 'priv-4',
                    'title' => '4. Data Lokasi & Presensi Geofencing',
                    'icon' => 'location_on',
                    'content' => "Data koordinat GPS hanya diakses pada saat pengguna melakukan check-in dan check-out kehadiran untuk memverifikasi radius kantor, dan tidak dilacak secara kontinu di luar jam absensi."
                ],
                [
                    'id' => 'priv-5',
                    'title' => '5. Penyimpanan & Hak Pembaruan Data',
                    'icon' => 'cloud_done',
                    'content' => "Data disimpan secara aman dalam sistem database terenkripsi. Peserta berhak mengajukan koreksi atau pembaruan informasi profil jika terdapat kekeliruan data diri."
                ]
            ]
        ];
    }
}

if (!function_exists('get_legal_data')) {
    function get_legal_data(string $type = 'privacy'): array {
        $clean_type = ($type === 'terms') ? 'terms' : 'privacy';
        $file = get_legal_file_path($clean_type);

        if (file_exists($file)) {
            $raw = @file_get_contents($file);
            if ($raw) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded) && isset($decoded['sections']) && is_array($decoded['sections'])) {
                    return $decoded;
                }
            }
        }

        // Fallback and generate seed file
        $default = get_default_legal_data($clean_type);
        save_legal_data($clean_type, $default);
        return $default;
    }
}

if (!function_exists('save_legal_data')) {
    function save_legal_data(string $type, array $data): bool {
        $clean_type = ($type === 'terms') ? 'terms' : 'privacy';
        $file = get_legal_file_path($clean_type);

        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $data['type'] = $clean_type;
        $data['updated_at'] = date('Y-m-d H:i:s');
        if (!isset($data['sections']) || !is_array($data['sections'])) {
            $data['sections'] = [];
        }

        return (bool)@file_put_contents(
            $file,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }
}

if (!function_exists('reset_legal_data')) {
    function reset_legal_data(string $type): array {
        $clean_type = ($type === 'terms') ? 'terms' : 'privacy';
        $default = get_default_legal_data($clean_type);
        save_legal_data($clean_type, $default);
        return $default;
    }
}

if (!function_exists('add_legal_section')) {
    function add_legal_section(string $type, string $title, string $icon, string $content): bool {
        $clean_type = ($type === 'terms') ? 'terms' : 'privacy';
        $data = get_legal_data($clean_type);
        $new_item = [
            'id' => substr($clean_type, 0, 4) . '-' . time() . '-' . mt_rand(100, 999),
            'title' => trim($title),
            'icon' => !empty(trim($icon)) ? trim($icon) : ($clean_type === 'terms' ? 'gavel' : 'security'),
            'content' => trim($content)
        ];
        $data['sections'][] = $new_item;
        return save_legal_data($clean_type, $data);
    }
}

if (!function_exists('edit_legal_section')) {
    function edit_legal_section(string $type, int $index, string $title, string $icon, string $content): bool {
        $clean_type = ($type === 'terms') ? 'terms' : 'privacy';
        $data = get_legal_data($clean_type);
        if ($index >= 0 && isset($data['sections'][$index])) {
            $data['sections'][$index]['title'] = trim($title);
            $data['sections'][$index]['icon'] = !empty(trim($icon)) ? trim($icon) : ($clean_type === 'terms' ? 'gavel' : 'security');
            $data['sections'][$index]['content'] = trim($content);
            return save_legal_data($clean_type, $data);
        }
        return false;
    }
}

if (!function_exists('delete_legal_section')) {
    function delete_legal_section(string $type, int $index): bool {
        $clean_type = ($type === 'terms') ? 'terms' : 'privacy';
        $data = get_legal_data($clean_type);
        if ($index >= 0 && isset($data['sections'][$index])) {
            array_splice($data['sections'], $index, 1);
            return save_legal_data($clean_type, $data);
        }
        return false;
    }
}

if (!function_exists('move_legal_section')) {
    function move_legal_section(string $type, int $index, int $direction): bool {
        $clean_type = ($type === 'terms') ? 'terms' : 'privacy';
        $data = get_legal_data($clean_type);
        $target = $index + $direction;
        if (isset($data['sections'][$index]) && isset($data['sections'][$target])) {
            $temp = $data['sections'][$index];
            $data['sections'][$index] = $data['sections'][$target];
            $data['sections'][$target] = $temp;
            return save_legal_data($clean_type, $data);
        }
        return false;
    }
}

if (!function_exists('update_legal_header')) {
    function update_legal_header(string $type, string $title, string $badge, string $intro): bool {
        $clean_type = ($type === 'terms') ? 'terms' : 'privacy';
        $data = get_legal_data($clean_type);
        $data['title'] = trim($title);
        $data['badge'] = trim($badge);
        $data['intro'] = trim($intro);
        return save_legal_data($clean_type, $data);
    }
}
