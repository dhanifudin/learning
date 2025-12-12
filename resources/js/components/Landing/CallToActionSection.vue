<template>
  <section class="cta-section bg-gradient-to-r from-educational-primary via-blue-600 to-indonesian-red py-16 lg:py-24 relative overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
      <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.3"%3E%3Ccircle cx="36" cy="24" r="3"/%3E%3Ccircle cx="6" cy="24" r="3"/%3E%3Ccircle cx="36" cy="54" r="3"/%3E%3Ccircle cx="6" cy="54" r="3"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]"></div>
    </div>

    <div class="container mx-auto px-4 relative z-10">
      <div class="text-center space-y-8">
        <!-- CTA Header -->
        <div class="space-y-6">
          <div class="inline-flex items-center bg-white/20 backdrop-blur-sm rounded-full px-6 py-3 border border-white/30">
            <GiftIcon class="w-6 h-6 text-yellow-300 mr-3" />
            <span class="text-lg font-semibold text-white">
              {{ $t('landing.cta.free_forever') }}
            </span>
          </div>
          
          <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white leading-tight">
            {{ $t('landing.cta.headline') }}
          </h2>
          
          <p class="text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto leading-relaxed">
            {{ $t('landing.cta.description') }}
          </p>
        </div>

        <!-- CTA Benefits -->
        <div class="grid md:grid-cols-3 gap-6 max-w-4xl mx-auto">
          <div 
            v-for="benefit in ctaBenefits"
            :key="benefit.icon"
            class="flex flex-col items-center text-center space-y-3"
          >
            <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
              <component :is="getBenefitIcon(benefit.icon)" class="w-6 h-6 text-white" />
            </div>
            <div>
              <h3 class="font-semibold text-white mb-1">{{ $t(benefit.title) }}</h3>
              <p class="text-sm text-blue-200">{{ $t(benefit.description) }}</p>
            </div>
          </div>
        </div>

        <!-- CTA Buttons -->
        <div class="space-y-6">
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <!-- Primary CTA -->
            <Link
              v-if="canRegister"
              :href="route('register')"
              class="btn-cta-primary group"
              @click="trackCTA('register_cta')"
            >
              <UserPlusIcon class="w-6 h-6 mr-2 group-hover:scale-110 transition-transform" />
              {{ $t('landing.cta.register_now') }}
              <ArrowRightIcon class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" />
            </Link>

            <!-- Secondary CTA -->
            <Link
              :href="route('demo')"
              class="btn-cta-secondary group"
              @click="trackCTA('demo_cta')"
            >
              <PlayIcon class="w-6 h-6 mr-2 group-hover:scale-110 transition-transform" />
              {{ $t('landing.cta.try_demo') }}
            </Link>
          </div>

          <!-- Trust Indicators -->
          <div class="flex flex-col sm:flex-row items-center justify-center space-y-4 sm:space-y-0 sm:space-x-8 text-blue-200">
            <div class="flex items-center">
              <ShieldCheckIcon class="w-5 h-5 mr-2" />
              <span class="text-sm">{{ $t('landing.cta.no_credit_card') }}</span>
            </div>
            <div class="flex items-center">
              <ClockIcon class="w-5 h-5 mr-2" />
              <span class="text-sm">{{ $t('landing.cta.setup_time') }}</span>
            </div>
            <div class="flex items-center">
              <UsersIcon class="w-5 h-5 mr-2" />
              <span class="text-sm">{{ $t('landing.cta.join_students', { count: formatNumber(25000) }) }}</span>
            </div>
          </div>
        </div>

        <!-- Newsletter Signup -->
        <div class="max-w-md mx-auto">
          <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 border border-white/20">
            <h3 class="text-lg font-semibold text-white mb-4">
              {{ $t('landing.cta.newsletter_title') }}
            </h3>
            
            <form @submit.prevent="subscribeNewsletter" class="space-y-3">
              <div class="flex flex-col sm:flex-row gap-3">
                <input
                  v-model="newsletterEmail"
                  type="email"
                  :placeholder="$t('landing.cta.email_placeholder')"
                  class="newsletter-input flex-1"
                  required
                  :disabled="isSubscribing"
                />
                <button
                  type="submit"
                  class="btn-newsletter"
                  :disabled="isSubscribing"
                >
                  <span v-if="!isSubscribing">{{ $t('landing.cta.subscribe') }}</span>
                  <span v-else class="flex items-center">
                    <LoaderIcon class="w-4 h-4 mr-2 animate-spin" />
                    {{ $t('landing.cta.subscribing') }}
                  </span>
                </button>
              </div>
              
              <!-- Newsletter result message -->
              <div v-if="newsletterMessage" class="text-sm" :class="newsletterSuccess ? 'text-green-200' : 'text-red-200'">
                {{ newsletterMessage }}
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Floating Action Elements -->
    <div class="absolute top-20 left-10 hidden lg:block animate-bounce-slow">
      <div class="w-16 h-16 bg-yellow-400 rounded-full flex items-center justify-center shadow-lg">
        <StarIcon class="w-8 h-8 text-white" />
      </div>
    </div>
    
    <div class="absolute bottom-20 right-10 hidden lg:block animate-bounce-slow-delay">
      <div class="w-12 h-12 bg-learning-success rounded-full flex items-center justify-center shadow-lg">
        <HeartIcon class="w-6 h-6 text-white" />
      </div>
    </div>
  </section>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { 
  GiftIcon,
  UserPlusIcon,
  PlayIcon,
  ArrowRightIcon,
  ShieldCheckIcon,
  ClockIcon,
  UsersIcon,
  StarIcon,
  HeartIcon,
  LoaderIcon,
  CheckIcon,
  BrainIcon,
  TrendingUpIcon,
  AwardIcon
} from 'lucide-vue-next'

interface Props {
  canRegister?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  canRegister: true
})

const { t } = useI18n()

// Newsletter form state
const newsletterEmail = ref('')
const isSubscribing = ref(false)
const newsletterMessage = ref('')
const newsletterSuccess = ref(false)

const ctaBenefits = computed(() => [
  {
    icon: 'brain',
    title: 'landing.cta.benefit_1_title',
    description: 'landing.cta.benefit_1_description'
  },
  {
    icon: 'trending-up',
    title: 'landing.cta.benefit_2_title',
    description: 'landing.cta.benefit_2_description'
  },
  {
    icon: 'award',
    title: 'landing.cta.benefit_3_title',
    description: 'landing.cta.benefit_3_description'
  }
])

const getBenefitIcon = (iconName: string) => {
  const iconMap = {
    'brain': BrainIcon,
    'trending-up': TrendingUpIcon,
    'award': AwardIcon
  }
  return iconMap[iconName as keyof typeof iconMap] || CheckIcon
}

const formatNumber = (num: number): string => {
  return new Intl.NumberFormat('id-ID').format(num)
}

const trackCTA = (type: string): void => {
  // Analytics tracking
  if (typeof window !== 'undefined' && window.gtag) {
    window.gtag('event', 'click', {
      event_category: 'CTA',
      event_label: type,
      event_source: 'landing_page_cta_section'
    })
  }
}

const subscribeNewsletter = async (): Promise<void> => {
  if (!newsletterEmail.value) return

  isSubscribing.value = true
  newsletterMessage.value = ''

  try {
    await router.post(route('newsletter'), {
      email: newsletterEmail.value,
      interests: ['platform_updates', 'student_tips']
    }, {
      onSuccess: () => {
        newsletterSuccess.value = true
        newsletterMessage.value = t('landing.cta.newsletter_success')
        newsletterEmail.value = ''
      },
      onError: (errors) => {
        newsletterSuccess.value = false
        newsletterMessage.value = errors.email || t('landing.cta.newsletter_error')
      },
      onFinish: () => {
        isSubscribing.value = false
      }
    })
  } catch (error) {
    newsletterSuccess.value = false
    newsletterMessage.value = t('landing.cta.newsletter_error')
    isSubscribing.value = false
  }
}
</script>

<style scoped>
.cta-section {
  position: relative;
}

.btn-cta-primary {
  @apply inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-educational-primary bg-white hover:bg-gray-50 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-white/25;
}

.btn-cta-secondary {
  @apply inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white bg-white/20 hover:bg-white/30 backdrop-blur-sm border border-white/30 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-white/25;
}

.newsletter-input {
  @apply px-4 py-3 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-blue-200 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-white/50 transition-all duration-200;
}

.newsletter-input::placeholder {
  color: rgba(191, 219, 254, 0.8);
}

.btn-newsletter {
  @apply px-6 py-3 bg-learning-success hover:bg-learning-success/90 text-white font-semibold rounded-lg shadow-sm hover:shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-learning-success/50 disabled:opacity-50 disabled:cursor-not-allowed;
}

.animate-bounce-slow {
  animation: bounce 4s infinite;
}

.animate-bounce-slow-delay {
  animation: bounce 4s infinite 2s;
}

@keyframes bounce {
  0%, 20%, 53%, 80%, 100% {
    transform: translate3d(0, 0, 0);
  }
  40%, 43% {
    transform: translate3d(0, -20px, 0);
  }
  70% {
    transform: translate3d(0, -10px, 0);
  }
  90% {
    transform: translate3d(0, -4px, 0);
  }
}

/* Mobile responsiveness */
@media (max-width: 640px) {
  .btn-cta-primary,
  .btn-cta-secondary {
    @apply w-full;
  }
}
</style>

