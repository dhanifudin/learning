<template>
  <section id="statistics" class="statistics-section py-16 lg:py-24 bg-gradient-to-r from-educational-primary via-blue-600 to-indonesian-red">
    <div class="container mx-auto px-4">
      <!-- Section Header -->
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
          {{ $t('landing.statistics.title') }}
        </h2>
        <p class="text-xl text-blue-100 max-w-3xl mx-auto leading-relaxed">
          {{ $t('landing.statistics.subtitle') }}
        </p>
      </div>

      <!-- Main Statistics Grid -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
        <div 
          v-for="(stat, index) in mainStatistics"
          :key="stat.key"
          class="stat-card"
          :style="{ animationDelay: `${index * 200}ms` }"
        >
          <div class="stat-icon">
            <component :is="getStatIcon(stat.icon)" class="w-8 h-8 text-white" />
          </div>
          <div class="stat-number" ref="statNumbers">
            {{ formatStatValue(stat.key) }}
          </div>
          <div class="stat-label">
            {{ $t(stat.label) }}
          </div>
        </div>
      </div>

      <!-- Detailed Statistics -->
      <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8 lg:p-12 border border-white/20">
        <div class="grid lg:grid-cols-2 gap-12">
          <!-- Performance Metrics -->
          <div>
            <h3 class="text-2xl font-bold text-white mb-8">
              {{ $t('landing.statistics.performance_title') }}
            </h3>
            
            <div class="space-y-6">
              <div 
                v-for="metric in performanceMetrics"
                :key="metric.key"
                class="performance-metric"
              >
                <div class="flex justify-between items-center mb-2">
                  <span class="text-blue-100 font-medium">{{ $t(metric.label) }}</span>
                  <span class="text-white font-bold">{{ metric.value }}{{ metric.suffix }}</span>
                </div>
                <div class="progress-bar-bg">
                  <div 
                    class="progress-bar-fill"
                    :style="{ width: `${metric.percentage}%` }"
                  ></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Usage Statistics -->
          <div>
            <h3 class="text-2xl font-bold text-white mb-8">
              {{ $t('landing.statistics.usage_title') }}
            </h3>
            
            <div class="grid grid-cols-2 gap-6">
              <div 
                v-for="usage in usageStatistics"
                :key="usage.key"
                class="usage-stat"
              >
                <div class="usage-icon">
                  <component :is="getUsageIcon(usage.icon)" class="w-6 h-6 text-white" />
                </div>
                <div class="usage-number">{{ usage.value }}{{ usage.suffix }}</div>
                <div class="usage-label">{{ $t(usage.label) }}</div>
              </div>
            </div>

            <!-- Growth Chart Visualization -->
            <div class="mt-8 p-4 bg-white/5 rounded-xl">
              <h4 class="text-lg font-semibold text-white mb-4">
                {{ $t('landing.statistics.growth_chart') }}
              </h4>
              
              <div class="growth-chart">
                <div 
                  v-for="(month, index) in growthData"
                  :key="index"
                  class="chart-bar"
                  :style="{ height: `${(month.value / Math.max(...growthData.map(m => m.value))) * 100}%` }"
                >
                  <div class="chart-tooltip">
                    <div class="tooltip-month">{{ month.month }}</div>
                    <div class="tooltip-value">{{ formatNumber(month.value) }} siswa</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Achievement Badges -->
      <div class="mt-16">
        <h3 class="text-2xl font-bold text-white text-center mb-8">
          {{ $t('landing.statistics.achievements_title') }}
        </h3>
        
        <div class="grid md:grid-cols-3 lg:grid-cols-5 gap-6">
          <div 
            v-for="achievement in achievements"
            :key="achievement.id"
            class="achievement-badge"
          >
            <div class="badge-icon">
              <component :is="getAchievementIcon(achievement.icon)" class="w-8 h-8 text-yellow-400" />
            </div>
            <div class="badge-title">{{ $t(achievement.title) }}</div>
            <div class="badge-description">{{ $t(achievement.description) }}</div>
          </div>
        </div>
      </div>

      <!-- Real-time Indicators -->
      <div class="mt-12 text-center">
        <div class="inline-flex items-center bg-white/20 backdrop-blur-sm rounded-full px-6 py-3 border border-white/30">
          <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse mr-3"></div>
          <span class="text-white font-medium">
            {{ $t('landing.statistics.real_time') }}: {{ realtimeUsers }} {{ $t('landing.statistics.active_now') }}
          </span>
        </div>
      </div>
    </div>
  </section>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { 
  UsersIcon,
  GraduationCapIcon,
  BookOpenIcon,
  TrendingUpIcon,
  ClockIcon,
  PlayIcon,
  CheckCircleIcon,
  StarIcon,
  AwardIcon,
  TrophyIcon,
  ShieldIcon,
  HeartIcon,
  ZapIcon
} from 'lucide-vue-next'

interface Props {
  statistics: {
    active_students: number
    partner_schools: number
    content_items: number
    assessments_completed: number
    average_improvement: number
    teacher_satisfaction: number
    student_engagement: number
    success_rate: number
  }
}

const props = defineProps<Props>()
const { t } = useI18n()

// Animated counters
const animatedStats = ref({
  active_students: 0,
  partner_schools: 0,
  content_items: 0,
  assessments_completed: 0
})

// Real-time users simulation
const realtimeUsers = ref(0)

const mainStatistics = computed(() => [
  {
    key: 'active_students',
    icon: 'users',
    label: 'landing.statistics.active_students'
  },
  {
    key: 'partner_schools',
    icon: 'graduation',
    label: 'landing.statistics.partner_schools'
  },
  {
    key: 'content_items',
    icon: 'book',
    label: 'landing.statistics.content_items'
  },
  {
    key: 'assessments_completed',
    icon: 'trending',
    label: 'landing.statistics.assessments_completed'
  }
])

const performanceMetrics = computed(() => [
  {
    key: 'average_improvement',
    value: props.statistics.average_improvement,
    suffix: '%',
    percentage: (props.statistics.average_improvement / 50) * 100,
    label: 'landing.statistics.avg_improvement'
  },
  {
    key: 'teacher_satisfaction',
    value: props.statistics.teacher_satisfaction,
    suffix: '%',
    percentage: props.statistics.teacher_satisfaction,
    label: 'landing.statistics.teacher_satisfaction'
  },
  {
    key: 'student_engagement',
    value: props.statistics.student_engagement,
    suffix: '%',
    percentage: props.statistics.student_engagement,
    label: 'landing.statistics.student_engagement'
  },
  {
    key: 'success_rate',
    value: props.statistics.success_rate,
    suffix: '%',
    percentage: props.statistics.success_rate,
    label: 'landing.statistics.success_rate'
  }
])

const usageStatistics = computed(() => [
  {
    key: 'daily_hours',
    value: '2.5',
    suffix: 'h',
    icon: 'clock',
    label: 'landing.statistics.daily_hours'
  },
  {
    key: 'videos_watched',
    value: '50K',
    suffix: '+',
    icon: 'play',
    label: 'landing.statistics.videos_watched'
  },
  {
    key: 'lessons_completed',
    value: '125K',
    suffix: '+',
    icon: 'check',
    label: 'landing.statistics.lessons_completed'
  },
  {
    key: 'perfect_scores',
    value: '12K',
    suffix: '+',
    icon: 'star',
    label: 'landing.statistics.perfect_scores'
  }
])

const growthData = computed(() => [
  { month: 'Jan', value: 5000 },
  { month: 'Feb', value: 8000 },
  { month: 'Mar', value: 12000 },
  { month: 'Apr', value: 15000 },
  { month: 'May', value: 20000 },
  { month: 'Jun', value: 25000 }
])

const achievements = computed(() => [
  {
    id: 1,
    icon: 'award',
    title: 'landing.statistics.achievement_1_title',
    description: 'landing.statistics.achievement_1_desc'
  },
  {
    id: 2,
    icon: 'trophy',
    title: 'landing.statistics.achievement_2_title',
    description: 'landing.statistics.achievement_2_desc'
  },
  {
    id: 3,
    icon: 'shield',
    title: 'landing.statistics.achievement_3_title',
    description: 'landing.statistics.achievement_3_desc'
  },
  {
    id: 4,
    icon: 'heart',
    title: 'landing.statistics.achievement_4_title',
    description: 'landing.statistics.achievement_4_desc'
  },
  {
    id: 5,
    icon: 'zap',
    title: 'landing.statistics.achievement_5_title',
    description: 'landing.statistics.achievement_5_desc'
  }
])

const formatStatValue = (key: string): string => {
  const value = animatedStats.value[key as keyof typeof animatedStats.value]
  
  switch (key) {
    case 'active_students':
      return formatNumber(value) + '+'
    case 'partner_schools':
      return formatNumber(value) + '+'
    case 'content_items':
      return formatNumber(value) + '+'
    case 'assessments_completed':
      return formatLargeNumber(value) + '+'
    default:
      return formatNumber(value)
  }
}

const formatNumber = (num: number): string => {
  return new Intl.NumberFormat('id-ID').format(num)
}

const formatLargeNumber = (num: number): string => {
  if (num >= 1000000) {
    return (num / 1000000).toFixed(1) + 'M'
  } else if (num >= 1000) {
    return Math.round(num / 1000) + 'K'
  }
  return formatNumber(num)
}

const getStatIcon = (iconName: string) => {
  const iconMap = {
    'users': UsersIcon,
    'graduation': GraduationCapIcon,
    'book': BookOpenIcon,
    'trending': TrendingUpIcon
  }
  return iconMap[iconName as keyof typeof iconMap] || UsersIcon
}

const getUsageIcon = (iconName: string) => {
  const iconMap = {
    'clock': ClockIcon,
    'play': PlayIcon,
    'check': CheckCircleIcon,
    'star': StarIcon
  }
  return iconMap[iconName as keyof typeof iconMap] || ClockIcon
}

const getAchievementIcon = (iconName: string) => {
  const iconMap = {
    'award': AwardIcon,
    'trophy': TrophyIcon,
    'shield': ShieldIcon,
    'heart': HeartIcon,
    'zap': ZapIcon
  }
  return iconMap[iconName as keyof typeof iconMap] || AwardIcon
}

const animateCounters = () => {
  const duration = 2000
  const steps = 60
  const stepDuration = duration / steps
  
  const targets = {
    active_students: props.statistics.active_students,
    partner_schools: props.statistics.partner_schools,
    content_items: props.statistics.content_items,
    assessments_completed: props.statistics.assessments_completed
  }
  
  let currentStep = 0
  
  const timer = setInterval(() => {
    currentStep++
    const progress = currentStep / steps
    
    Object.keys(targets).forEach(key => {
      const target = targets[key as keyof typeof targets]
      const current = Math.round(target * progress)
      animatedStats.value[key as keyof typeof animatedStats.value] = current
    })
    
    if (currentStep >= steps) {
      clearInterval(timer)
    }
  }, stepDuration)
}

const updateRealtimeUsers = () => {
  // Simulate real-time user count fluctuation
  const base = 150
  const variation = Math.floor(Math.random() * 20) - 10
  realtimeUsers.value = Math.max(base + variation, 100)
}

// Intersection Observer for animations
let observer: IntersectionObserver | null = null
let realtimeInterval: number | null = null

onMounted(() => {
  // Set up intersection observer for counter animation
  if ('IntersectionObserver' in window) {
    observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting && entry.target.classList.contains('statistics-section')) {
            animateCounters()
            observer?.unobserve(entry.target)
          }
        })
      },
      { threshold: 0.3 }
    )
    
    const section = document.querySelector('.statistics-section')
    if (section) {
      observer.observe(section)
    }
  }
  
  // Start real-time user count updates
  updateRealtimeUsers()
  realtimeInterval = setInterval(updateRealtimeUsers, 5000)
})

onUnmounted(() => {
  observer?.disconnect()
  if (realtimeInterval) {
    clearInterval(realtimeInterval)
  }
})
</script>

<style scoped>
.statistics-section {
  position: relative;
  overflow: hidden;
}

.stat-card {
  @apply text-center text-white opacity-0;
  animation: fadeInUp 0.8s ease-out forwards;
}

.stat-icon {
  @apply w-16 h-16 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center mx-auto mb-4;
}

.stat-number {
  @apply text-3xl md:text-4xl lg:text-5xl font-bold mb-2;
}

.stat-label {
  @apply text-blue-100 font-medium;
}

.performance-metric {
  @apply space-y-2;
}

.progress-bar-bg {
  @apply w-full bg-white/20 rounded-full h-3;
}

.progress-bar-fill {
  @apply h-3 bg-gradient-to-r from-yellow-400 to-learning-success rounded-full transition-all duration-1000 ease-out;
}

.usage-stat {
  @apply text-center p-4 bg-white/10 rounded-xl border border-white/20;
}

.usage-icon {
  @apply w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center mx-auto mb-3;
}

.usage-number {
  @apply text-2xl font-bold text-white mb-1;
}

.usage-label {
  @apply text-blue-200 text-sm;
}

.growth-chart {
  @apply flex items-end justify-between space-x-2 h-20;
}

.chart-bar {
  @apply flex-1 bg-gradient-to-t from-yellow-400 to-learning-success rounded-t-md relative cursor-pointer transition-all duration-300 hover:opacity-80 min-h-[10px];
}

.chart-tooltip {
  @apply absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 bg-gray-900 text-white text-xs rounded-lg px-2 py-1 opacity-0 transition-opacity duration-200;
}

.chart-bar:hover .chart-tooltip {
  @apply opacity-100;
}

.tooltip-month {
  @apply font-medium;
}

.tooltip-value {
  @apply text-gray-300;
}

.achievement-badge {
  @apply text-center p-4 bg-white/10 backdrop-blur-sm rounded-xl border border-white/20 hover:bg-white/15 transition-all duration-300;
}

.badge-icon {
  @apply w-16 h-16 bg-white/20 rounded-xl flex items-center justify-center mx-auto mb-3;
}

.badge-title {
  @apply text-white font-semibold mb-2;
}

.badge-description {
  @apply text-blue-200 text-sm;
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

/* Responsive adjustments */
@media (max-width: 768px) {
  .stat-number {
    @apply text-2xl md:text-3xl;
  }
  
  .growth-chart {
    @apply h-16;
  }
}
</style>

