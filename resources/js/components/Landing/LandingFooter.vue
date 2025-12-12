<template>
  <footer class="landing-footer bg-gray-900 text-white">
    <!-- Main Footer Content -->
    <div class="container mx-auto px-4 py-16">
      <div class="grid lg:grid-cols-5 md:grid-cols-3 gap-8">
        <!-- Brand Section -->
        <div class="lg:col-span-2">
          <Link 
            :href="route('home')" 
            class="flex items-center space-x-3 mb-6"
          >
            <div class="w-12 h-12 bg-gradient-to-br from-educational-primary to-indonesian-red rounded-xl flex items-center justify-center">
              <BrainIcon class="w-7 h-7 text-white" />
            </div>
            <div class="text-2xl font-bold">
              {{ $t('landing.footer.brand_name') }}
            </div>
          </Link>
          
          <p class="text-gray-400 mb-6 leading-relaxed max-w-md">
            {{ $t('landing.footer.description') }}
          </p>
          
          <!-- Social Media Links -->
          <div class="space-y-4">
            <h4 class="font-semibold text-white">{{ $t('landing.footer.follow_us') }}</h4>
            <div class="flex space-x-4">
              <a
                v-for="social in socialLinks"
                :key="social.name"
                :href="social.url"
                :aria-label="$t(social.label)"
                class="social-link"
                target="_blank"
                rel="noopener noreferrer"
              >
                <component :is="getSocialIcon(social.name)" class="w-6 h-6" />
              </a>
            </div>
          </div>
        </div>

        <!-- Quick Links -->
        <div>
          <h4 class="font-semibold text-white mb-6">{{ $t('landing.footer.quick_links') }}</h4>
          <ul class="space-y-3">
            <li v-for="link in quickLinks" :key="link.name">
              <Link
                :href="link.href"
                class="footer-link"
              >
                {{ $t(link.label) }}
              </Link>
            </li>
          </ul>
        </div>

        <!-- Educational Resources -->
        <div>
          <h4 class="font-semibold text-white mb-6">{{ $t('landing.footer.resources') }}</h4>
          <ul class="space-y-3">
            <li v-for="resource in educationalResources" :key="resource.name">
              <Link
                :href="resource.href"
                class="footer-link"
              >
                {{ $t(resource.label) }}
              </Link>
            </li>
          </ul>
        </div>

        <!-- Support & Contact -->
        <div>
          <h4 class="font-semibold text-white mb-6">{{ $t('landing.footer.support') }}</h4>
          
          <!-- Contact Information -->
          <div class="space-y-4 mb-6">
            <div class="flex items-center space-x-3">
              <MailIcon class="w-5 h-5 text-gray-400" />
              <a 
                href="mailto:info@learning.example.com"
                class="footer-link"
              >
                info@learning.example.com
              </a>
            </div>
            
            <div class="flex items-center space-x-3">
              <PhoneIcon class="w-5 h-5 text-gray-400" />
              <a 
                href="tel:+62211234567"
                class="footer-link"
              >
                +62 21 1234 567
              </a>
            </div>
            
            <div class="flex items-center space-x-3">
              <MapPinIcon class="w-5 h-5 text-gray-400" />
              <span class="text-gray-400 text-sm">
                Jakarta, Indonesia
              </span>
            </div>
          </div>

          <!-- App Download Links -->
          <div class="space-y-3">
            <h5 class="text-sm font-medium text-gray-300">{{ $t('landing.footer.get_app') }}</h5>
            <div class="space-y-2">
              <a 
                href="#" 
                class="app-download-link"
                aria-label="Download from Play Store"
              >
                <SmartphoneIcon class="w-5 h-5 mr-2" />
                <div>
                  <div class="text-xs text-gray-400">{{ $t('landing.footer.get_on') }}</div>
                  <div class="text-sm font-medium">Google Play</div>
                </div>
              </a>
              
              <a 
                href="#" 
                class="app-download-link"
                aria-label="Download from App Store"
              >
                <SmartphoneIcon class="w-5 h-5 mr-2" />
                <div>
                  <div class="text-xs text-gray-400">{{ $t('landing.footer.download_on') }}</div>
                  <div class="text-sm font-medium">App Store</div>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Newsletter Subscription -->
    <div class="border-t border-gray-800">
      <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto text-center">
          <h3 class="text-xl font-bold mb-4">{{ $t('landing.footer.newsletter_title') }}</h3>
          <p class="text-gray-400 mb-6">{{ $t('landing.footer.newsletter_description') }}</p>
          
          <form @submit.prevent="subscribeNewsletter" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
            <input
              v-model="newsletterEmail"
              type="email"
              :placeholder="$t('landing.footer.email_placeholder')"
              class="newsletter-input flex-1"
              required
              :disabled="isSubscribing"
            />
            <button
              type="submit"
              class="btn-newsletter"
              :disabled="isSubscribing || !newsletterEmail"
            >
              <span v-if="!isSubscribing">{{ $t('landing.footer.subscribe') }}</span>
              <span v-else class="flex items-center">
                <LoaderIcon class="w-4 h-4 mr-2 animate-spin" />
                {{ $t('landing.footer.subscribing') }}
              </span>
            </button>
          </form>
          
          <!-- Newsletter result message -->
          <div v-if="newsletterMessage" class="mt-3 text-sm" :class="newsletterSuccess ? 'text-green-400' : 'text-red-400'">
            {{ newsletterMessage }}
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Footer -->
    <div class="border-t border-gray-800">
      <div class="container mx-auto px-4 py-6">
        <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
          <!-- Copyright -->
          <div class="text-gray-400 text-sm">
            © {{ currentYear }} {{ $t('landing.footer.brand_name') }}. {{ $t('landing.footer.all_rights_reserved') }}
          </div>

          <!-- Legal Links -->
          <div class="flex flex-wrap items-center space-x-6 text-sm">
            <Link
              v-for="legal in legalLinks"
              :key="legal.name"
              :href="legal.href"
              class="footer-link text-sm"
            >
              {{ $t(legal.label) }}
            </Link>
          </div>

          <!-- Language & Region -->
          <div class="flex items-center space-x-3">
            <GlobeIcon class="w-4 h-4 text-gray-400" />
            <LanguageSwitcher variant="footer" />
          </div>
        </div>
      </div>
    </div>

    <!-- Back to Top Button -->
    <button
      v-show="showBackToTop"
      @click="scrollToTop"
      class="back-to-top"
      :aria-label="$t('landing.footer.back_to_top')"
    >
      <ChevronUpIcon class="w-6 h-6" />
    </button>
  </footer>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { 
  BrainIcon,
  MailIcon,
  PhoneIcon,
  MapPinIcon,
  SmartphoneIcon,
  LoaderIcon,
  GlobeIcon,
  ChevronUpIcon,
  FacebookIcon,
  TwitterIcon,
  InstagramIcon,
  YoutubeIcon,
  LinkedinIcon
} from 'lucide-vue-next'
import LanguageSwitcher from '@/Components/Common/LanguageSwitcher.vue'

const { t } = useI18n()

// Newsletter form state
const newsletterEmail = ref('')
const isSubscribing = ref(false)
const newsletterMessage = ref('')
const newsletterSuccess = ref(false)

// Back to top state
const showBackToTop = ref(false)

const currentYear = computed(() => new Date().getFullYear())

const socialLinks = computed(() => [
  {
    name: 'facebook',
    url: 'https://facebook.com/learningplatform',
    label: 'landing.footer.facebook'
  },
  {
    name: 'instagram',
    url: 'https://instagram.com/learningplatform',
    label: 'landing.footer.instagram'
  },
  {
    name: 'twitter',
    url: 'https://twitter.com/learningplatform',
    label: 'landing.footer.twitter'
  },
  {
    name: 'youtube',
    url: 'https://youtube.com/@learningplatform',
    label: 'landing.footer.youtube'
  },
  {
    name: 'linkedin',
    url: 'https://linkedin.com/company/learningplatform',
    label: 'landing.footer.linkedin'
  }
])

const quickLinks = computed(() => [
  {
    name: 'features',
    href: route('features'),
    label: 'landing.footer.features'
  },
  {
    name: 'demo',
    href: route('demo'),
    label: 'landing.footer.demo'
  },
  {
    name: 'about',
    href: route('about'),
    label: 'landing.footer.about'
  },
  {
    name: 'contact',
    href: '#',
    label: 'landing.footer.contact'
  }
])

const educationalResources = computed(() => [
  {
    name: 'study_guides',
    href: '#',
    label: 'landing.footer.study_guides'
  },
  {
    name: 'math_topics',
    href: '#',
    label: 'landing.footer.math_topics'
  },
  {
    name: 'learning_tips',
    href: '#',
    label: 'landing.footer.learning_tips'
  },
  {
    name: 'teacher_resources',
    href: '#',
    label: 'landing.footer.teacher_resources'
  }
])

const legalLinks = computed(() => [
  {
    name: 'privacy',
    href: '#',
    label: 'landing.footer.privacy_policy'
  },
  {
    name: 'terms',
    href: '#',
    label: 'landing.footer.terms_of_service'
  },
  {
    name: 'cookies',
    href: '#',
    label: 'landing.footer.cookie_policy'
  }
])

const getSocialIcon = (name: string) => {
  const iconMap = {
    'facebook': FacebookIcon,
    'instagram': InstagramIcon,
    'twitter': TwitterIcon,
    'youtube': YoutubeIcon,
    'linkedin': LinkedinIcon
  }
  return iconMap[name as keyof typeof iconMap] || FacebookIcon
}

const subscribeNewsletter = async (): Promise<void> => {
  if (!newsletterEmail.value) return

  isSubscribing.value = true
  newsletterMessage.value = ''

  try {
    await router.post(route('newsletter'), {
      email: newsletterEmail.value,
      interests: ['platform_updates', 'educational_content']
    }, {
      onSuccess: () => {
        newsletterSuccess.value = true
        newsletterMessage.value = t('landing.footer.newsletter_success')
        newsletterEmail.value = ''
      },
      onError: (errors) => {
        newsletterSuccess.value = false
        newsletterMessage.value = errors.email || t('landing.footer.newsletter_error')
      },
      onFinish: () => {
        isSubscribing.value = false
      }
    })
  } catch (error) {
    newsletterSuccess.value = false
    newsletterMessage.value = t('landing.footer.newsletter_error')
    isSubscribing.value = false
  }
}

const scrollToTop = (): void => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  })
}

const handleScroll = (): void => {
  showBackToTop.value = window.scrollY > 300
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<style scoped>
.landing-footer {
  position: relative;
}

.social-link {
  @apply w-10 h-10 bg-gray-800 hover:bg-educational-primary rounded-lg flex items-center justify-center transition-all duration-300 transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-educational-primary/50;
}

.footer-link {
  @apply text-gray-400 hover:text-white transition-colors duration-200;
}

.footer-link:hover {
  @apply text-educational-primary;
}

.app-download-link {
  @apply flex items-center space-x-2 text-gray-400 hover:text-white transition-colors duration-200 text-left;
}

.newsletter-input {
  @apply px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-educational-primary focus:border-educational-primary transition-all duration-200;
}

.btn-newsletter {
  @apply px-6 py-3 bg-educational-primary hover:bg-educational-primary/90 text-white font-semibold rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-educational-primary/50 disabled:opacity-50 disabled:cursor-not-allowed;
}

.back-to-top {
  @apply fixed bottom-8 right-8 w-12 h-12 bg-educational-primary hover:bg-educational-primary/90 text-white rounded-full flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-110 focus:outline-none focus:ring-4 focus:ring-educational-primary/25 z-40;
}

/* Mobile responsiveness */
@media (max-width: 640px) {
  .back-to-top {
    @apply bottom-6 right-6 w-10 h-10;
  }
}

/* Smooth scrolling */
html {
  scroll-behavior: smooth;
}
</style>

