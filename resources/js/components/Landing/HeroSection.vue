<template>
  <section 
    id="hero" 
    class="hero-section bg-gradient-to-br from-educational-primary via-blue-600 to-indonesian-red relative overflow-hidden"
  >
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
      <svg class="w-full h-full" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
            <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="1"/>
          </pattern>
        </defs>
        <rect width="100" height="100" fill="url(#grid)" />
      </svg>
    </div>

    <div class="container mx-auto px-4 py-16 lg:py-24 relative z-10">
      <div class="grid lg:grid-cols-2 gap-12 items-center">
        <!-- Hero Content -->
        <div class="text-white space-y-8">
          <!-- Free Badge -->
          <div class="inline-flex items-center bg-learning-success/20 backdrop-blur-sm rounded-full px-4 py-2 border border-learning-success/30">
            <GiftIcon class="w-5 h-5 text-learning-success mr-2" />
            <span class="text-sm font-medium text-learning-success">
              {{ $t('landing.hero.free_badge') }}
            </span>
          </div>

          <!-- Main Headline -->
          <div class="space-y-4">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight">
              {{ $t('landing.hero.headline') }}
              <span class="text-yellow-300">{{ $t('landing.hero.headline_accent') }}</span>
            </h1>
            <p class="text-xl md:text-2xl text-blue-100 leading-relaxed">
              {{ $t('landing.hero.subheadline') }}
            </p>
          </div>

          <!-- Value Propositions -->
          <div class="space-y-3">
            <div 
              v-for="benefit in heroBenefits" 
              :key="benefit"
              class="flex items-center"
            >
              <CheckCircleIcon class="w-6 h-6 text-learning-success mr-3 flex-shrink-0" />
              <span class="text-lg text-blue-100">{{ $t(benefit) }}</span>
            </div>
          </div>

          <!-- Call to Action Buttons -->
          <div class="flex flex-col sm:flex-row gap-4">
            <Link
              v-if="canRegister"
              :href="route('register')"
              class="btn-primary-large group"
              :data-analytics="`utm_source=${visitor.utm_source || 'direct'}&utm_medium=${visitor.utm_medium || 'website'}&utm_campaign=hero_cta`"
            >
              <UserPlusIcon class="w-6 h-6 mr-2 group-hover:scale-110 transition-transform" />
              {{ $t('landing.hero.cta_primary') }}
            </Link>
            
            <Link
              :href="route('demo')"
              class="btn-secondary-large group"
            >
              <PlayCircleIcon class="w-6 h-6 mr-2 group-hover:scale-110 transition-transform" />
              {{ $t('landing.hero.cta_secondary') }}
            </Link>
          </div>

          <!-- Social Proof -->
          <div class="flex items-center space-x-6 pt-4">
            <div class="text-center">
              <div class="text-2xl font-bold text-yellow-300">{{ formatNumber(statistics.active_students) }}+</div>
              <div class="text-sm text-blue-200">{{ $t('landing.hero.active_students') }}</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-yellow-300">{{ statistics.partner_schools }}+</div>
              <div class="text-sm text-blue-200">{{ $t('landing.hero.partner_schools') }}</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-yellow-300">{{ statistics.success_rate }}%</div>
              <div class="text-sm text-blue-200">{{ $t('landing.hero.success_rate') }}</div>
            </div>
          </div>
        </div>

        <!-- Hero Visual -->
        <div class="relative">
          <!-- Main Hero Image/Video -->
          <div class="relative z-10 transform hover:scale-105 transition-transform duration-500">
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-1 shadow-2xl">
              <img 
                :src="heroImage" 
                :alt="$t('landing.hero.image_alt')"
                class="w-full h-auto rounded-xl shadow-lg"
                loading="lazy"
              />
            </div>
          </div>

          <!-- Floating Elements -->
          <div class="absolute -top-4 -left-4 bg-learning-success rounded-full p-3 shadow-lg animate-bounce-slow">
            <BrainIcon class="w-8 h-8 text-white" />
          </div>
          
          <div class="absolute -bottom-4 -right-4 bg-yellow-500 rounded-full p-3 shadow-lg animate-pulse">
            <StarIcon class="w-8 h-8 text-white" />
          </div>
          
          <div class="absolute top-1/2 -right-8 bg-indonesian-red rounded-full p-2 shadow-lg animate-bounce-slow-delay">
            <HeartIcon class="w-6 h-6 text-white" />
          </div>
        </div>
      </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
      <ChevronDownIcon class="w-8 h-8 text-white/60" />
    </div>
  </section>

<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { 
  GiftIcon,
  CheckCircleIcon,
  UserPlusIcon,
  PlayCircleIcon,
  BrainIcon,
  StarIcon,
  HeartIcon,
  ChevronDownIcon
} from 'lucide-vue-next'

interface Props {
  canRegister: boolean
  visitor: {
    is_mobile: boolean
    referrer: string | null
    utm_source: string | null
    utm_medium: string | null
    utm_campaign: string | null
    session_id: string
  }
  statistics: {
    active_students: number
    partner_schools: number
    success_rate: number
  }
}

const props = defineProps<Props>()
const { t } = useI18n()

const heroBenefits = computed(() => [
  'landing.hero.benefit_1',
  'landing.hero.benefit_2', 
  'landing.hero.benefit_3'
])

const heroImage = computed(() => {
  return props.visitor.is_mobile 
    ? '/images/landing/hero-mobile.jpg'
    : '/images/landing/hero-desktop.jpg'
})

const formatNumber = (num: number): string => {
  return new Intl.NumberFormat('id-ID').format(num)
}

// Analytics tracking for CTA clicks
const trackCTAClick = (type: string) => {
  // Analytics implementation will go here
  if (typeof window !== 'undefined' && window.gtag) {
    window.gtag('event', 'click', {
      event_category: 'CTA',
      event_label: type,
      utm_source: props.visitor.utm_source || 'direct',
      utm_medium: props.visitor.utm_medium || 'website'
    })
  }
}
</script>

<style scoped>
.hero-section {
  min-height: 90vh;
  @apply flex items-center;
}

.btn-primary-large {
  @apply inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white bg-learning-success hover:bg-learning-success/90 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-learning-success/25;
}

.btn-secondary-large {
  @apply inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-educational-primary bg-white hover:bg-gray-50 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-white/25;
}

.animate-bounce-slow {
  animation: bounce 3s infinite;
}

.animate-bounce-slow-delay {
  animation: bounce 3s infinite 1s;
}

@keyframes bounce {
  0%, 20%, 53%, 80%, 100% {
    transform: translate3d(0, 0, 0);
  }
  40%, 43% {
    transform: translate3d(0, -30px, 0);
  }
  70% {
    transform: translate3d(0, -15px, 0);
  }
  90% {
    transform: translate3d(0, -4px, 0);
  }
}

/* Ensure responsiveness */
@media (max-width: 640px) {
  .hero-section {
    min-height: 100vh;
  }
  
  .btn-primary-large,
  .btn-secondary-large {
    @apply w-full justify-center;
  }
}
</style>

