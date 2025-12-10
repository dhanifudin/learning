<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Content;
use App\Models\User;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        // Get teacher users for content creation
        $teachers = User::where('role', 'teacher')->get();
        $mathTeacher = $teachers->where('email', 'siti.nurhaliza@learning.test')->first();
        $physicsTeacher = $teachers->where('email', 'ahmad.rizky@learning.test')->first();
        $indonesianTeacher = $teachers->where('email', 'maya.sari@learning.test')->first();
        $chemistryTeacher = $teachers->where('email', 'budi.hartono@learning.test')->first();
        $biologyTeacher = $teachers->where('email', 'rina.wijaya@learning.test')->first();

        $contents = [
            // Mathematics Content
            [
                'title' => 'Pengenalan Trigonometri',
                'description' => 'Materi pengenalan dasar trigonometri meliputi sinus, cosinus, dan tangen. Dilengkapi dengan contoh soal dan penerapan dalam kehidupan sehari-hari.',
                'subject' => 'Matematika',
                'topic' => 'Trigonometri',
                'grade_level' => '10',
                'content_type' => 'video',
                'target_learning_style' => 'visual',
                'difficulty_level' => 'beginner',
                'external_url' => 'https://www.youtube.com/watch?v=trigonometry-basics',
                'thumbnail_url' => 'https://i.ytimg.com/vi/trigonometry-basics/maxresdefault.jpg',
                'duration_minutes' => 25,
                'metadata' => [
                    'topics_covered' => ['Sinus', 'Cosinus', 'Tangen', 'Aplikasi'],
                    'prerequisites' => ['Aljabar dasar', 'Geometri'],
                    'learning_objectives' => ['Memahami konsep trigonometri', 'Menghitung nilai sinus, cosinus, tangen']
                ],
                'created_by' => $mathTeacher->id ?? 1,
            ],
            [
                'title' => 'Latihan Soal Trigonometri',
                'description' => 'Kumpulan latihan soal trigonometri dengan berbagai tingkat kesulitan. Cocok untuk latihan mandiri dengan pembahasan lengkap.',
                'subject' => 'Matematika',
                'topic' => 'Trigonometri',
                'grade_level' => '10',
                'content_type' => 'pdf',
                'target_learning_style' => 'kinesthetic',
                'difficulty_level' => 'intermediate',
                'file_url' => '/storage/content/math/trigonometry-exercises.pdf',
                'duration_minutes' => 60,
                'metadata' => [
                    'question_count' => 25,
                    'difficulty_distribution' => ['mudah' => 10, 'sedang' => 10, 'sulit' => 5]
                ],
                'created_by' => $mathTeacher->id ?? 1,
            ],
            [
                'title' => 'Podcast Matematika: Sejarah Trigonometri',
                'description' => 'Mendengarkan sejarah perkembangan trigonometri dari zaman kuno hingga modern. Cocok untuk pembelajaran auditori.',
                'subject' => 'Matematika',
                'topic' => 'Trigonometri',
                'grade_level' => '10',
                'content_type' => 'audio',
                'target_learning_style' => 'auditory',
                'difficulty_level' => 'beginner',
                'external_url' => 'https://open.spotify.com/episode/trigonometry-history',
                'duration_minutes' => 30,
                'metadata' => [
                    'format' => 'podcast',
                    'language' => 'indonesian'
                ],
                'created_by' => $mathTeacher->id ?? 1,
            ],

            // Physics Content
            [
                'title' => 'Hukum Newton dan Gerak',
                'description' => 'Penjelasan lengkap tentang tiga hukum Newton disertai demonstrasi dan percobaan sederhana.',
                'subject' => 'Fisika',
                'topic' => 'Mekanika',
                'grade_level' => '10',
                'content_type' => 'video',
                'target_learning_style' => 'visual',
                'difficulty_level' => 'beginner',
                'external_url' => 'https://www.youtube.com/watch?v=newton-laws',
                'thumbnail_url' => 'https://i.ytimg.com/vi/newton-laws/maxresdefault.jpg',
                'duration_minutes' => 35,
                'metadata' => [
                    'experiments' => ['Percobaan Inersia', 'Gaya dan Percepatan', 'Aksi-Reaksi'],
                    'formulas_covered' => ['F = ma', 'F = -F']
                ],
                'created_by' => $physicsTeacher->id ?? 1,
            ],
            [
                'title' => 'Simulasi Gerak Parabola',
                'description' => 'Aplikasi interaktif untuk memahami konsep gerak parabola dengan berbagai variabel yang dapat diubah.',
                'subject' => 'Fisika',
                'topic' => 'Mekanika',
                'grade_level' => '10',
                'content_type' => 'interactive',
                'target_learning_style' => 'kinesthetic',
                'difficulty_level' => 'intermediate',
                'external_url' => 'https://phet.colorado.edu/sims/projectile-motion',
                'duration_minutes' => 45,
                'metadata' => [
                    'simulation_type' => 'PhET',
                    'variables' => ['kecepatan awal', 'sudut elevasi', 'massa'],
                    'interactive_features' => ['drag_drop', 'real_time_calculation']
                ],
                'created_by' => $physicsTeacher->id ?? 1,
            ],

            // Chemistry Content
            [
                'title' => 'Tabel Periodik Unsur',
                'description' => 'Pengenalan struktur tabel periodik, golongan, periode, dan sifat-sifat unsur kimia.',
                'subject' => 'Kimia',
                'topic' => 'Struktur Atom',
                'grade_level' => '10',
                'content_type' => 'video',
                'target_learning_style' => 'visual',
                'difficulty_level' => 'beginner',
                'external_url' => 'https://www.youtube.com/watch?v=periodic-table',
                'thumbnail_url' => 'https://i.ytimg.com/vi/periodic-table/maxresdefault.jpg',
                'duration_minutes' => 40,
                'metadata' => [
                    'elements_covered' => 118,
                    'groups_explained' => ['alkali', 'halogen', 'gas mulia'],
                    'visual_aids' => ['animated_table', 'electron_configuration']
                ],
                'created_by' => $chemistryTeacher->id ?? 1,
            ],
            [
                'title' => 'Praktikum Virtual: Reaksi Kimia',
                'description' => 'Laboratorium virtual untuk melakukan berbagai reaksi kimia dengan aman dan mudah.',
                'subject' => 'Kimia',
                'topic' => 'Reaksi Kimia',
                'grade_level' => '10',
                'content_type' => 'interactive',
                'target_learning_style' => 'kinesthetic',
                'difficulty_level' => 'intermediate',
                'external_url' => 'https://virtual-lab.chemistry.com',
                'duration_minutes' => 60,
                'metadata' => [
                    'experiments' => ['asam_basa', 'redoks', 'sintesis'],
                    'safety_features' => true,
                    'equipment_simulation' => ['pipet', 'bunsen_burner', 'beaker']
                ],
                'created_by' => $chemistryTeacher->id ?? 1,
            ],

            // Biology Content
            [
                'title' => 'Sistem Peredaran Darah Manusia',
                'description' => 'Animasi 3D yang menunjukkan alur peredaran darah dalam tubuh manusia, dari jantung ke seluruh tubuh.',
                'subject' => 'Biologi',
                'topic' => 'Sistem Tubuh',
                'grade_level' => '11',
                'content_type' => 'video',
                'target_learning_style' => 'visual',
                'difficulty_level' => 'intermediate',
                'external_url' => 'https://www.youtube.com/watch?v=circulatory-system',
                'thumbnail_url' => 'https://i.ytimg.com/vi/circulatory-system/maxresdefault.jpg',
                'duration_minutes' => 30,
                'metadata' => [
                    'organs_covered' => ['jantung', 'arteri', 'vena', 'kapiler'],
                    'animation_quality' => '3D',
                    'medical_accuracy' => true
                ],
                'created_by' => $biologyTeacher->id ?? 1,
            ],

            // Indonesian Language Content
            [
                'title' => 'Teknik Menulis Esai yang Baik',
                'description' => 'Panduan lengkap menulis esai dengan struktur yang benar, mulai dari penentuan topik hingga kesimpulan.',
                'subject' => 'Bahasa Indonesia',
                'topic' => 'Menulis',
                'grade_level' => '10',
                'content_type' => 'text',
                'target_learning_style' => 'visual',
                'difficulty_level' => 'intermediate',
                'file_url' => '/storage/content/indonesian/essay-writing-guide.pdf',
                'duration_minutes' => 90,
                'metadata' => [
                    'essay_types' => ['argumentatif', 'deskriptif', 'naratif'],
                    'examples_included' => 5,
                    'writing_tips' => 15
                ],
                'created_by' => $indonesianTeacher->id ?? 1,
            ],
            [
                'title' => 'Diskusi Sastra Indonesia Modern',
                'description' => 'Podcast diskusi tentang karya-karya sastra Indonesia modern dan analisis mendalam.',
                'subject' => 'Bahasa Indonesia',
                'topic' => 'Sastra',
                'grade_level' => '12',
                'content_type' => 'audio',
                'target_learning_style' => 'auditory',
                'difficulty_level' => 'advanced',
                'external_url' => 'https://open.spotify.com/episode/modern-indonesian-literature',
                'duration_minutes' => 45,
                'metadata' => [
                    'authors_discussed' => ['Pramoedya Ananta Toer', 'Chairil Anwar', 'Ayu Utami'],
                    'literary_periods' => ['angkatan 45', 'angkatan 66', 'kontemporer']
                ],
                'created_by' => $indonesianTeacher->id ?? 1,
            ],

            // Advanced Mathematics
            [
                'title' => 'Kalkulus: Limit dan Kontinuitas',
                'description' => 'Pengenalan konsep limit dalam kalkulus dengan pendekatan intuitif dan formal.',
                'subject' => 'Matematika',
                'topic' => 'Kalkulus',
                'grade_level' => '12',
                'content_type' => 'video',
                'target_learning_style' => 'visual',
                'difficulty_level' => 'advanced',
                'external_url' => 'https://www.youtube.com/watch?v=calculus-limits',
                'thumbnail_url' => 'https://i.ytimg.com/vi/calculus-limits/maxresdefault.jpg',
                'duration_minutes' => 50,
                'metadata' => [
                    'concepts_covered' => ['definisi limit', 'limit kiri kanan', 'kontinuitas'],
                    'graphical_approach' => true,
                    'problem_solving' => 10
                ],
                'created_by' => $mathTeacher->id ?? 1,
            ],
        ];

        foreach ($contents as $contentData) {
            Content::create(array_merge($contentData, [
                'views_count' => rand(10, 500),
                'rating' => rand(35, 50) / 10, // Rating 3.5 - 5.0
                'is_active' => true,
            ]));
        }
    }
}