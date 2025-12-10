<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GradeSubject;

class GradeSubjectsSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            // Grade 10 - Wajib (Required subjects)
            ['10', 'BIND', 'Bahasa Indonesia', 'Indonesian Language', 'wajib', 1],
            ['10', 'BING', 'Bahasa Inggris', 'English Language', 'wajib', 2],
            ['10', 'MTK', 'Matematika', 'Mathematics', 'wajib', 3],
            ['10', 'SJR', 'Sejarah', 'History', 'wajib', 4],
            ['10', 'PKN', 'Pendidikan Kewarganegaraan', 'Civic Education', 'wajib', 5],
            ['10', 'AGM', 'Pendidikan Agama', 'Religious Education', 'wajib', 6],
            ['10', 'PJOK', 'Pendidikan Jasmani', 'Physical Education', 'wajib', 7],
            ['10', 'SBK', 'Seni Budaya', 'Arts and Culture', 'wajib', 8],
            ['10', 'PK', 'Prakarya', 'Craft and Entrepreneurship', 'wajib', 9],
            
            // Grade 10 - Peminatan IPA (Science Specialization)
            ['10', 'FIS', 'Fisika', 'Physics', 'peminatan', 10],
            ['10', 'KIM', 'Kimia', 'Chemistry', 'peminatan', 11],
            ['10', 'BIO', 'Biologi', 'Biology', 'peminatan', 12],
            ['10', 'MTKP', 'Matematika Peminatan', 'Advanced Mathematics', 'peminatan', 13],
            
            // Grade 10 - Peminatan IPS (Social Sciences)
            ['10', 'GEO', 'Geografi', 'Geography', 'peminatan', 14],
            ['10', 'EKO', 'Ekonomi', 'Economics', 'peminatan', 15],
            ['10', 'SOS', 'Sosiologi', 'Sociology', 'peminatan', 16],
            ['10', 'SJRP', 'Sejarah Peminatan', 'Advanced History', 'peminatan', 17],
            
            // Grade 11 - Same subjects as Grade 10
            ['11', 'BIND', 'Bahasa Indonesia', 'Indonesian Language', 'wajib', 1],
            ['11', 'BING', 'Bahasa Inggris', 'English Language', 'wajib', 2],
            ['11', 'MTK', 'Matematika', 'Mathematics', 'wajib', 3],
            ['11', 'SJR', 'Sejarah', 'History', 'wajib', 4],
            ['11', 'PKN', 'Pendidikan Kewarganegaraan', 'Civic Education', 'wajib', 5],
            ['11', 'AGM', 'Pendidikan Agama', 'Religious Education', 'wajib', 6],
            ['11', 'PJOK', 'Pendidikan Jasmani', 'Physical Education', 'wajib', 7],
            ['11', 'SBK', 'Seni Budaya', 'Arts and Culture', 'wajib', 8],
            ['11', 'PK', 'Prakarya', 'Craft and Entrepreneurship', 'wajib', 9],
            
            // Grade 11 - Peminatan IPA
            ['11', 'FIS', 'Fisika', 'Physics', 'peminatan', 10],
            ['11', 'KIM', 'Kimia', 'Chemistry', 'peminatan', 11],
            ['11', 'BIO', 'Biologi', 'Biology', 'peminatan', 12],
            ['11', 'MTKP', 'Matematika Peminatan', 'Advanced Mathematics', 'peminatan', 13],
            
            // Grade 11 - Peminatan IPS
            ['11', 'GEO', 'Geografi', 'Geography', 'peminatan', 14],
            ['11', 'EKO', 'Ekonomi', 'Economics', 'peminatan', 15],
            ['11', 'SOS', 'Sosiologi', 'Sociology', 'peminatan', 16],
            ['11', 'SJRP', 'Sejarah Peminatan', 'Advanced History', 'peminatan', 17],
            
            // Grade 12 - Same subjects as previous grades
            ['12', 'BIND', 'Bahasa Indonesia', 'Indonesian Language', 'wajib', 1],
            ['12', 'BING', 'Bahasa Inggris', 'English Language', 'wajib', 2],
            ['12', 'MTK', 'Matematika', 'Mathematics', 'wajib', 3],
            ['12', 'SJR', 'Sejarah', 'History', 'wajib', 4],
            ['12', 'PKN', 'Pendidikan Kewarganegaraan', 'Civic Education', 'wajib', 5],
            ['12', 'AGM', 'Pendidikan Agama', 'Religious Education', 'wajib', 6],
            ['12', 'PJOK', 'Pendidikan Jasmani', 'Physical Education', 'wajib', 7],
            ['12', 'SBK', 'Seni Budaya', 'Arts and Culture', 'wajib', 8],
            ['12', 'PK', 'Prakarya', 'Craft and Entrepreneurship', 'wajib', 9],
            
            // Grade 12 - Peminatan IPA
            ['12', 'FIS', 'Fisika', 'Physics', 'peminatan', 10],
            ['12', 'KIM', 'Kimia', 'Chemistry', 'peminatan', 11],
            ['12', 'BIO', 'Biologi', 'Biology', 'peminatan', 12],
            ['12', 'MTKP', 'Matematika Peminatan', 'Advanced Mathematics', 'peminatan', 13],
            
            // Grade 12 - Peminatan IPS
            ['12', 'GEO', 'Geografi', 'Geography', 'peminatan', 14],
            ['12', 'EKO', 'Ekonomi', 'Economics', 'peminatan', 15],
            ['12', 'SOS', 'Sosiologi', 'Sociology', 'peminatan', 16],
            ['12', 'SJRP', 'Sejarah Peminatan', 'Advanced History', 'peminatan', 17],
            
            // Lintas Minat (Cross-interest subjects)
            ['10', 'BMAND', 'Bahasa Mandarin', 'Mandarin Language', 'lintas_minat', 18],
            ['10', 'BJEP', 'Bahasa Jepang', 'Japanese Language', 'lintas_minat', 19],
            ['10', 'BKOR', 'Bahasa Korea', 'Korean Language', 'lintas_minat', 20],
            ['11', 'BMAND', 'Bahasa Mandarin', 'Mandarin Language', 'lintas_minat', 18],
            ['11', 'BJEP', 'Bahasa Jepang', 'Japanese Language', 'lintas_minat', 19],
            ['11', 'BKOR', 'Bahasa Korea', 'Korean Language', 'lintas_minat', 20],
            ['12', 'BMAND', 'Bahasa Mandarin', 'Mandarin Language', 'lintas_minat', 18],
            ['12', 'BJEP', 'Bahasa Jepang', 'Japanese Language', 'lintas_minat', 19],
            ['12', 'BKOR', 'Bahasa Korea', 'Korean Language', 'lintas_minat', 20],
        ];

        foreach ($subjects as $subject) {
            GradeSubject::create([
                'grade_level' => $subject[0],
                'subject_code' => $subject[1],
                'subject_name_id' => $subject[2],
                'subject_name_en' => $subject[3],
                'category' => $subject[4],
                'display_order' => $subject[5],
                'is_active' => true,
            ]);
        }
    }
}