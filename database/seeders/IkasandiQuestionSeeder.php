<?php

namespace Database\Seeders;

use App\Models\AssessmentCategory;
use App\Models\AssessmentQuestion;
use Illuminate\Database\Seeder;

class IkasandiQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $framework = [
            [
                'code' => 'GOV',
                'name' => 'Tata Kelola Keamanan Informasi & Kebijakan',
                'weight' => 15.00,
                'order_num' => 1,
                'questions' => [
                    [
                        'code' => 'GOV-01',
                        'question' => 'Apakah OPD memiliki Surat Keputusan (SK) Tim Pengelola Keamanan Informasi / CSIRT internal?',
                        'explanation' => 'Penetapan personil penanggung jawab keamanan informasi dan narahubung persandian.',
                        'guidance' => 'Lampirkan SK Kepala OPD tentang Tim Pengelola TIK / Keamanan Informasi.',
                    ],
                    [
                        'code' => 'GOV-02',
                        'question' => 'Apakah OPD menerapkan SOP Pengelolaan Password dan Hak Akses Akun Sistem Pemerintahan?',
                        'explanation' => 'Kebijakan rotasi password berkala dan pelarangan berbagi akun (shared credentials).',
                        'guidance' => 'Lampirkan dokumen SOP pengelolaan akses & password pengguna.',
                    ],
                ],
            ],
            [
                'code' => 'AST',
                'name' => 'Inventarisasi & Pengamanan Aset Informasi',
                'weight' => 20.00,
                'order_num' => 2,
                'questions' => [
                    [
                        'code' => 'AST-01',
                        'question' => 'Apakah seluruh aset TIK (server, PC, laptop, switch, router) tercatat lengkap dalam inventaris CMDB/ITAM?',
                        'explanation' => 'Pencatatan spesifikasi teknis, nomor seri, penanggung jawab, dan lokasi fisik.',
                        'guidance' => 'Lampirkan daftar inventaris aset TIK termutakhir atau export CMDB SIKANDI.',
                    ],
                    [
                        'code' => 'AST-02',
                        'question' => 'Apakah perangkat PC/Laptop dinas terpasang Antivirus / EDR resmi dan terupdate secara berkala?',
                        'explanation' => 'Proteksi endpoint dari infeksi malware, ransomware, dan spyware.',
                        'guidance' => 'Lampirkan screenshot status update antivirus pada minimal 3 komputer dinas.',
                    ],
                ],
            ],
            [
                'code' => 'CRY',
                'name' => 'Penerapan Kriptografi & Pengamanan Sandi',
                'weight' => 15.00,
                'order_num' => 3,
                'questions' => [
                    [
                        'code' => 'CRY-01',
                        'question' => 'Apakah seluruh aplikasi web dan portal OPD telah menggunakan protokol enkripsi HTTPS dengan sertifikat SSL valid?',
                        'explanation' => 'Mencegah intersepsi data melalui man-in-the-middle attack.',
                        'guidance' => 'Cantumkan tautan URL website dinas dengan status SSL A/Valid.',
                    ],
                    [
                        'code' => 'CRY-02',
                        'question' => 'Apakah OPD telah menerapkan Tanda Tangan Elektronik (TTE) Tersertifikasi BSrE pada dokumen resmi/naskah dinas?',
                        'explanation' => 'Pemanfaatan sertifikat elektronik dari Balai Sertifikasi Elektronik (BSSN).',
                        'guidance' => 'Lampirkan contoh dokumen dinas ber-TTE resmi.',
                    ],
                ],
            ],
            [
                'code' => 'BCP',
                'name' => 'Kelangsungan Layanan, Backup & Pemulihan Data',
                'weight' => 20.00,
                'order_num' => 4,
                'questions' => [
                    [
                        'code' => 'BCP-01',
                        'question' => 'Apakah OPD melakukan pencadangan data (backup) database dan sistem aplikasi secara rutin dan terjadwal?',
                        'explanation' => 'Backup harian/mingguan untuk memastikan ketersediaan data saat kegagalan sistem.',
                        'guidance' => 'Lampirkan log atau jadwal backup rutin database.',
                    ],
                    [
                        'code' => 'BCP-02',
                        'question' => 'Apakah salinan data cadangan disimpan pada media terpisah (off-site backup / cloud storage terisolasi)?',
                        'explanation' => 'Menghindari kehilangan total jika terjadi bencana fisik di ruang server lokal.',
                        'guidance' => 'Lampirkan dokumentasi penyimpanan offsite backup.',
                    ],
                    [
                        'code' => 'BCP-03',
                        'question' => 'Apakah telah dilakukan uji coba pemulihan (restore drill) data cadangan setidaknya sekali dalam setahun?',
                        'explanation' => 'Memastikan integritas berkas backup dapat direstore dengan sempurna.',
                        'guidance' => 'Lampirkan berita acara uji coba restore data.',
                    ],
                ],
            ],
            [
                'code' => 'INC',
                'name' => 'Manajemen Insiden Siber & Pelaporan CSIRT',
                'weight' => 15.00,
                'order_num' => 5,
                'questions' => [
                    [
                        'code' => 'INC-01',
                        'question' => 'Apakah OPD memiliki prosedur penanganan dan pelaporan darurat insiden siber ke CSIRT Diskominfo Ciamis?',
                        'explanation' => 'Jalur komunikasi 1x24 jam ketika terjadi defacement, kebocoran data, atau serangan malware.',
                        'guidance' => 'Lampirkan SOP pelaporan insiden keamanan informasi.',
                    ],
                ],
            ],
            [
                'code' => 'HRD',
                'name' => 'Peningkatan Kesadaran Keamanan Informasi Pegawai',
                'weight' => 15.00,
                'order_num' => 6,
                'questions' => [
                    [
                        'code' => 'HRD-01',
                        'question' => 'Apakah pegawai OPD telah mengikuti sosialisasi / literasi kesadaran keamanan informasi (Security Awareness)?',
                        'explanation' => 'Edukasi pencegahan phishing, social engineering, dan kebersihan siber.',
                        'guidance' => 'Lampirkan daftar hadir / sertifikat sosialisasi keamanan informasi.',
                    ],
                ],
            ],
        ];

        foreach ($framework as $catData) {
            $questions = $catData['questions'];
            unset($catData['questions']);

            $cat = AssessmentCategory::create($catData);

            $order = 1;
            foreach ($questions as $q) {
                AssessmentQuestion::create([
                    'category_id' => $cat->id,
                    'code' => $q['code'],
                    'question' => $q['question'],
                    'explanation' => $q['explanation'],
                    'guidance' => $q['guidance'],
                    'max_score' => 100.00,
                    'is_active' => true,
                    'order_num' => $order++,
                ]);
            }
        }
    }
}
