<template>
  <section v-if="stories && stories.length" id="success-stories" class="success-stories py-16 lg:py-24 bg-gray-50">
    <div class="container mx-auto px-4">
      <!-- Section Header -->
      <div class="text-center mb-16">
        <div class="inline-flex items-center bg-learning-success/10 rounded-full px-6 py-3 mb-6">
          <TrophyIcon class="w-6 h-6 text-learning-success mr-2" />
          <span class="text-lg font-semibold text-learning-success">
            {{ $t('landing.success_stories.badge') }}
          </span>
        </div>
        
        <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
          {{ $t('landing.success_stories.title') }}
        </h2>
        
        <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
          {{ $t('landing.success_stories.subtitle') }}
        </p>
      </div>

      <!-- Featured Success Story -->
      <div v-if="featuredStory" class="mb-16">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
          <div class="grid lg:grid-cols-2">
            <!-- Story Content -->
            <div class="p-8 lg:p-12">
              <div class="space-y-6">
                <div class="flex items-center space-x-4">
                  <img 
                    :src="featuredStory.photo" 
                    :alt="featuredStory.name"
                    class="w-16 h-16 rounded-full object-cover"
                    loading="lazy"
                  />
                  <div>
                    <h3 class="text-xl font-bold text-gray-900">{{ featuredStory.name }}</h3>
                    <p class="text-gray-600">{{ featuredStory.school }}</p>
                    <p class="text-sm text-educational-primary font-medium">{{ featuredStory.grade }}</p>
                  </div>
                </div>

                <blockquote class="text-lg text-gray-700 leading-relaxed italic">
                  "{{ featuredStory.quote }}"
                </blockquote>

                <div class="space-y-4">
                  <h4 class="font-semibold text-gray-900">{{ $t('landing.success_stories.achievements') }}</h4>
                  <ul class="space-y-2">
                    <li 
                      v-for="achievement in featuredStory.achievements"
                      :key="achievement"
                      class="flex items-center"
                    >
                      <CheckCircleIcon class="w-5 h-5 text-learning-success mr-3" />
                      <span class="text-gray-700">{{ achievement }}</span>
                    </li>
                  </ul>
                </div>

                <div class="grid grid-cols-3 gap-4 pt-4 border-t border-gray-200">
                  <div class="text-center">
                    <div class="text-2xl font-bold text-educational-primary">{{ featuredStory.metrics.before }}</div>
                    <div class="text-xs text-gray-600">{{ $t('landing.success_stories.before') }}</div>
                  </div>
                  <div class="text-center">
                    <ArrowRightIcon class="w-6 h-6 text-gray-400 mx-auto" />
                  </div>
                  <div class="text-center">
                    <div class="text-2xl font-bold text-learning-success">{{ featuredStory.metrics.after }}</div>
                    <div class="text-xs text-gray-600">{{ $t('landing.success_stories.after') }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Visual/Chart Section -->
            <div class="p-8 lg:p-12 bg-gradient-to-br from-educational-primary/5 to-indonesian-red/5">
              <h4 class="text-lg font-bold text-gray-900 mb-6">
                {{ $t('landing.success_stories.progress_chart') }}
              </h4>
              
              <!-- Simple Progress Chart -->
              <div class="space-y-4">
                <div 
                  v-for="(subject, index) in featuredStory.subjects"
                  :key="subject.name"
                  class="progress-item"
                  :style="{ animationDelay: `${index * 200}ms` }"
                >
                  <div class="flex justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">{{ subject.name }}</span>
                    <span class="text-sm text-gray-600">+{{ subject.improvement }}%</span>
                  </div>
                  <div class="progress-bar-container">
                    <div class="progress-bar-bg">
                      <div 
                        class="progress-bar-before" 
                        :style="{ width: `${subject.before}%` }"
                      ></div>
                      <div 
                        class="progress-bar-after" 
                        :style="{ width: `${subject.after}%` }"
                      ></div>
                    </div>
                  </div>
                  <div class="flex justify-between text-xs text-gray-500 mt-1">
                    <span>Sebelum: {{ subject.before }}%</span>
                    <span>Setelah: {{ subject.after }}%</span>
                  </div>
                </div>
              </div>

              <!-- Learning Journey Timeline -->
              <div class="mt-8">
                <h5 class="font-semibold text-gray-900 mb-4">{{ $t('landing.success_stories.learning_journey') }}</h5>
                <div class="space-y-3">
                  <div 
                    v-for="milestone in featuredStory.milestones"
                    :key="milestone.id"
                    class="timeline-item"
                  >
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                      <div class="text-sm font-medium text-gray-900">{{ milestone.title }}</div>
                      <div class="text-xs text-gray-600">{{ milestone.date }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Other Success Stories Grid -->
      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div
          v-for="(story, index) in otherStories"
          :key="story.id"
          class="success-story-card"
          :style="{ animationDelay: `${index * 150}ms` }"
        >
          <div class="story-header">
            <img 
              :src="story.photo" 
              :alt="story.name"
              class="w-12 h-12 rounded-full object-cover"
              loading="lazy"
            />
            <div class="flex-1">
              <h4 class="font-bold text-gray-900">{{ story.name }}</h4>
              <p class="text-sm text-gray-600">{{ story.school }}</p>
            </div>
            <div class="improvement-badge">
              <TrendingUpIcon class="w-4 h-4 mr-1" />
              <span>+{{ story.improvement }}%</span>
            </div>
          </div>

          <blockquote class="story-quote">
            "{{ story.quote }}"
          </blockquote>

          <div class="story-metrics">
            <div class="metric-item">
              <span class="metric-label">{{ $t('landing.success_stories.study_time') }}</span>
              <span class="metric-value">{{ story.metrics.study_time }}</span>
            </div>
            <div class="metric-item">
              <span class="metric-label">{{ $t('landing.success_stories.completion_rate') }}</span>
              <span class="metric-value">{{ story.metrics.completion_rate }}%</span>
            </div>
          </div>

          <div class="story-footer">
            <div class="learning-style-indicator" :class="getStyleColor(story.learning_style)">
              <component :is="getStyleIcon(story.learning_style)" class="w-4 h-4 mr-2" />
              <span>{{ story.learning_style }} Learner</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Call to Action -->
      <div class="mt-16 text-center">
        <div class="max-w-2xl mx-auto">
          <h3 class="text-2xl font-bold text-gray-900 mb-4">
            {{ $t('landing.success_stories.cta_title') }}
          </h3>
          <p class="text-gray-600 mb-8">
            {{ $t('landing.success_stories.cta_description') }}
          </p>
          
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <Link
              :href="route('register')"
              class="btn-primary-large"
            >
              <UserPlusIcon class="w-6 h-6 mr-2" />
              {{ $t('landing.success_stories.join_now') }}
            </Link>
            
            <Link
              :href="route('demo')"
              class="btn-secondary-large"
            >
              <PlayIcon class="w-6 h-6 mr-2" />
              {{ $t('landing.success_stories.try_demo') }}
            </Link>
          </div>
        </div>
      </div>
    </div>
  </section>

<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { 
  TrophyIcon,
  CheckCircleIcon,
  ArrowRightIcon,
  TrendingUpIcon,
  UserPlusIcon,
  PlayIcon,
  EyeIcon,
  VolumeIcon,
  HandIcon
} from 'lucide-vue-next'

interface Story {
  id: number
  title: string
  description: string
  metrics: Record<string, any>
  name?: string
  school?: string
  grade?: string
  photo?: string
  quote?: string
  improvement?: number
  learning_style?: string
  achievements?: string[]
  subjects?: Array<{
    name: string
    before: number
    after: number
    improvement: number
  }>
  milestones?: Array<{
    id: number
    title: string
    date: string
  }>
  study_time?: string
  completion_rate?: number
}

interface Props {
  stories: Story[]
}

const props = defineProps<Props>()
const { t } = useI18n()

// Mock data for demonstration - in real implementation this would come from props
const featuredStory = computed(() => ({
  id: 1,
  name: 'Ahmad Ridwan Pratama',
  school: 'SMAN 1 Jakarta',
  grade: 'Kelas 11 IPA',
  photo: '/images/success-stories/ahmad-featured.jpg',
  quote: 'Platform ini benar-benar mengubah cara saya belajar matematika. AI-nya memahami bahwa saya lebih suka belajar dengan visual, jadi saya dapat video dan diagram yang mudah dipahami. Sekarang matematika jadi mata pelajaran favorit saya!',
  metrics: {
    before: 65,
    after: 88
  },
  achievements: [
    'Meningkatkan nilai matematika dari 65 menjadi 88',
    'Menyelesaikan 150+ latihan soal dalam 3 bulan',
    'Menjadi yang terbaik di kelasnya dalam trigonometri',
    'Membantu teman-teman dengan materi yang sudah dipahami'
  ],
  subjects: [
    { name: 'Aljabar', before: 60, after: 85, improvement: 25 },
    { name: 'Trigonometri', before: 55, after: 90, improvement: 35 },
    { name: 'Geometri', before: 70, after: 88, improvement: 18 },
    { name: 'Kalkulus Dasar', before: 65, after: 82, improvement: 17 }
  ],
  milestones: [
    { id: 1, title: 'Bergabung dengan platform', date: 'Jan 2024' },
    { id: 2, title: 'Menyelesaikan assessment gaya belajar', date: 'Jan 2024' },
    { id: 3, title: 'Nilai pertama meningkat', date: 'Feb 2024' },
    { id: 4, title: 'Mencapai target 80+', date: 'Mar 2024' },
    { id: 5, title: 'Menjadi tutor untuk teman', date: 'Apr 2024' }
  ]
}))

const otherStories = computed(() => [
  {
    id: 2,
    name: 'Siti Nurhaliza',
    school: 'SMAN 3 Bandung',
    photo: '/images/success-stories/siti.jpg',
    quote: 'Fitur audio sangat membantu saya yang lebih suka mendengar penjelasan. Sekarang bisa belajar sambil jogging!',
    improvement: 22,
    learning_style: 'Auditory',
    metrics: {
      study_time: '2.5h/hari',
      completion_rate: 94
    }
  },
  {
    id: 3,
    name: 'Budi Santoso',
    school: 'SMAN 2 Surabaya',
    photo: '/images/success-stories/budi.jpg',
    quote: 'Simulasi interaktif membantu saya memahami konsep yang abstract. Sekarang geometri ruang jadi mudah!',
    improvement: 28,
    learning_style: 'Kinesthetic',
    metrics: {
      study_time: '3h/hari',
      completion_rate: 89
    }
  },
  {
    id: 4,
    name: 'Maya Dewi',
    school: 'SMAN 5 Yogyakarta',
    photo: '/images/success-stories/maya.jpg',
    quote: 'Analytics dashboard membantu saya melihat progress dan area yang harus diperbaiki. Motivasi belajar jadi lebih tinggi.',
    improvement: 31,
    learning_style: 'Visual',
    metrics: {
      study_time: '2h/hari',
      completion_rate: 96
    }
  }
])

const getStyleColor = (style: string): string => {
  const colorMap = {
    'Visual': 'bg-blue-50 text-blue-700 border-blue-200',
    'Auditory': 'bg-green-50 text-green-700 border-green-200',
    'Kinesthetic': 'bg-purple-50 text-purple-700 border-purple-200'
  }
  return colorMap[style as keyof typeof colorMap] || 'bg-gray-50 text-gray-700 border-gray-200'
}

const getStyleIcon = (style: string) => {
  const iconMap = {
    'Visual': EyeIcon,
    'Auditory': VolumeIcon,
    'Kinesthetic': HandIcon
  }
  return iconMap[style as keyof typeof iconMap] || EyeIcon
}
</script>

<style scoped>
.success-story-card {
  @apply bg-white rounded-xl p-6 shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300 opacity-0;
  animation: fadeInUp 0.6s ease-out forwards;
}

.story-header {
  @apply flex items-center space-x-4 mb-4;
}

.improvement-badge {
  @apply flex items-center bg-learning-success/10 text-learning-success rounded-full px-3 py-1 text-sm font-medium;
}

.story-quote {
  @apply text-gray-700 leading-relaxed mb-4 italic;
}

.story-metrics {
  @apply grid grid-cols-2 gap-4 mb-4 pt-4 border-t border-gray-200;
}

.metric-item {
  @apply text-center;
}

.metric-label {
  @apply block text-xs text-gray-600 mb-1;
}

.metric-value {
  @apply text-lg font-bold text-educational-primary;
}

.story-footer {
  @apply pt-4 border-t border-gray-200;
}

.learning-style-indicator {
  @apply inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border;
}

.progress-item {
  @apply opacity-0;
  animation: fadeInUp 0.6s ease-out forwards;
}

.progress-bar-container {
  @apply relative;
}

.progress-bar-bg {
  @apply w-full bg-gray-200 rounded-full h-4 relative overflow-hidden;
}

.progress-bar-before {
  @apply absolute left-0 top-0 h-full bg-gray-400 rounded-full transition-all duration-1000;
}

.progress-bar-after {
  @apply absolute left-0 top-0 h-full bg-learning-success rounded-full transition-all duration-1000 delay-500;
}

.timeline-item {
  @apply flex items-start space-x-3;
}

.timeline-dot {
  @apply w-3 h-3 bg-educational-primary rounded-full mt-1 flex-shrink-0;
}

.timeline-content {
  @apply flex-1;
}

.btn-primary-large {
  @apply inline-flex items-center px-8 py-4 bg-educational-primary hover:bg-educational-primary/90 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-educational-primary/25;
}

.btn-secondary-large {
  @apply inline-flex items-center px-8 py-4 bg-white border border-educational-primary text-educational-primary hover:bg-educational-primary hover:text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-educational-primary/25;
}

/* Animation keyframes */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>

