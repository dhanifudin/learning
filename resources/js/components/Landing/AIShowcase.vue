<template>
  <section id="ai-showcase" class="ai-showcase py-16 lg:py-24 bg-gradient-to-br from-blue-50 via-white to-purple-50">
    <div class="container mx-auto px-4">
      <!-- Section Header -->
      <div class="text-center mb-16">
        <div class="inline-flex items-center bg-educational-primary/10 rounded-full px-6 py-3 mb-6">
          <BrainIcon class="w-6 h-6 text-educational-primary mr-2" />
          <span class="text-lg font-semibold text-educational-primary">
            {{ $t('landing.ai_showcase.badge') }}
          </span>
        </div>
        
        <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
          {{ $t('landing.ai_showcase.title') }}
        </h2>
        
        <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
          {{ $t('landing.ai_showcase.subtitle') }}
        </p>
      </div>

      <!-- AI Workflow Visualization -->
      <div class="max-w-6xl mx-auto">
        <div class="grid lg:grid-cols-3 gap-8 mb-16">
          <!-- Step 1: Assessment -->
          <div class="ai-workflow-step" :class="{ 'active': currentStep >= 1 }">
            <div class="workflow-content">
              <div class="step-number">1</div>
              <div class="step-icon">
                <ClipboardListIcon class="w-8 h-8 text-white" />
              </div>
              <h3 class="text-xl font-bold text-gray-900 mb-3">
                {{ $t('landing.ai_showcase.step_1_title') }}
              </h3>
              <p class="text-gray-600 mb-4">
                {{ $t('landing.ai_showcase.step_1_description') }}
              </p>
              <div class="workflow-animation">
                <div class="assessment-preview">
                  <div class="question-item" v-for="i in 3" :key="i">
                    <div class="question-text"></div>
                    <div class="question-options">
                      <div class="option" v-for="j in 5" :key="j" :class="{ 'selected': j === 4 && i === 1 }"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 2: AI Analysis -->
          <div class="ai-workflow-step" :class="{ 'active': currentStep >= 2 }">
            <div class="workflow-content">
              <div class="step-number">2</div>
              <div class="step-icon">
                <BrainIcon class="w-8 h-8 text-white" />
              </div>
              <h3 class="text-xl font-bold text-gray-900 mb-3">
                {{ $t('landing.ai_showcase.step_2_title') }}
              </h3>
              <p class="text-gray-600 mb-4">
                {{ $t('landing.ai_showcase.step_2_description') }}
              </p>
              <div class="workflow-animation">
                <div class="ai-analysis-preview">
                  <div class="neural-network">
                    <div class="node" v-for="i in 9" :key="i" :class="{ 'active': aiProcessing }"></div>
                  </div>
                  <div class="analysis-result" :class="{ 'visible': currentStep >= 2 }">
                    <div class="learning-style-badge visual">Visual: 85%</div>
                    <div class="learning-style-badge auditory">Auditory: 65%</div>
                    <div class="learning-style-badge kinesthetic">Kinesthetic: 45%</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 3: Personalization -->
          <div class="ai-workflow-step" :class="{ 'active': currentStep >= 3 }">
            <div class="workflow-content">
              <div class="step-number">3</div>
              <div class="step-icon">
                <TargetIcon class="w-8 h-8 text-white" />
              </div>
              <h3 class="text-xl font-bold text-gray-900 mb-3">
                {{ $t('landing.ai_showcase.step_3_title') }}
              </h3>
              <p class="text-gray-600 mb-4">
                {{ $t('landing.ai_showcase.step_3_description') }}
              </p>
              <div class="workflow-animation">
                <div class="personalization-preview">
                  <div class="content-card" :class="{ 'recommended': currentStep >= 3 }">
                    <VideoIcon class="w-6 h-6 text-blue-600" />
                    <span>Video: Trigonometri</span>
                  </div>
                  <div class="content-card" :class="{ 'recommended': currentStep >= 3 }">
                    <BookOpenIcon class="w-6 h-6 text-green-600" />
                    <span>Interaktif: Geometri</span>
                  </div>
                  <div class="content-card" :class="{ 'recommended': currentStep >= 3 }">
                    <ImageIcon class="w-6 h-6 text-purple-600" />
                    <span>Visual: Fungsi</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Interactive Demo Section -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
          <div class="grid lg:grid-cols-2">
            <!-- Demo Controls -->
            <div class="p-8 lg:p-12 bg-gradient-to-br from-educational-primary/5 to-indonesian-red/5">
              <h3 class="text-2xl font-bold text-gray-900 mb-6">
                {{ $t('landing.ai_showcase.demo_title') }}
              </h3>
              
              <div class="space-y-6">
                <!-- Student Profile Selection -->
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-3">
                    {{ $t('landing.ai_showcase.select_profile') }}
                  </label>
                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <button
                      v-for="profile in demoProfiles"
                      :key="profile.id"
                      @click="selectProfile(profile)"
                      class="demo-profile-button"
                      :class="{ 'active': selectedProfile?.id === profile.id }"
                    >
                      <div class="w-8 h-8 rounded-full mx-auto mb-2" :class="profile.color"></div>
                      <div class="text-sm font-medium">{{ profile.name }}</div>
                      <div class="text-xs text-gray-500">{{ profile.style }}</div>
                    </button>
                  </div>
                </div>

                <!-- Demo Action Buttons -->
                <div class="space-y-3">
                  <button
                    @click="runDemo"
                    :disabled="isRunningDemo || !selectedProfile"
                    class="btn-demo-primary w-full"
                  >
                    <span v-if="!isRunningDemo" class="flex items-center justify-center">
                      <PlayIcon class="w-5 h-5 mr-2" />
                      {{ $t('landing.ai_showcase.run_demo') }}
                    </span>
                    <span v-else class="flex items-center justify-center">
                      <LoaderIcon class="w-5 h-5 mr-2 animate-spin" />
                      {{ $t('landing.ai_showcase.analyzing') }}
                    </span>
                  </button>
                  
                  <Link
                    :href="route('demo')"
                    class="btn-demo-secondary w-full inline-flex items-center justify-center"
                  >
                    <ExternalLinkIcon class="w-5 h-5 mr-2" />
                    {{ $t('landing.ai_showcase.full_demo') }}
                  </Link>
                </div>

                <!-- Demo Results -->
                <div v-if="demoResults" class="demo-results">
                  <h4 class="font-semibold text-gray-900 mb-3">
                    {{ $t('landing.ai_showcase.results_title') }}
                  </h4>
                  <div class="space-y-3">
                    <div class="result-item">
                      <span class="result-label">{{ $t('landing.ai_showcase.dominant_style') }}:</span>
                      <span class="result-value" :class="getStyleColor(demoResults.dominant_style)">
                        {{ demoResults.dominant_style }}
                      </span>
                    </div>
                    <div class="result-item">
                      <span class="result-label">{{ $t('landing.ai_showcase.confidence') }}:</span>
                      <span class="result-value">{{ Math.round(demoResults.confidence * 100) }}%</span>
                    </div>
                    <div class="result-item">
                      <span class="result-label">{{ $t('landing.ai_showcase.recommended_content') }}:</span>
                      <span class="result-value">{{ demoResults.content_count }} item</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Live Demo Visualization -->
            <div class="p-8 lg:p-12">
              <h4 class="text-lg font-bold text-gray-900 mb-6">
                {{ $t('landing.ai_showcase.live_analysis') }}
              </h4>
              
              <!-- Learning Style Chart -->
              <div class="mb-8">
                <div class="space-y-4">
                  <div class="chart-item">
                    <div class="flex justify-between items-center mb-2">
                      <span class="text-sm font-medium text-gray-700">Visual</span>
                      <span class="text-sm text-gray-500">{{ demoChartData.visual }}%</span>
                    </div>
                    <div class="progress-bar">
                      <div 
                        class="progress-fill bg-blue-500" 
                        :style="{ width: `${demoChartData.visual}%` }"
                      ></div>
                    </div>
                  </div>
                  
                  <div class="chart-item">
                    <div class="flex justify-between items-center mb-2">
                      <span class="text-sm font-medium text-gray-700">Auditory</span>
                      <span class="text-sm text-gray-500">{{ demoChartData.auditory }}%</span>
                    </div>
                    <div class="progress-bar">
                      <div 
                        class="progress-fill bg-green-500" 
                        :style="{ width: `${demoChartData.auditory}%` }"
                      ></div>
                    </div>
                  </div>
                  
                  <div class="chart-item">
                    <div class="flex justify-between items-center mb-2">
                      <span class="text-sm font-medium text-gray-700">Kinesthetic</span>
                      <span class="text-sm text-gray-500">{{ demoChartData.kinesthetic }}%</span>
                    </div>
                    <div class="progress-bar">
                      <div 
                        class="progress-fill bg-purple-500" 
                        :style="{ width: `${demoChartData.kinesthetic}%` }"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Content Recommendations Preview -->
              <div v-if="demoResults">
                <h5 class="text-md font-bold text-gray-900 mb-4">
                  {{ $t('landing.ai_showcase.content_recommendations') }}
                </h5>
                <div class="space-y-3">
                  <div 
                    v-for="content in demoContentRecommendations" 
                    :key="content.id"
                    class="content-recommendation"
                  >
                    <component :is="content.icon" class="w-5 h-5" :class="content.iconColor" />
                    <div class="flex-1">
                      <div class="font-medium text-sm">{{ content.title }}</div>
                      <div class="text-xs text-gray-500">{{ content.type }}</div>
                    </div>
                    <div class="text-xs font-medium text-educational-primary">
                      {{ content.relevance }}%
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- AI Features Grid -->
      <div class="mt-16 grid md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div 
          v-for="feature in aiFeatures"
          :key="feature.icon"
          class="ai-feature-card"
        >
          <div class="feature-icon-bg">
            <component :is="getFeatureIcon(feature.icon)" class="w-6 h-6 text-white" />
          </div>
          <h4 class="font-bold text-gray-900 mb-2">{{ $t(feature.title) }}</h4>
          <p class="text-gray-600 text-sm">{{ $t(feature.description) }}</p>
        </div>
      </div>
    </div>
  </section>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { 
  BrainIcon,
  ClipboardListIcon,
  TargetIcon,
  VideoIcon,
  BookOpenIcon,
  ImageIcon,
  PlayIcon,
  LoaderIcon,
  ExternalLinkIcon,
  SparklesIcon,
  ZapIcon,
  TrendingUpIcon,
  Shield
} from 'lucide-vue-next'

const { t } = useI18n()

// Animation state
const currentStep = ref(0)
const aiProcessing = ref(false)
const isRunningDemo = ref(false)

// Demo state
const selectedProfile = ref(null)
const demoResults = ref(null)

const demoChartData = ref({
  visual: 0,
  auditory: 0,
  kinesthetic: 0
})

const demoProfiles = computed(() => [
  {
    id: 1,
    name: 'Ahmad',
    style: 'Visual',
    color: 'bg-blue-500',
    data: { visual: 85, auditory: 45, kinesthetic: 30 }
  },
  {
    id: 2,
    name: 'Siti',
    style: 'Auditory',
    color: 'bg-green-500',
    data: { visual: 40, auditory: 80, kinesthetic: 35 }
  },
  {
    id: 3,
    name: 'Budi',
    style: 'Kinesthetic',
    color: 'bg-purple-500',
    data: { visual: 30, auditory: 50, kinesthetic: 85 }
  }
])

const demoContentRecommendations = computed(() => {
  if (!demoResults.value) return []
  
  return [
    {
      id: 1,
      title: 'Video: Trigonometri Dasar',
      type: 'Video Tutorial',
      relevance: 95,
      icon: VideoIcon,
      iconColor: 'text-blue-600'
    },
    {
      id: 2,
      title: 'Simulasi: Grafik Fungsi',
      type: 'Interaktif',
      relevance: 88,
      icon: BookOpenIcon,
      iconColor: 'text-green-600'
    },
    {
      id: 3,
      title: 'Diagram: Geometri Ruang',
      type: 'Visual',
      relevance: 82,
      icon: ImageIcon,
      iconColor: 'text-purple-600'
    }
  ]
})

const aiFeatures = computed(() => [
  {
    icon: 'brain',
    title: 'landing.ai_showcase.feature_1_title',
    description: 'landing.ai_showcase.feature_1_description'
  },
  {
    icon: 'zap',
    title: 'landing.ai_showcase.feature_2_title',
    description: 'landing.ai_showcase.feature_2_description'
  },
  {
    icon: 'trending-up',
    title: 'landing.ai_showcase.feature_3_title',
    description: 'landing.ai_showcase.feature_3_description'
  },
  {
    icon: 'shield',
    title: 'landing.ai_showcase.feature_4_title',
    description: 'landing.ai_showcase.feature_4_description'
  }
])

const selectProfile = (profile) => {
  selectedProfile.value = profile
  demoResults.value = null
  // Reset chart data
  demoChartData.value = { visual: 0, auditory: 0, kinesthetic: 0 }
}

const runDemo = async () => {
  if (!selectedProfile.value) return
  
  isRunningDemo.value = true
  aiProcessing.value = true
  
  // Animate chart data
  const targetData = selectedProfile.value.data
  const duration = 2000
  const steps = 50
  const stepDuration = duration / steps
  
  for (let i = 0; i <= steps; i++) {
    await new Promise(resolve => setTimeout(resolve, stepDuration))
    const progress = i / steps
    
    demoChartData.value = {
      visual: Math.round(targetData.visual * progress),
      auditory: Math.round(targetData.auditory * progress),
      kinesthetic: Math.round(targetData.kinesthetic * progress)
    }
  }
  
  // Set results
  setTimeout(() => {
    const dominantStyle = Object.entries(targetData).reduce((a, b) => 
      targetData[a[0]] > targetData[b[0]] ? a : b
    )[0]
    
    demoResults.value = {
      dominant_style: dominantStyle,
      confidence: 0.92,
      content_count: 12
    }
    
    isRunningDemo.value = false
    aiProcessing.value = false
  }, 500)
}

const getFeatureIcon = (iconName: string) => {
  const iconMap = {
    'brain': BrainIcon,
    'zap': ZapIcon,
    'trending-up': TrendingUpIcon,
    'shield': Shield
  }
  return iconMap[iconName] || BrainIcon
}

const getStyleColor = (style: string) => {
  const colorMap = {
    'visual': 'text-blue-600',
    'auditory': 'text-green-600',
    'kinesthetic': 'text-purple-600'
  }
  return colorMap[style] || 'text-gray-600'
}

// Animation cycle
let animationInterval: number | null = null

const startAnimation = () => {
  animationInterval = setInterval(() => {
    currentStep.value = (currentStep.value + 1) % 4
    if (currentStep.value === 0) {
      currentStep.value = 1
    }
  }, 3000)
}

onMounted(() => {
  setTimeout(() => {
    currentStep.value = 1
  }, 1000)
  
  startAnimation()
})

onUnmounted(() => {
  if (animationInterval) {
    clearInterval(animationInterval)
  }
})
</script>

<style scoped>
.ai-workflow-step {
  @apply relative bg-white rounded-xl p-6 shadow-sm border border-gray-200 transition-all duration-500;
  opacity: 0.6;
  transform: translateY(20px);
}

.ai-workflow-step.active {
  @apply opacity-100 shadow-lg border-educational-primary/20;
  transform: translateY(0);
}

.step-number {
  @apply absolute -top-3 -left-3 w-8 h-8 bg-educational-primary text-white rounded-full flex items-center justify-center text-sm font-bold;
}

.step-icon {
  @apply w-16 h-16 bg-gradient-to-br from-educational-primary to-indonesian-red rounded-xl flex items-center justify-center mb-4;
}

.workflow-animation {
  @apply mt-4 p-4 bg-gray-50 rounded-lg;
}

.assessment-preview .question-item {
  @apply mb-3 last:mb-0;
}

.question-text {
  @apply h-3 bg-gray-300 rounded mb-2;
}

.question-options {
  @apply flex space-x-2;
}

.option {
  @apply w-6 h-6 bg-gray-200 rounded-full transition-all duration-300;
}

.option.selected {
  @apply bg-educational-primary;
}

.neural-network {
  @apply grid grid-cols-3 gap-2 mb-4;
}

.node {
  @apply w-4 h-4 bg-gray-300 rounded-full transition-all duration-300;
}

.node.active {
  @apply bg-educational-primary animate-pulse;
}

.learning-style-badge {
  @apply inline-block px-2 py-1 text-xs font-medium rounded-full mb-1 mr-2;
  opacity: 0;
  transition: all 0.5s ease;
}

.learning-style-badge.visual {
  @apply bg-blue-100 text-blue-800;
}

.learning-style-badge.auditory {
  @apply bg-green-100 text-green-800;
}

.learning-style-badge.kinesthetic {
  @apply bg-purple-100 text-purple-800;
}

.analysis-result.visible .learning-style-badge {
  opacity: 1;
}

.content-card {
  @apply flex items-center space-x-2 p-2 bg-gray-100 rounded-lg mb-2 transition-all duration-300;
  opacity: 0.5;
}

.content-card.recommended {
  @apply bg-educational-primary/10 border border-educational-primary/20;
  opacity: 1;
}

.demo-profile-button {
  @apply p-3 text-center bg-white border border-gray-200 rounded-lg hover:border-educational-primary hover:shadow-sm transition-all duration-200;
}

.demo-profile-button.active {
  @apply border-educational-primary bg-educational-primary/5;
}

.btn-demo-primary {
  @apply px-6 py-3 bg-educational-primary hover:bg-educational-primary/90 text-white font-semibold rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-educational-primary/50 disabled:opacity-50 disabled:cursor-not-allowed;
}

.btn-demo-secondary {
  @apply px-6 py-3 bg-white border border-educational-primary text-educational-primary hover:bg-educational-primary hover:text-white font-semibold rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-educational-primary/50;
}

.demo-results {
  @apply p-4 bg-educational-primary/5 rounded-lg border border-educational-primary/20;
}

.result-item {
  @apply flex justify-between items-center py-1;
}

.result-label {
  @apply text-sm text-gray-600;
}

.result-value {
  @apply text-sm font-semibold;
}

.progress-bar {
  @apply w-full bg-gray-200 rounded-full h-2;
}

.progress-fill {
  @apply h-2 rounded-full transition-all duration-1000 ease-out;
}

.content-recommendation {
  @apply flex items-center space-x-3 p-3 bg-gray-50 rounded-lg;
}

.ai-feature-card {
  @apply p-6 bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300;
}

.feature-icon-bg {
  @apply w-12 h-12 bg-gradient-to-br from-educational-primary to-indonesian-red rounded-lg flex items-center justify-center mb-4;
}
</style>

