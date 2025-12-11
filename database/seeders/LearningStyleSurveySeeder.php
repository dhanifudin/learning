<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LearningStyleSurvey;
use App\Models\User;

class LearningStyleSurveySeeder extends Seeder
{
    public function run(): void
    {
        // Get admin user to create the surveys
        $admin = User::where('role', 'admin')->first();

        if (!$admin) {
            $this->command->error('No admin user found. Please run UsersSeeder first.');
            return;
        }

        // Indonesian Learning Style Survey
        $indonesianSurvey = LearningStyleSurvey::create([
            'title' => 'Survei Gaya Belajar Siswa',
            'description' => 'Survei ini dirancang untuk memahami gaya belajar Anda. Jawablah setiap pertanyaan dengan jujur berdasarkan preferensi belajar Anda.',
            'version' => '1.0',
            'language' => 'id',
            'questions' => [
                // Visual Learning Questions (5 questions)
                [
                    'id' => 'visual_1',
                    'text' => 'Saya lebih mudah memahami materi pelajaran ketika disajikan dalam bentuk diagram, grafik, atau gambar.',
                    'category' => 'visual',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'visual_2',
                    'text' => 'Saya lebih suka menonton video pembelajaran daripada hanya mendengarkan penjelasan guru.',
                    'category' => 'visual',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'visual_3',
                    'text' => 'Ketika belajar, saya sering membuat catatan dengan menggunakan warna-warna berbeda dan diagram.',
                    'category' => 'visual',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'visual_4',
                    'text' => 'Saya dapat mengingat informasi dengan lebih baik ketika melihat peta pikiran (mind map) atau bagan.',
                    'category' => 'visual',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'visual_5',
                    'text' => 'Saya lebih mudah mengerjakan soal matematika ketika ada gambar atau ilustrasi yang membantu.',
                    'category' => 'visual',
                    'type' => 'likert',
                    'required' => true
                ],

                // Auditory Learning Questions (5 questions)
                [
                    'id' => 'auditory_1',
                    'text' => 'Saya belajar paling baik ketika guru menjelaskan materi secara lisan dengan detail.',
                    'category' => 'auditory',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'auditory_2',
                    'text' => 'Saya sering membaca materi pelajaran dengan suara keras agar lebih mudah diingat.',
                    'category' => 'auditory',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'auditory_3',
                    'text' => 'Saya lebih suka berdiskusi dengan teman tentang materi pelajaran daripada belajar sendiri.',
                    'category' => 'auditory',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'auditory_4',
                    'text' => 'Saya mudah terganggu dengan suara bising ketika sedang belajar dan membutuhkan lingkungan yang tenang.',
                    'category' => 'auditory',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'auditory_5',
                    'text' => 'Saya sering menghafal materi dengan cara mengulang-ulang secara lisan atau dalam hati.',
                    'category' => 'auditory',
                    'type' => 'likert',
                    'required' => true
                ],

                // Kinesthetic Learning Questions (5 questions)
                [
                    'id' => 'kinesthetic_1',
                    'text' => 'Saya belajar paling efektif ketika bisa mempraktikkan langsung apa yang dipelajari.',
                    'category' => 'kinesthetic',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'kinesthetic_2',
                    'text' => 'Saya lebih suka melakukan eksperimen atau praktikum daripada hanya membaca teori.',
                    'category' => 'kinesthetic',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'kinesthetic_3',
                    'text' => 'Saya sering menggunakan gerakan tangan atau tubuh ketika sedang menjelaskan sesuatu.',
                    'category' => 'kinesthetic',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'kinesthetic_4',
                    'text' => 'Saya lebih mudah mengingat informasi ketika sambil bergerak atau berjalan-jalan.',
                    'category' => 'kinesthetic',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'kinesthetic_5',
                    'text' => 'Saya merasa sulit untuk duduk diam dalam waktu lama dan perlu istirahat secara berkala.',
                    'category' => 'kinesthetic',
                    'type' => 'likert',
                    'required' => true
                ]
            ],
            'scoring_rules' => [
                'visual' => [
                    'question_ids' => ['visual_1', 'visual_2', 'visual_3', 'visual_4', 'visual_5'],
                    'weight' => 1.0,
                    'max_score' => 25
                ],
                'auditory' => [
                    'question_ids' => ['auditory_1', 'auditory_2', 'auditory_3', 'auditory_4', 'auditory_5'],
                    'weight' => 1.0,
                    'max_score' => 25
                ],
                'kinesthetic' => [
                    'question_ids' => ['kinesthetic_1', 'kinesthetic_2', 'kinesthetic_3', 'kinesthetic_4', 'kinesthetic_5'],
                    'weight' => 1.0,
                    'max_score' => 25
                ]
            ],
            'time_limit_minutes' => 15,
            'is_active' => true,
            'published_at' => now(),
            'created_by' => $admin->id,
        ]);

        // English Learning Style Survey
        $englishSurvey = LearningStyleSurvey::create([
            'title' => 'Student Learning Style Survey',
            'description' => 'This survey is designed to understand your learning preferences. Please answer each question honestly based on how you prefer to learn.',
            'version' => '1.0',
            'language' => 'en',
            'questions' => [
                // Visual Learning Questions (5 questions)
                [
                    'id' => 'visual_1',
                    'text' => 'I understand learning materials better when they are presented in diagrams, graphs, or images.',
                    'category' => 'visual',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'visual_2',
                    'text' => 'I prefer watching educational videos rather than just listening to teacher explanations.',
                    'category' => 'visual',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'visual_3',
                    'text' => 'When studying, I often take notes using different colors and diagrams.',
                    'category' => 'visual',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'visual_4',
                    'text' => 'I can remember information better when I see mind maps or charts.',
                    'category' => 'visual',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'visual_5',
                    'text' => 'I find it easier to solve math problems when there are pictures or illustrations to help.',
                    'category' => 'visual',
                    'type' => 'likert',
                    'required' => true
                ],

                // Auditory Learning Questions (5 questions)
                [
                    'id' => 'auditory_1',
                    'text' => 'I learn best when the teacher explains materials verbally in detail.',
                    'category' => 'auditory',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'auditory_2',
                    'text' => 'I often read learning materials out loud to remember them better.',
                    'category' => 'auditory',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'auditory_3',
                    'text' => 'I prefer discussing learning materials with friends rather than studying alone.',
                    'category' => 'auditory',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'auditory_4',
                    'text' => 'I get easily distracted by noise when studying and need a quiet environment.',
                    'category' => 'auditory',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'auditory_5',
                    'text' => 'I often memorize materials by repeating them verbally or mentally.',
                    'category' => 'auditory',
                    'type' => 'likert',
                    'required' => true
                ],

                // Kinesthetic Learning Questions (5 questions)
                [
                    'id' => 'kinesthetic_1',
                    'text' => 'I learn most effectively when I can practice what I am learning directly.',
                    'category' => 'kinesthetic',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'kinesthetic_2',
                    'text' => 'I prefer doing experiments or practical work rather than just reading theory.',
                    'category' => 'kinesthetic',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'kinesthetic_3',
                    'text' => 'I often use hand or body movements when explaining something.',
                    'category' => 'kinesthetic',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'kinesthetic_4',
                    'text' => 'I remember information better when I am moving or walking around.',
                    'category' => 'kinesthetic',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'kinesthetic_5',
                    'text' => 'I find it difficult to sit still for long periods and need regular breaks.',
                    'category' => 'kinesthetic',
                    'type' => 'likert',
                    'required' => true
                ]
            ],
            'scoring_rules' => [
                'visual' => [
                    'question_ids' => ['visual_1', 'visual_2', 'visual_3', 'visual_4', 'visual_5'],
                    'weight' => 1.0,
                    'max_score' => 25
                ],
                'auditory' => [
                    'question_ids' => ['auditory_1', 'auditory_2', 'auditory_3', 'auditory_4', 'auditory_5'],
                    'weight' => 1.0,
                    'max_score' => 25
                ],
                'kinesthetic' => [
                    'question_ids' => ['kinesthetic_1', 'kinesthetic_2', 'kinesthetic_3', 'kinesthetic_4', 'kinesthetic_5'],
                    'weight' => 1.0,
                    'max_score' => 25
                ]
            ],
            'time_limit_minutes' => 15,
            'is_active' => true,
            'published_at' => now(),
            'created_by' => $admin->id,
        ]);

        // Advanced Indonesian Learning Style Survey (More detailed)
        $advancedSurvey = LearningStyleSurvey::create([
            'title' => 'Survei Gaya Belajar Lanjutan',
            'description' => 'Survei komprehensif untuk menganalisis gaya belajar Anda dengan lebih detail. Survei ini mencakup berbagai aspek pembelajaran yang disesuaikan dengan konteks pendidikan Indonesia.',
            'version' => '2.0',
            'language' => 'id',
            'questions' => [
                // Extended Visual Questions
                [
                    'id' => 'visual_adv_1',
                    'text' => 'Ketika guru menjelaskan rumus matematika, saya lebih paham jika ditulis di papan tulis dengan langkah-langkah yang jelas.',
                    'category' => 'visual',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'visual_adv_2',
                    'text' => 'Saya lebih mudah memahami sejarah Indonesia melalui timeline bergambar daripada membaca teks panjang.',
                    'category' => 'visual',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'visual_adv_3',
                    'text' => 'Saya sering menggunakan highlighter dengan warna berbeda untuk menandai bagian penting dalam buku.',
                    'category' => 'visual',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'visual_adv_4',
                    'text' => 'Ketika belajar biologi, saya lebih suka melihat diagram organ tubuh daripada hanya membaca deskripsinya.',
                    'category' => 'visual',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'visual_adv_5',
                    'text' => 'Saya lebih mudah mengingat kosakata bahasa Inggris jika ada gambar yang menyertainya.',
                    'category' => 'visual',
                    'type' => 'likert',
                    'required' => true
                ],

                // Extended Auditory Questions
                [
                    'id' => 'auditory_adv_1',
                    'text' => 'Saya lebih suka belajar dengan mendengarkan podcast atau audio pembelajaran daripada membaca e-book.',
                    'category' => 'auditory',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'auditory_adv_2',
                    'text' => 'Ketika mengerjakan tugas kelompok, saya lebih produktif saat berdiskusi dibanding bekerja sendiri.',
                    'category' => 'auditory',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'auditory_adv_3',
                    'text' => 'Saya mudah mengingat lirik lagu Indonesia dan sering belajar melalui lagu edukatif.',
                    'category' => 'auditory',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'auditory_adv_4',
                    'text' => 'Saya lebih paham pelajaran fisika ketika guru menjelaskan dengan analogi atau cerita.',
                    'category' => 'auditory',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'auditory_adv_5',
                    'text' => 'Saya sering merekam penjelasan guru untuk didengar ulang di rumah.',
                    'category' => 'auditory',
                    'type' => 'likert',
                    'required' => true
                ],

                // Extended Kinesthetic Questions
                [
                    'id' => 'kinesthetic_adv_1',
                    'text' => 'Saya lebih paham pelajaran kimia ketika melakukan praktikum langsung di laboratorium.',
                    'category' => 'kinesthetic',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'kinesthetic_adv_2',
                    'text' => 'Ketika belajar geografi, saya suka membuat maket atau model untuk memahami konsep.',
                    'category' => 'kinesthetic',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'kinesthetic_adv_3',
                    'text' => 'Saya lebih suka mengerjakan tugas sambil berdiri atau berjalan-jalan di sekitar meja.',
                    'category' => 'kinesthetic',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'kinesthetic_adv_4',
                    'text' => 'Saya menggunakan jari untuk menghitung atau memvisualisasikan konsep matematika.',
                    'category' => 'kinesthetic',
                    'type' => 'likert',
                    'required' => true
                ],
                [
                    'id' => 'kinesthetic_adv_5',
                    'text' => 'Saya lebih mudah mengingat tari tradisional Indonesia dengan mempraktikkannya langsung.',
                    'category' => 'kinesthetic',
                    'type' => 'likert',
                    'required' => true
                ]
            ],
            'scoring_rules' => [
                'visual' => [
                    'question_ids' => ['visual_adv_1', 'visual_adv_2', 'visual_adv_3', 'visual_adv_4', 'visual_adv_5'],
                    'weight' => 1.0,
                    'max_score' => 25
                ],
                'auditory' => [
                    'question_ids' => ['auditory_adv_1', 'auditory_adv_2', 'auditory_adv_3', 'auditory_adv_4', 'auditory_adv_5'],
                    'weight' => 1.0,
                    'max_score' => 25
                ],
                'kinesthetic' => [
                    'question_ids' => ['kinesthetic_adv_1', 'kinesthetic_adv_2', 'kinesthetic_adv_3', 'kinesthetic_adv_4', 'kinesthetic_adv_5'],
                    'weight' => 1.0,
                    'max_score' => 25
                ]
            ],
            'time_limit_minutes' => 20,
            'is_active' => false, // Not active by default - needs admin approval
            'published_at' => null,
            'created_by' => $admin->id,
        ]);

        $this->command->info('Created learning style surveys:');
        $this->command->info('- Indonesian Survey (ID: ' . $indonesianSurvey->id . ')');
        $this->command->info('- English Survey (ID: ' . $englishSurvey->id . ')');
        $this->command->info('- Advanced Indonesian Survey (ID: ' . $advancedSurvey->id . ')');
    }
}