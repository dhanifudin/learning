<template>
  <section id="features" class="features-section py-16 lg:py-24 bg-gray-50">
    <div class="container mx-auto px-4">
      <!-- Section Header -->
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
          {{ $t('landing.features.title') }}
        </h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
          {{ $t('landing.features.subtitle') }}
        </p>
      </div>

      <!-- Features Grid -->
      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
        <div
          v-for="(feature, index) in features"
          :key="feature.icon"
          class="feature-card group"
          :style="{ animationDelay: `${index * 150}ms` }"
        >
          <!-- Feature Icon -->
          <div class="feature-icon-wrapper mb-6">
            <div class="feature-icon">
              <component :is="getFeatureIcon(feature.icon)" class="w-8 h-8 text-white" />
            </div>
          </div>

          <!-- Feature Content -->
          <div class="space-y-4">
            <h3 class="text-xl font-bold text-gray-900 group-hover:text-educational-primary transition-colors">
              {{ feature.title }}
            </h3>
            <p class="text-gray-600 leading-relaxed">
              {{ feature.description }}
            </p>

            <!-- Feature Benefits -->
            <ul class="space-y-2">
              <li 
                v-for="benefit in feature.benefits"
                :key="benefit"
                class="flex items-center text-sm text-gray-500"
              >
                <CheckIcon class="w-4 h-4 text-learning-success mr-2 flex-shrink-0" />
                <span>{{ benefit }}</span>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Interactive Feature Demo -->
      <div class="bg-white rounded-2xl shadow-xl p-8 lg:p-12">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
          <!-- Demo Content -->
          <div class="space-y-6">
            <div class="space-y-4">
              <div class="inline-flex items-center bg-educational-primary/10 rounded-full px-4 py-2">
                <SparklesIcon class="w-5 h-5 text-educational-primary mr-2" />
                <span class="text-sm font-medium text-educational-primary">
                  {{ $t('landing.features.demo_badge') }}
                </span>
              </div>
              
              <h3 class="text-2xl md:text-3xl font-bold text-gray-900">
                {{ $t('landing.features.demo_title') }}
              </h3>
              
              <p class="text-lg text-gray-600">
                {{ $t('landing.features.demo_description') }}
              </p>
            </div>

            <!-- Demo Steps -->
            <div class="space-y-4">
              <div 
                v-for="(step, index) in demoSteps"
                :key="step"
                class="flex items-center"
              >
                <div class="w-8 h-8 bg-educational-primary text-white rounded-full flex items-center justify-center text-sm font-bold mr-4 flex-shrink-0">
                  {{ index + 1 }}
                </div>
                <span class="text-gray-700">{{ $t(step) }}</span>
              </div>
            </div>

            <!-- Demo CTA -->
            <Link
              :href="route('demo')"
              class="btn-primary-large group inline-flex"
            >
              <PlayIcon class="w-6 h-6 mr-2 group-hover:scale-110 transition-transform" />
              {{ $t('landing.features.try_demo') }}
            </Link>
          </div>

          <!-- Demo Visual/Animation -->
          <div class="relative">
            <!-- Interactive Preview -->
            <div class="demo-preview bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-100">
              <!-- Simulated Dashboard -->
              <div class="space-y-4">
                <!-- Header -->
                <div class="flex items-center justify-between pb-4 border-b border-blue-200">
                  <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-educational-primary rounded-full flex items-center justify-center">
                      <UserIcon class="w-5 h-5 text-white" />
                    </div>
                    <div>
                      <div class="font-semibold text-gray-900">{{ $t('landing.features.demo_student_name') }}</div>
                      <div class="text-sm text-gray-500">{{ $t('landing.features.demo_grade') }}</div>
                    </div>
                  </div>
                  <div class="text-sm text-educational-primary font-medium">
                    {{ $t('landing.features.demo_learning_style') }}
                  </div>
                </div>

                <!-- Learning Style Chart -->
                <div class="space-y-3">
                  <div class="text-sm font-medium text-gray-700">{{ $t('landing.features.demo_chart_title') }}</div>
                  <div class="space-y-2">
                    <div class="flex items-center">
                      <div class="text-xs text-gray-600 w-16">{{ $t('landing.features.visual') }}</div>
                      <div class="flex-1 bg-gray-200 rounded-full h-2 mx-3">
                        <div 
                          class="bg-blue-500 h-2 rounded-full transition-all duration-1000 ease-out"
                          :style="{ width: demoAnimated ? '85%' : '0%' }"
                        ></div>
                      </div>
                      <div class="text-xs text-gray-600 w-8">85%</div>
                    </div>
                    <div class="flex items-center">
                      <div class="text-xs text-gray-600 w-16">{{ $t('landing.features.auditory') }}</div>
                      <div class="flex-1 bg-gray-200 rounded-full h-2 mx-3">
                        <div 
                          class="bg-green-500 h-2 rounded-full transition-all duration-1000 ease-out delay-300"
                          :style="{ width: demoAnimated ? '65%' : '0%' }"
                        ></div>
                      </div>
                      <div class="text-xs text-gray-600 w-8">65%</div>
                    </div>
                    <div class="flex items-center">
                      <div class="text-xs text-gray-600 w-16">{{ $t('landing.features.kinesthetic') }}</div>
                      <div class="flex-1 bg-gray-200 rounded-full h-2 mx-3">
                        <div 
                          class="bg-purple-500 h-2 rounded-full transition-all duration-1000 ease-out delay-500"
                          :style="{ width: demoAnimated ? '45%' : '0%' }"
                        ></div>
                      </div>
                      <div class="text-xs text-gray-600 w-8">45%</div>
                    </div>
                  </div>
                </div>

                <!-- Recommendations -->
                <div class="space-y-2 pt-2">
                  <div class="text-sm font-medium text-gray-700">{{ $t('landing.features.demo_recommendations') }}</div>
                  <div class="grid grid-cols-2 gap-2">
                    <div class="bg-blue-50 rounded-lg p-3 text-center">
                      <VideoIcon class="w-5 h-5 text-blue-600 mx-auto mb-1" />
                      <div class="text-xs text-blue-900 font-medium">{{ $t('landing.features.video_content') }}</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-3 text-center">
                      <BookOpenIcon class="w-5 h-5 text-green-600 mx-auto mb-1" />
                      <div class="text-xs text-green-900 font-medium">{{ $t('landing.features.interactive_content') }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Floating Elements -->
            <div class="absolute -top-4 -right-4 bg-yellow-400 rounded-full p-3 shadow-lg animate-bounce">
              <StarIcon class="w-6 h-6 text-white" />
            </div>
            
            <div class="absolute -bottom-4 -left-4 bg-learning-success rounded-full p-3 shadow-lg animate-pulse">
              <TrendingUpIcon class="w-6 h-6 text-white" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { 
  CheckIcon,
  SparklesIcon,
  PlayIcon,
  UserIcon,
  VideoIcon,
  BookOpenIcon,
  StarIcon,
  TrendingUpIcon,
  BrainIcon,
  TargetIcon,
  BarChart3Icon,
  GraduationCapIcon
} from 'lucide-vue-next'

interface Feature {
  icon: string
  title: string
  description: string
  benefits: string[]
}

interface Props {
  features: Feature[]
}

const props = defineProps<Props>()
const { t } = useI18n()

const demoAnimated = ref(false)

const demoSteps = computed(() => [
  'landing.features.step_1',
  'landing.features.step_2',
  'landing.features.step_3'
])

const getFeatureIcon = (iconName: string) => {
  const iconMap = {
    'brain-circuit': BrainIcon,
    'target-arrow': TargetIcon,
    'chart-analytics': BarChart3Icon,
    'graduation-cap': GraduationCapIcon
  }
  return iconMap[iconName as keyof typeof iconMap] || BrainIcon
}

// Intersection Observer for animations
let observer: IntersectionObserver | null = null

onMounted(() => {
  // Animate demo after a delay
  setTimeout(() => {
    demoAnimated.value = true
  }, 1000)

  // Set up intersection observer for scroll animations
  if ('IntersectionObserver' in window) {
    observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('animate-fade-in-up')
          }
        })
      },
      { threshold: 0.1 }
    )

    // Observe feature cards
    document.querySelectorAll('.feature-card').forEach((card) => {
      observer?.observe(card)
    })
  }
})

onUnmounted(() => {
  observer?.disconnect()
})
</script>

<style scoped>
.feature-card {
  @apply bg-white rounded-xl p-6 shadow-sm hover:shadow-lg transition-all duration-300 transform hover:-translate-y-2 opacity-0;
  animation: fadeInUp 0.6s ease-out forwards;
}

.feature-icon-wrapper {
  @apply relative;
}

.feature-icon {
  @apply w-16 h-16 bg-gradient-to-br from-educational-primary to-indonesian-red rounded-xl flex items-center justify-center shadow-lg transform group-hover:scale-110 transition-transform duration-300;
}

.feature-icon::before {
  content: '';
  @apply absolute inset-0 bg-gradient-to-br from-educational-primary to-indonesian-red rounded-xl opacity-0 group-hover:opacity-20 transition-opacity duration-300;
  transform: scale(1.2);
}

.btn-primary-large {
  @apply px-8 py-4 bg-educational-primary hover:bg-educational-primary/90 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-educational-primary/25;
}

.demo-preview {
  box-shadow: 
    0 0 0 1px rgba(59, 130, 246, 0.1),
    0 4px 6px -1px rgba(0, 0, 0, 0.1),
    0 2px 4px -1px rgba(0, 0, 0, 0.06);
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

.animate-fade-in-up {
  animation: fadeInUp 0.6s ease-out forwards;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .feature-card {
    @apply transform-none hover:translate-y-0;
  }
  
  .demo-preview {
    @apply p-4;
  }
}
</style>

