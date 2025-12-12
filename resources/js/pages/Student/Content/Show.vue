<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import Heading from '@/components/Heading.vue'
import Icon from '@/components/Icon.vue'

const props = defineProps({
  content: {
    type: Object,
    required: true
  },
  relatedContent: {
    type: Array,
    default: () => []
  },
  isRecommended: {
    type: Boolean,
    default: false
  },
  hasCompleted: {
    type: Boolean,
    default: false
  }
})

const isCompleting = ref(false)

const contentTypeIcon = computed(() => {
  const icons = {
    video: 'playCircle',
    pdf: 'fileText',
    audio: 'volume2',
    interactive: 'monitor',
    text: 'bookOpen'
  }
  return icons[props.content.content_type] || 'file'
})

const difficultyColor = computed(() => {
  const colors = {
    beginner: 'bg-green-100 text-green-800',
    intermediate: 'bg-yellow-100 text-yellow-800',
    advanced: 'bg-red-100 text-red-800'
  }
  return colors[props.content.difficulty_level] || 'bg-gray-100 text-gray-800'
})

const learningStyleColor = computed(() => {
  const colors = {
    visual: 'bg-blue-100 text-blue-800',
    auditory: 'bg-purple-100 text-purple-800',
    kinesthetic: 'bg-orange-100 text-orange-800',
    all: 'bg-gray-100 text-gray-800'
  }
  return colors[props.content.target_learning_style] || 'bg-gray-100 text-gray-800'
})

const formattedDuration = computed(() => {
  if (!props.content.duration_minutes) return null
  
  const hours = Math.floor(props.content.duration_minutes / 60)
  const minutes = props.content.duration_minutes % 60
  
  if (hours > 0) {
    return `${hours}h ${minutes}m`
  }
  return `${minutes}m`
})

const ratingStars = computed(() => {
  const rating = props.content.rating || 0
  return Array.from({ length: 5 }, (_, i) => i < Math.floor(rating))
})

const markAsComplete = () => {
  if (isCompleting.value) return
  
  isCompleting.value = true
  
  router.post(`/dashboard/${props.content.id}/complete`, {}, {
    preserveState: true,
    onSuccess: () => {
      isCompleting.value = false
    },
    onError: () => {
      isCompleting.value = false
    }
  })
}

const downloadContent = () => {
  window.open(`/dashboard/${props.content.id}/download`, '_blank')
}

const startContent = () => {
  // Track interaction
  router.post(`/dashboard/${props.content.id}/track`, {
    action: 'view',
    timestamp: Date.now(),
    metadata: {
      referrer: 'content_page'
    }
  }, {
    preserveState: true
  })
  
  // Open external content or navigate to content
  if (props.content.external_url) {
    window.open(props.content.external_url, '_blank')
  } else if (props.content.file_url) {
    window.open(props.content.file_url, '_blank')
  }
}
</script>

<template>
  <Head :title="content.title" />

  <AppLayout>
    <div class="space-y-6">
      <!-- Navigation -->
      <div class="flex items-center gap-2 text-sm text-gray-600">
        <Link href="/dashboard" class="hover:text-gray-900 transition-colors">
          Dashboard
        </Link>
        <Icon name="chevronRight" class="w-4 h-4" />
        <span class="text-gray-900">{{ content.title }}</span>
      </div>

      <!-- Header -->
      <div class="flex items-start justify-between gap-6">
        <div class="flex-1">
          <div class="flex items-center gap-3 mb-2">
            <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <Icon :name="contentTypeIcon" class="w-6 h-6 text-blue-600" />
            </div>
            <div>
              <Heading class="mb-1">{{ content.title }}</Heading>
              <p class="text-gray-600">{{ content.subject }} • {{ content.topic }}</p>
            </div>
          </div>
          
          <div class="flex items-center gap-4 mb-4">
            <Badge :class="difficultyColor">
              {{ content.difficulty_level }}
            </Badge>
            <Badge :class="learningStyleColor">
              {{ content.target_learning_style }}
            </Badge>
            <Badge v-if="formattedDuration" variant="outline">
              <Icon name="clock" class="w-4 h-4 mr-1" />
              {{ formattedDuration }}
            </Badge>
            <Badge v-if="isRecommended" class="bg-yellow-100 text-yellow-800">
              <Icon name="star" class="w-4 h-4 mr-1" />
              Recommended for you
            </Badge>
            <Badge v-if="hasCompleted" class="bg-green-100 text-green-800">
              <Icon name="checkCircle" class="w-4 h-4 mr-1" />
              Completed
            </Badge>
          </div>

          <p class="text-gray-700 leading-relaxed mb-6">
            {{ content.description }}
          </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col gap-3 min-w-[200px]">
          <Button
            @click="startContent"
            class="w-full"
            size="lg"
          >
            <Icon :name="contentTypeIcon" class="w-5 h-5 mr-2" />
            {{ hasCompleted ? 'Review Content' : 'Start Learning' }}
          </Button>
          
          <Button
            v-if="content.file_url && content.content_type === 'pdf'"
            @click="downloadContent"
            variant="outline"
            class="w-full"
          >
            <Icon name="download" class="w-4 h-4 mr-2" />
            Download
          </Button>
          
          <Button
            v-if="!hasCompleted"
            @click="markAsComplete"
            :disabled="isCompleting"
            variant="outline"
            class="w-full"
          >
            <Icon v-if="isCompleting" name="loader2" class="w-4 h-4 mr-2 animate-spin" />
            <Icon v-else name="checkCircle" class="w-4 h-4 mr-2" />
            {{ isCompleting ? 'Marking Complete...' : 'Mark as Complete' }}
          </Button>
        </div>
      </div>

      <!-- Content Details -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content Area -->
        <div class="lg:col-span-2">
          <Card>
            <CardHeader>
              <CardTitle>About This Content</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div>
                <h4 class="font-medium text-gray-900 mb-2">Learning Objectives</h4>
                <ul class="text-gray-600 space-y-1">
                  <li>• Understand core concepts of {{ content.topic }}</li>
                  <li>• Apply knowledge through practical examples</li>
                  <li>• Master problem-solving techniques</li>
                  <li v-if="content.difficulty_level === 'advanced'">• Connect concepts to real-world applications</li>
                </ul>
              </div>

              <div v-if="content.rating > 0">
                <h4 class="font-medium text-gray-900 mb-2">Rating</h4>
                <div class="flex items-center gap-2">
                  <div class="flex items-center">
                    <Icon
                      v-for="(filled, index) in ratingStars"
                      :key="index"
                      name="star"
                      :class="filled ? 'text-yellow-400 fill-current' : 'text-gray-300'"
                      class="w-5 h-5"
                    />
                  </div>
                  <span class="text-gray-600">
                    {{ Number(content.rating || 0).toFixed(1) }} out of 5
                  </span>
                </div>
              </div>

              <div v-if="content.creator">
                <h4 class="font-medium text-gray-900 mb-2">Created by</h4>
                <p class="text-gray-600">{{ content.creator.name }}</p>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Quick Info -->
          <Card>
            <CardHeader>
              <CardTitle class="text-lg">Quick Info</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div class="flex items-center gap-3">
                <Icon name="graduationCap" class="w-5 h-5 text-gray-500" />
                <div>
                  <p class="font-medium">Grade Level</p>
                  <p class="text-sm text-gray-600">Grade {{ content.grade_level }}</p>
                </div>
              </div>
              
              <div class="flex items-center gap-3">
                <Icon name="book" class="w-5 h-5 text-gray-500" />
                <div>
                  <p class="font-medium">Subject</p>
                  <p class="text-sm text-gray-600">{{ content.subject }}</p>
                </div>
              </div>
              
              <div class="flex items-center gap-3">
                <Icon name="target" class="w-5 h-5 text-gray-500" />
                <div>
                  <p class="font-medium">Topic</p>
                  <p class="text-sm text-gray-600">{{ content.topic }}</p>
                </div>
              </div>
              
              <div class="flex items-center gap-3">
                <Icon name="brain" class="w-5 h-5 text-gray-500" />
                <div>
                  <p class="font-medium">Learning Style</p>
                  <p class="text-sm text-gray-600 capitalize">{{ content.target_learning_style }}</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Related Content -->
          <Card v-if="relatedContent.length > 0">
            <CardHeader>
              <CardTitle class="text-lg">Related Content</CardTitle>
              <CardDescription>More content on {{ content.topic }}</CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
              <Link
                v-for="related in relatedContent"
                :key="related.id"
                :href="`/dashboard/${related.id}`"
                class="block p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
              >
                <div class="flex items-start gap-3">
                  <div class="h-8 w-8 bg-blue-100 rounded flex items-center justify-center flex-shrink-0">
                    <Icon :name="contentTypeIcon" class="w-4 h-4 text-blue-600" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <h4 class="font-medium text-gray-900 truncate">{{ related.title }}</h4>
                    <p class="text-sm text-gray-500 truncate">{{ related.content_type }}</p>
                  </div>
                </div>
              </Link>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>