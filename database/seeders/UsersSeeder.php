<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\LearningStyleProfile;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@learning.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create Teacher Users
        $teachers = [
            [
                'name' => 'Dr. Siti Nurhaliza',
                'email' => 'siti.nurhaliza@learning.test',
                'teacher_number' => 'TCH001',
                'subject' => 'Matematika',
                'department' => 'MIPA'
            ],
            [
                'name' => 'Ahmad Rizky, S.Pd',
                'email' => 'ahmad.rizky@learning.test',
                'teacher_number' => 'TCH002',
                'subject' => 'Fisika',
                'department' => 'MIPA'
            ],
            [
                'name' => 'Maya Sari, M.Pd',
                'email' => 'maya.sari@learning.test',
                'teacher_number' => 'TCH003',
                'subject' => 'Bahasa Indonesia',
                'department' => 'Bahasa'
            ],
            [
                'name' => 'Budi Hartono, S.Si',
                'email' => 'budi.hartono@learning.test',
                'teacher_number' => 'TCH004',
                'subject' => 'Kimia',
                'department' => 'MIPA'
            ],
            [
                'name' => 'Rina Wijaya, S.Pd',
                'email' => 'rina.wijaya@learning.test',
                'teacher_number' => 'TCH005',
                'subject' => 'Biologi',
                'department' => 'MIPA'
            ],
        ];

        foreach ($teachers as $teacherData) {
            $user = User::create([
                'name' => $teacherData['name'],
                'email' => $teacherData['email'],
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'email_verified_at' => now(),
            ]);

            Teacher::create([
                'user_id' => $user->id,
                'teacher_number' => $teacherData['teacher_number'],
                'subject' => $teacherData['subject'],
                'department' => $teacherData['department'],
            ]);
        }

        // Create Student Users
        $students = [
            // Grade 10 Students - IPA
            [
                'name' => 'Andi Pratama',
                'email' => 'andi.pratama@student.test',
                'student_number' => 'STD2024001',
                'grade_level' => '10',
                'class' => '10 IPA 1',
                'major' => 'IPA',
                'interests' => ['Matematika', 'Fisika', 'Kimia'],
                'learning_style' => ['visual' => 75, 'auditory' => 20, 'kinesthetic' => 30, 'dominant' => 'visual']
            ],
            [
                'name' => 'Sari Dewi',
                'email' => 'sari.dewi@student.test',
                'student_number' => 'STD2024002',
                'grade_level' => '10',
                'class' => '10 IPA 1',
                'major' => 'IPA',
                'interests' => ['Biologi', 'Kimia', 'Matematika'],
                'learning_style' => ['visual' => 50, 'auditory' => 65, 'kinesthetic' => 35, 'dominant' => 'auditory']
            ],
            [
                'name' => 'Reza Firmansyah',
                'email' => 'reza.firmansyah@student.test',
                'student_number' => 'STD2024003',
                'grade_level' => '10',
                'class' => '10 IPA 2',
                'major' => 'IPA',
                'interests' => ['Fisika', 'Matematika', 'Teknologi'],
                'learning_style' => ['visual' => 40, 'auditory' => 30, 'kinesthetic' => 80, 'dominant' => 'kinesthetic']
            ],
            [
                'name' => 'Putri Ayu',
                'email' => 'putri.ayu@student.test',
                'student_number' => 'STD2024004',
                'grade_level' => '10',
                'class' => '10 IPA 2',
                'major' => 'IPA',
                'interests' => ['Biologi', 'Matematika', 'Kesehatan'],
                'learning_style' => ['visual' => 70, 'auditory' => 60, 'kinesthetic' => 45, 'dominant' => 'mixed']
            ],

            // Grade 10 Students - IPS
            [
                'name' => 'Dimas Wijaya',
                'email' => 'dimas.wijaya@student.test',
                'student_number' => 'STD2024005',
                'grade_level' => '10',
                'class' => '10 IPS 1',
                'major' => 'IPS',
                'interests' => ['Sejarah', 'Geografi', 'Ekonomi'],
                'learning_style' => ['visual' => 55, 'auditory' => 75, 'kinesthetic' => 25, 'dominant' => 'auditory']
            ],
            [
                'name' => 'Luna Maharani',
                'email' => 'luna.maharani@student.test',
                'student_number' => 'STD2024006',
                'grade_level' => '10',
                'class' => '10 IPS 1',
                'major' => 'IPS',
                'interests' => ['Bahasa Indonesia', 'Sejarah', 'Seni'],
                'learning_style' => ['visual' => 85, 'auditory' => 40, 'kinesthetic' => 20, 'dominant' => 'visual']
            ],

            // Grade 11 Students
            [
                'name' => 'Bayu Setiawan',
                'email' => 'bayu.setiawan@student.test',
                'student_number' => 'STD2023001',
                'grade_level' => '11',
                'class' => '11 IPA 1',
                'major' => 'IPA',
                'interests' => ['Fisika', 'Matematika', 'Komputer'],
                'learning_style' => ['visual' => 45, 'auditory' => 25, 'kinesthetic' => 90, 'dominant' => 'kinesthetic']
            ],
            [
                'name' => 'Citra Melati',
                'email' => 'citra.melati@student.test',
                'student_number' => 'STD2023002',
                'grade_level' => '11',
                'class' => '11 IPS 1',
                'major' => 'IPS',
                'interests' => ['Ekonomi', 'Bahasa Inggris', 'Sosiologi'],
                'learning_style' => ['visual' => 60, 'auditory' => 70, 'kinesthetic' => 35, 'dominant' => 'mixed']
            ],

            // Grade 12 Students
            [
                'name' => 'Farel Adiputra',
                'email' => 'farel.adiputra@student.test',
                'student_number' => 'STD2022001',
                'grade_level' => '12',
                'class' => '12 IPA 1',
                'major' => 'IPA',
                'interests' => ['Matematika', 'Fisika', 'Teknik'],
                'learning_style' => ['visual' => 80, 'auditory' => 50, 'kinesthetic' => 30, 'dominant' => 'visual']
            ],
            [
                'name' => 'Gita Permatasari',
                'email' => 'gita.permatasari@student.test',
                'student_number' => 'STD2022002',
                'grade_level' => '12',
                'class' => '12 IPS 1',
                'major' => 'IPS',
                'interests' => ['Bahasa Indonesia', 'Sejarah', 'Politik'],
                'learning_style' => ['visual' => 35, 'auditory' => 85, 'kinesthetic' => 40, 'dominant' => 'auditory']
            ],
        ];

        foreach ($students as $studentData) {
            $user = User::create([
                'name' => $studentData['name'],
                'email' => $studentData['email'],
                'password' => Hash::make('password'),
                'role' => 'student',
                'email_verified_at' => now(),
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'student_number' => $studentData['student_number'],
                'grade_level' => $studentData['grade_level'],
                'class' => $studentData['class'],
                'major' => $studentData['major'],
                'learning_interests' => $studentData['interests'],
                'enrollment_year' => 2024 - (int)$studentData['grade_level'] + 10,
                'status' => 'active',
                'profile_completed' => true,
                'preferred_language' => 'id',
            ]);

            // Create learning style profile
            LearningStyleProfile::create([
                'student_id' => $student->id,
                'visual_score' => $studentData['learning_style']['visual'],
                'auditory_score' => $studentData['learning_style']['auditory'],
                'kinesthetic_score' => $studentData['learning_style']['kinesthetic'],
                'dominant_style' => $studentData['learning_style']['dominant'],
                'survey_data' => [
                    'completed_at' => now(),
                    'total_questions' => 15,
                    'responses' => array_fill(1, 15, rand(3, 5)),
                ],
                'analysis_date' => now(),
                'ai_confidence_score' => rand(75, 95),
            ]);
        }
    }
}