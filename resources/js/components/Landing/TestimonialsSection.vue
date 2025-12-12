<template>
  <section id="testimonials" class="testimonials-section py-16 lg:py-24 bg-white">
    <div class="container mx-auto px-4">
      <!-- Section Header -->
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
          {{ $t('landing.testimonials.title') }}
        </h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
          {{ $t('landing.testimonials.subtitle') }}
        </p>
      </div>

      <!-- Student Testimonials -->
      <div class="mb-16">
        <div class="flex items-center justify-center mb-8">
          <div class="flex items-center bg-blue-50 rounded-full px-6 py-3">
            <UserIcon class="w-5 h-5 text-educational-primary mr-2" />
            <h3 class="text-lg font-semibold text-educational-primary">
              {{ $t('landing.testimonials.student_stories') }}
            </h3>
          </div>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            v-for="(testimonial, index) in studentTestimonials"
            :key="testimonial.id"
            class="testimonial-card"
            :style="{ animationDelay: `${index * 200}ms` }"
          >
            <!-- Student Info -->
            <div class="flex items-center mb-4">
              <div class="relative">
                <img 
                  :src="testimonial.photo || '/images/placeholders/student-avatar.png'"
                  :alt="testimonial.name"
                  class="w-16 h-16 rounded-full object-cover border-4 border-blue-100"
                  loading="lazy"
                />
                <div v-if="testimonial.verified" class="absolute -bottom-1 -right-1 w-6 h-6 bg-learning-success rounded-full flex items-center justify-center">
                  <CheckIcon class="w-4 h-4 text-white" />
                </div>
              </div>
              
              <div class="ml-4 flex-1">
                <h4 class="font-semibold text-gray-900">{{ testimonial.name }}</h4>
                <p class="text-sm text-gray-600">{{ testimonial.grade }} - {{ testimonial.school }}</p>
                <div class="flex items-center mt-1">
                  <span class="inline-block w-3 h-3 rounded-full mr-2" :class="getLearningStyleColor(testimonial.learning_style)"></span>
                  <span class="text-xs text-gray-500">{{ testimonial.learning_style }} Learner</span>
                </div>
              </div>
            </div>

            <!-- Quote -->
            <blockquote class="text-gray-700 mb-4 leading-relaxed">
              "{{ testimonial.quote }}"
            </blockquote>

            <!-- Improvement Badge -->
            <div class="flex items-center justify-between">
              <div class="flex items-center bg-learning-success/10 rounded-full px-3 py-1">
                <TrendingUpIcon class="w-4 h-4 text-learning-success mr-1" />
                <span class="text-sm font-semibold text-learning-success">
                  {{ testimonial.improvement }}
                </span>
              </div>
              
              <!-- Star Rating -->
              <div class="flex items-center">
                <StarIcon 
                  v-for="star in 5" 
                  :key="star" 
                  class="w-4 h-4 text-yellow-400 fill-current"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Teacher Testimonials -->
      <div class="bg-gray-50 rounded-2xl p-8 lg:p-12">
        <div class="flex items-center justify-center mb-8">
          <div class="flex items-center bg-indonesian-red/10 rounded-full px-6 py-3">
            <AcademicCapIcon class="w-5 h-5 text-indonesian-red mr-2" />
            <h3 class="text-lg font-semibold text-indonesian-red">
              {{ $t('landing.testimonials.teacher_insights') }}
            </h3>
          </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
          <div
            v-for="(testimonial, index) in teacherTestimonials"
            :key="testimonial.id"
            class="teacher-testimonial-card"
          >
            <!-- Teacher Info -->
            <div class="flex items-start space-x-4 mb-6">
              <div class="relative flex-shrink-0">
                <img 
                  :src="testimonial.photo || '/images/placeholders/teacher-avatar.png'"
                  :alt="testimonial.name"
                  class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-lg"
                  loading="lazy"
                />
                <div v-if="testimonial.verified" class="absolute -bottom-1 -right-1 w-6 h-6 bg-learning-success rounded-full flex items-center justify-center">
                  <CheckIcon class="w-4 h-4 text-white" />
                </div>
              </div>
              
              <div class="flex-1">
                <h4 class="text-lg font-bold text-gray-900">{{ testimonial.name }}</h4>
                <p class="text-sm text-gray-600 mb-1">{{ testimonial.position }}</p>
                <p class="text-sm text-gray-500">{{ testimonial.school }}</p>
                
                <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                  <span class="flex items-center">
                    <ClockIcon class="w-3 h-3 mr-1" />
                    {{ testimonial.experience }}
                  </span>
                  <span class="flex items-center">
                    <UsersIcon class="w-3 h-3 mr-1" />
                    {{ testimonial.students_helped }} siswa
                  </span>
                </div>
              </div>
            </div>

            <!-- Quote -->
            <blockquote class="text-lg text-gray-700 leading-relaxed mb-4 font-medium">
              "{{ testimonial.quote }}"
            </blockquote>

            <!-- Teaching Impact -->
            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
              <div class="text-center">
                <div class="text-2xl font-bold text-educational-primary">85%</div>
                <div class="text-xs text-gray-600">{{ $t('landing.testimonials.student_improvement') }}</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-learning-success">92%</div>
                <div class="text-xs text-gray-600">{{ $t('landing.testimonials.teaching_efficiency') }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Overall Impact Stats -->
      <div class="mt-16 text-center">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 max-w-4xl mx-auto">
          <div 
            v-for="stat in impactStats"
            :key="stat.label"
            class="space-y-2"
          >
            <div class="text-3xl md:text-4xl font-bold text-educational-primary">
              {{ stat.value }}{{ stat.suffix }}
            </div>
            <div class="text-sm text-gray-600">
              {{ $t(stat.label) }}
            </div>
          </div>
        </div>
      </div>

      <!-- Video Testimonials Teaser -->
      <div class="mt-16 bg-gradient-to-r from-educational-primary/5 to-indonesian-red/5 rounded-2xl p-8 text-center">
        <div class="max-w-2xl mx-auto">
          <h3 class="text-2xl font-bold text-gray-900 mb-4">
            {{ $t('landing.testimonials.video_title') }}
          </h3>
          <p class="text-gray-600 mb-6">
            {{ $t('landing.testimonials.video_description') }}
          </p>
          
          <button 
            @click="playTestimonialVideo"
            class="btn-video-play group"
          >
            <PlayIcon class="w-6 h-6 mr-2 group-hover:scale-110 transition-transform" />
            {{ $t('landing.testimonials.watch_video') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Video Modal (if needed) -->
    <Teleport to="body">
      <div 
        v-if="showVideoModal"
        class="fixed inset-0 bg-black/75 flex items-center justify-center z-50 p-4"
        @click="closeVideoModal"
      >
        <div class="bg-white rounded-lg max-w-4xl w-full aspect-video relative" @click.stop>
          <button 
            @click="closeVideoModal"
            class="absolute top-4 right-4 w-8 h-8 bg-gray-800/50 text-white rounded-full flex items-center justify-center hover:bg-gray-800/75 transition-colors z-10"
          >
            <XIcon class="w-5 h-5" />
          </button>
          
          <!-- Video iframe or component would go here -->
          <div class="w-full h-full bg-gray-100 rounded-lg flex items-center justify-center">
            <p class="text-gray-500">{{ $t('landing.testimonials.video_placeholder') }}</p>
          </div>
        </div>
      </div>
    </Teleport>
  </section>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { 
  UserIcon,
  AcademicCapIcon,
  CheckIcon,
  TrendingUpIcon,
  StarIcon,
  ClockIcon,
  UsersIcon,
  PlayIcon,
  XIcon
} from 'lucide-vue-next'

interface StudentTestimonial {
  id: number
  name: string
  grade: string
  school: string
  photo: string
  quote: string
  improvement: string
  learning_style: string
  verified: boolean
}

interface TeacherTestimonial {
  id: number
  name: string
  position: string
  school: string
  photo: string
  quote: string
  experience: string
  students_helped: string
  verified: boolean
}

interface Props {
  studentTestimonials: StudentTestimonial[]
  teacherTestimonials: TeacherTestimonial[]
}

const props = defineProps<Props>()
const { t } = useI18n()

const showVideoModal = ref(false)

const impactStats = computed(() => [
  {
    value: '22.5',
    suffix: '%',
    label: 'landing.testimonials.avg_improvement'
  },
  {
    value: '94',
    suffix: '%',
    label: 'landing.testimonials.satisfaction'
  },
  {
    value: '25',
    suffix: 'K+',
    label: 'landing.testimonials.students'
  },
  {
    value: '150',
    suffix: '+',
    label: 'landing.testimonials.schools'
  }
])

const getLearningStyleColor = (style: string): string => {
  const colorMap = {
    'Visual': 'bg-blue-500',
    'Auditory': 'bg-green-500',
    'Kinesthetic': 'bg-purple-500'
  }
  return colorMap[style as keyof typeof colorMap] || 'bg-gray-500'
}

const playTestimonialVideo = (): void => {
  showVideoModal.value = true
  // Track video play event
  if (typeof window !== 'undefined' && window.gtag) {
    window.gtag('event', 'play', {
      event_category: 'Video',
      event_label: 'testimonial_video'
    })
  }
}

const closeVideoModal = (): void => {
  showVideoModal.value = false
}

// Close modal on escape key
const handleKeyDown = (event: KeyboardEvent): void => {
  if (event.key === 'Escape') {
    closeVideoModal()
  }
}

onMounted(() => {
  document.addEventListener('keydown', handleKeyDown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeyDown)
})
</script>

<style scoped>
.testimonial-card {
  @apply bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 opacity-0;
  animation: fadeInUp 0.6s ease-out forwards;
}

.teacher-testimonial-card {
  @apply bg-white rounded-xl p-6 shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300;
}

.btn-video-play {
  @apply inline-flex items-center px-6 py-3 bg-educational-primary hover:bg-educational-primary/90 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-educational-primary/25;
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

/* Ensure responsiveness */
@media (max-width: 768px) {
  .testimonial-card {
    @apply transform-none hover:translate-y-0;
  }
}

/* Modal styling */
.aspect-video {
  aspect-ratio: 16 / 9;
}

/* Custom scrollbar for modal if needed */
.modal-content::-webkit-scrollbar {
  width: 8px;
}

.modal-content::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.modal-content::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}

.modal-content::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>

