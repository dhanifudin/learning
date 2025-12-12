<template>
  <div class="landing-page">
    <!-- Simple Landing Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div class="flex items-center">
            <h1 class="text-2xl font-bold text-gray-900">
              🧠 AI Learning Platform
            </h1>
          </div>
          <nav class="flex items-center space-x-4">
            <!-- Authenticated User Navigation -->
            <div v-if="isAuthenticated" class="flex items-center space-x-4">
              <span class="text-gray-600 text-sm">
                Welcome, {{ user?.name }}
              </span>
              <Link 
                :href="dashboard.url()" 
                class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors"
              >
                Dashboard
              </Link>
            </div>
            
            <!-- Guest Navigation -->
            <div v-else class="flex items-center space-x-4">
              <Link 
                v-if="canRegister"
                :href="register.url()" 
                class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors"
              >
                Register
              </Link>
              <Link 
                :href="login.url()" 
                class="text-gray-600 hover:text-gray-900 px-4 py-2 text-sm font-medium transition-colors"
              >
                Login
              </Link>
            </div>
          </nav>
        </div>
      </div>
    </header>

    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-blue-600 to-purple-700 text-white py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <!-- Authenticated User Hero -->
        <div v-if="isAuthenticated">
          <h1 class="text-4xl md:text-6xl font-bold mb-6">
            Selamat Datang Kembali, {{ user?.name }}!
          </h1>
          <p class="text-xl md:text-2xl mb-8 text-blue-100 max-w-3xl mx-auto">
            Lanjutkan perjalanan belajar Anda dengan konten yang dipersonalisasi dan AI yang membantu mencapai tujuan akademik.
          </p>
          <div class="flex flex-col sm:flex-row justify-center gap-4">
            <Link 
              :href="dashboard.url()" 
              class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors"
            >
              Lanjutkan Belajar
            </Link>
            <Link 
              :href="demo.url()" 
              class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors"
            >
              Lihat Fitur Baru
            </Link>
          </div>
        </div>

        <!-- Guest Hero -->
        <div v-else>
          <h1 class="text-4xl md:text-6xl font-bold mb-6">
            Platform Pembelajaran AI
          </h1>
          <p class="text-xl md:text-2xl mb-8 text-blue-100 max-w-3xl mx-auto">
            Sistem pembelajaran berbasis AI yang menganalisis gaya belajar dan memberikan konten personal untuk siswa SMA/SMK Indonesia.
          </p>
          <div class="flex flex-col sm:flex-row justify-center gap-4">
            <Link 
              v-if="canRegister"
              :href="register.url()" 
              class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors"
            >
              Daftar Gratis
            </Link>
            <Link 
              :href="demo.url()" 
              class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors"
            >
              Coba Demo
            </Link>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Mengapa Memilih Platform Kami?
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Teknologi AI terdepan untuk pembelajaran yang dipersonalisasi sesuai dengan gaya belajar setiap siswa.
          </p>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
          <div v-for="feature in features" :key="feature.title" class="text-center p-6">
            <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl">🧠</span>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-4">{{ feature.title }}</h3>
            <p class="text-gray-600">{{ feature.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Platform Terpercaya
          </h2>
        </div>
        
        <div class="grid md:grid-cols-4 gap-8 text-center">
          <div>
            <div class="text-4xl font-bold text-blue-600 mb-2">{{ statistics.active_students?.toLocaleString() || '25,000' }}</div>
            <div class="text-gray-600">Siswa Aktif</div>
          </div>
          <div>
            <div class="text-4xl font-bold text-blue-600 mb-2">{{ statistics.partner_schools || 150 }}</div>
            <div class="text-gray-600">Sekolah Partner</div>
          </div>
          <div>
            <div class="text-4xl font-bold text-blue-600 mb-2">{{ statistics.content_items?.toLocaleString() || '5,000' }}</div>
            <div class="text-gray-600">Konten Pembelajaran</div>
          </div>
          <div>
            <div class="text-4xl font-bold text-blue-600 mb-2">{{ statistics.average_improvement || 23 }}%</div>
            <div class="text-gray-600">Peningkatan Nilai</div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-blue-600 text-white">
      <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <!-- Authenticated User CTA -->
        <div v-if="isAuthenticated">
          <h2 class="text-3xl md:text-4xl font-bold mb-6">
            Tetap Termotivasi dalam Belajar!
          </h2>
          <p class="text-xl mb-8 text-blue-100">
            Manfaatkan fitur AI kami untuk mencapai potensi penuh Anda. Lihat progress Anda dan dapatkan rekomendasi konten terbaru.
          </p>
          <div class="flex flex-col sm:flex-row justify-center gap-4">
            <Link 
              :href="dashboard.url()" 
              class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors inline-block"
            >
              Lihat Progress Saya
            </Link>
            <Link 
              :href="demo.url()" 
              class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors inline-block"
            >
              Jelajahi Fitur Baru
            </Link>
          </div>
        </div>

        <!-- Guest CTA -->
        <div v-else>
          <h2 class="text-3xl md:text-4xl font-bold mb-6">
            Siap Meningkatkan Prestasi Belajar?
          </h2>
          <p class="text-xl mb-8 text-blue-100">
            Bergabunglah dengan ribuan siswa yang telah merasakan manfaat pembelajaran dengan AI.
          </p>
          <Link 
            v-if="canRegister"
            :href="register.url()" 
            class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors inline-block"
          >
            Mulai Belajar Sekarang
          </Link>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
          <h3 class="text-2xl font-bold mb-4">AI Learning Platform</h3>
          <p class="text-gray-400 mb-4">
            Platform pembelajaran berbasis AI untuk siswa SMA/SMK Indonesia
          </p>
          <div class="flex justify-center space-x-6 text-sm text-gray-400">
            <a href="#" class="hover:text-white transition-colors">Tentang Kami</a>
            <a href="#" class="hover:text-white transition-colors">Kontak</a>
            <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
            <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { register, login, demo, dashboard } from '@/routes'

interface Props {
  canRegister: boolean
  auth?: {
    user?: {
      id: number
      name: string
      email: string
      role: string
    }
  }
  visitor?: {
    is_mobile: boolean
    referrer: string | null
    utm_source: string | null
    utm_medium: string | null
    utm_campaign: string | null
    session_id: string
  }
  testimonials?: {
    students: Array<any>
    teachers: Array<any>
  }
  statistics?: {
    active_students: number
    partner_schools: number
    content_items: number
    assessments_completed: number
    average_improvement: number
    teacher_satisfaction: number
    student_engagement: number
    success_rate: number
  }
  features?: Array<{
    icon: string
    title: string
    description: string
    benefits: string[]
  }>
  successStories?: Array<any>
  meta?: {
    title: string
    description: string
    keywords: string
  }
}

const props = withDefaults(defineProps<Props>(), {
  features: () => [
    {
      icon: 'brain',
      title: 'Analisis Gaya Belajar AI',
      description: 'Kecerdasan buatan menganalisis preferensi belajar dan memberikan konten yang disesuaikan',
      benefits: ['Pembelajaran lebih efektif', 'Waktu belajar lebih efisien', 'Hasil yang terukur']
    },
    {
      icon: 'target',
      title: 'Konten Personal',
      description: 'Materi pembelajaran yang disesuaikan dengan gaya belajar dan tingkat kemampuan',
      benefits: ['Video untuk visual learner', 'Audio untuk auditory learner', 'Simulasi untuk kinesthetic learner']
    },
    {
      icon: 'chart',
      title: 'Analytics Mendalam',
      description: 'Pantau progress belajar dengan analitik komprehensif dan insight yang actionable',
      benefits: ['Progress tracking real-time', 'Identifikasi kelemahan', 'Rekomendasi improvement']
    }
  ],
  statistics: () => ({
    active_students: 25000,
    partner_schools: 150,
    content_items: 5000,
    assessments_completed: 100000,
    average_improvement: 23,
    teacher_satisfaction: 94,
    student_engagement: 87,
    success_rate: 89
  })
})

// Computed properties
const isAuthenticated = computed(() => !!props.auth?.user)
const user = computed(() => props.auth?.user)
</script>

<style scoped>
.landing-page {
  min-height: 100vh;
  background-color: white;
}
</style>