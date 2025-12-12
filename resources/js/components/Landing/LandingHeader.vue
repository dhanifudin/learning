<template>
  <header class="landing-header bg-white/95 backdrop-blur-md border-b border-gray-200/50 sticky top-0 z-50">
    <nav class="container mx-auto px-4 py-4" role="navigation" :aria-label="$t('landing.nav.main_navigation')">
      <div class="flex items-center justify-between">
        <!-- Logo -->
        <Link 
          :href="route('home')" 
          class="flex items-center space-x-3 group"
          :aria-label="$t('landing.nav.home_link')"
        >
          <div class="w-10 h-10 bg-gradient-to-br from-educational-primary to-indonesian-red rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
            <BrainIcon class="w-6 h-6 text-white" />
          </div>
          <div class="text-xl font-bold text-gray-900">
            {{ $t('landing.nav.brand_name') }}
          </div>
        </Link>

        <!-- Desktop Navigation -->
        <div class="hidden lg:flex items-center space-x-8">
          <Link
            v-for="item in navigationItems"
            :key="item.name"
            :href="item.href"
            class="nav-link"
            :class="{ 'nav-link-active': isActive(item.href) }"
          >
            {{ $t(item.label) }}
          </Link>
        </div>

        <!-- Desktop CTA Buttons -->
        <div class="hidden lg:flex items-center space-x-4">
          <!-- Language Switcher -->
          <LanguageSwitcher />
          
          <!-- Login Button -->
          <Link
            :href="route('login')"
            class="text-gray-600 hover:text-educational-primary font-medium transition-colors"
          >
            {{ $t('landing.nav.login') }}
          </Link>
          
          <!-- Register Button -->
          <Link
            v-if="canRegister"
            :href="route('register')"
            class="btn-primary"
          >
            <UserPlusIcon class="w-5 h-5 mr-2" />
            {{ $t('landing.nav.register_free') }}
          </Link>
        </div>

        <!-- Mobile Menu Button -->
        <button
          class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors"
          @click="toggleMobileMenu"
          :aria-expanded="mobileMenuOpen"
          :aria-label="$t('landing.nav.toggle_menu')"
        >
          <MenuIcon v-if="!mobileMenuOpen" class="w-6 h-6 text-gray-600" />
          <XIcon v-else class="w-6 h-6 text-gray-600" />
        </button>
      </div>

      <!-- Mobile Menu -->
      <Transition name="mobile-menu">
        <div 
          v-if="mobileMenuOpen"
          class="lg:hidden mt-4 pb-4 border-t border-gray-200"
        >
          <div class="space-y-3 mt-4">
            <Link
              v-for="item in navigationItems"
              :key="item.name"
              :href="item.href"
              class="block px-3 py-2 text-gray-600 hover:text-educational-primary hover:bg-gray-50 rounded-lg transition-all"
              @click="closeMobileMenu"
            >
              {{ $t(item.label) }}
            </Link>
          </div>
          
          <div class="border-t border-gray-200 mt-4 pt-4 space-y-3">
            <!-- Language Switcher Mobile -->
            <div class="px-3">
              <LanguageSwitcher />
            </div>
            
            <!-- Login/Register Mobile -->
            <Link
              :href="route('login')"
              class="block w-full text-center px-4 py-2 text-gray-600 hover:text-educational-primary border border-gray-300 rounded-lg transition-colors"
              @click="closeMobileMenu"
            >
              {{ $t('landing.nav.login') }}
            </Link>
            
            <Link
              v-if="canRegister"
              :href="route('register')"
              class="block w-full text-center btn-primary"
              @click="closeMobileMenu"
            >
              <UserPlusIcon class="w-5 h-5 mr-2 inline" />
              {{ $t('landing.nav.register_free') }}
            </Link>
          </div>
        </div>
      </Transition>
    </nav>
  </header>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { 
  BrainIcon,
  UserPlusIcon,
  MenuIcon,
  XIcon
} from 'lucide-vue-next'
import LanguageSwitcher from '@/Components/Common/LanguageSwitcher.vue'

interface Props {
  canRegister?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  canRegister: true
})

const { t } = useI18n()
const page = usePage()
const mobileMenuOpen = ref(false)

const navigationItems = computed(() => [
  {
    name: 'features',
    label: 'landing.nav.features',
    href: route('features')
  },
  {
    name: 'demo',
    label: 'landing.nav.demo',
    href: route('demo')
  },
  {
    name: 'about',
    label: 'landing.nav.about',
    href: route('about')
  }
])

const isActive = (href: string): boolean => {
  return page.url === href
}

const toggleMobileMenu = (): void => {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

const closeMobileMenu = (): void => {
  mobileMenuOpen.value = false
}

// Close mobile menu on resize
const handleResize = (): void => {
  if (window.innerWidth >= 1024) {
    mobileMenuOpen.value = false
  }
}

onMounted(() => {
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})
</script>

<style scoped>
.landing-header {
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

.nav-link {
  @apply text-gray-600 hover:text-educational-primary font-medium transition-colors relative;
}

.nav-link:hover::after {
  content: '';
  @apply absolute bottom-0 left-0 w-full h-0.5 bg-educational-primary transform scale-x-100 transition-transform;
}

.nav-link::after {
  content: '';
  @apply absolute bottom-0 left-0 w-full h-0.5 bg-educational-primary transform scale-x-0 transition-transform;
}

.nav-link-active {
  @apply text-educational-primary;
}

.nav-link-active::after {
  @apply transform scale-x-100;
}

.btn-primary {
  @apply inline-flex items-center px-6 py-2 bg-educational-primary hover:bg-educational-primary/90 text-white font-semibold rounded-lg shadow-sm hover:shadow-md transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-educational-primary/25;
}

/* Mobile menu transitions */
.mobile-menu-enter-active,
.mobile-menu-leave-active {
  transition: all 0.3s ease;
}

.mobile-menu-enter-from,
.mobile-menu-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.mobile-menu-enter-to,
.mobile-menu-leave-from {
  opacity: 1;
  transform: translateY(0);
}

/* Ensure smooth scrolling for anchor links */
@media (prefers-reduced-motion: no-preference) {
  .nav-link[href*="#"] {
    scroll-behavior: smooth;
  }
}
</style>

