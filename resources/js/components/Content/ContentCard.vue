<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import Icon from '@/components/Icon.vue'

const props = defineProps({
  content: {
    type: Object,
    required: true
  },
  showActions: {
    type: Boolean,
    default: true
  },
  showMetrics: {
    type: Boolean,
    default: false
  },
  isRecommended: {
    type: Boolean,
    default: false
  },
  hasCompleted: {
    type: Boolean,
    default: false
  },
  relevanceScore: {
    type: Number,
    default: null
  },
  recommendationReason: {
    type: String,
    default: null
  }
})

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
    return `${hours}j ${minutes}m`
  }
  return `${minutes}m`
})

const ratingStars = computed(() => {
  const rating = props.content.rating || 0
  return Array.from({ length: 5 }, (_, i) => i < Math.floor(rating))
})
</script>

<template>
  <Card class="h-full flex flex-col hover:shadow-lg transition-shadow duration-200">
    <!-- Thumbnail -->
    <div class="relative">
      <img
        v-if="content.thumbnail_url"
        :src="content.thumbnail_url"
        :alt="content.title"
        class="w-full h-48 object-cover rounded-t-lg"
      />
      <div
        v-else
        class="w-full h-48 bg-gradient-to-br from-blue-500 to-purple-600 rounded-t-lg flex items-center justify-center"
      >
        <Icon :name="contentTypeIcon" class="w-16 h-16 text-white" />
      </div>
      
      <!-- Content Type Badge -->
      <Badge class="absolute top-2 left-2 bg-white/90 text-gray-800">
        <Icon :name="contentTypeIcon" class="w-4 h-4 mr-1" />
        {{ content.content_type.toUpperCase() }}
      </Badge>
      
      <!-- Recommended Badge -->
      <Badge
        v-if="isRecommended"
        class="absolute top-2 right-2 bg-yellow-500 text-yellow-900"
      >
        <Icon name="star" class="w-4 h-4 mr-1" />
        Recommended
      </Badge>
      
      <!-- Completed Badge -->
      <Badge
        v-if="hasCompleted"
        class="absolute top-2 right-2 bg-green-500 text-green-900"
      >
        <Icon name="checkCircle" class="w-4 h-4 mr-1" />
        Completed
      </Badge>
    </div>

    <CardHeader class="pb-2">
      <div class="flex items-start justify-between gap-2">
        <CardTitle class="text-lg line-clamp-2 flex-1">
          {{ content.title }}
        </CardTitle>
        <div v-if="relevanceScore" class="text-sm font-medium text-blue-600">
          {{ Math.round(relevanceScore * 100) }}%
        </div>
      </div>
      <CardDescription class="line-clamp-2">
        {{ content.description }}
      </CardDescription>
    </CardHeader>

    <CardContent class="flex-1 pt-0">
      <!-- Topic and Grade -->
      <div class="space-y-2 mb-4">
        <div class="flex items-center gap-2 text-sm text-gray-600">
          <Icon name="book" class="w-4 h-4" />
          {{ content.subject }} • {{ content.topic }}
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-600">
          <Icon name="graduationCap" class="w-4 h-4" />
          Grade {{ content.grade_level }}
        </div>
      </div>

      <!-- Badges -->
      <div class="flex flex-wrap gap-2 mb-4">
        <Badge :class="difficultyColor">
          {{ content.difficulty_level }}
        </Badge>
        <Badge :class="learningStyleColor">
          {{ content.target_learning_style }}
        </Badge>
        <Badge v-if="formattedDuration" variant="outline">
          <Icon name="clock" class="w-3 h-3 mr-1" />
          {{ formattedDuration }}
        </Badge>
      </div>

      <!-- Rating -->
      <div v-if="content.rating > 0" class="flex items-center gap-2 mb-2">
        <div class="flex items-center">
          <Icon
            v-for="(filled, index) in ratingStars"
            :key="index"
            name="star"
            :class="filled ? 'text-yellow-400 fill-current' : 'text-gray-300'"
            class="w-4 h-4"
          />
        </div>
        <span class="text-sm text-gray-600">
          {{ Number(content.rating || 0).toFixed(1) }}
        </span>
      </div>

      <!-- Metrics -->
      <div v-if="showMetrics" class="flex items-center gap-4 text-sm text-gray-600 mb-2">
        <div class="flex items-center gap-1">
          <Icon name="eye" class="w-4 h-4" />
          {{ content.views_count || 0 }}
        </div>
        <div v-if="content.creator" class="flex items-center gap-1">
          <Icon name="user" class="w-4 h-4" />
          {{ content.creator.name }}
        </div>
      </div>

      <!-- Recommendation Reason -->
      <div v-if="recommendationReason" class="text-sm text-blue-600 bg-blue-50 p-2 rounded">
        <Icon name="lightbulb" class="w-4 h-4 inline mr-1" />
        {{ recommendationReason }}
      </div>
    </CardContent>

    <CardFooter v-if="showActions" class="pt-0">
      <div class="flex gap-2 w-full">
        <Button
          as="Link"
          :href="`/student/content/${content.id}`"
          class="flex-1"
          variant="default"
        >
          <Icon name="play" class="w-4 h-4 mr-2" />
          {{ hasCompleted ? 'Review' : 'Start' }}
        </Button>
        
        <Button
          v-if="content.file_url && content.content_type === 'pdf'"
          as="Link"
          :href="`/student/content/${content.id}/download`"
          variant="outline"
          size="sm"
        >
          <Icon name="download" class="w-4 h-4" />
        </Button>
      </div>
    </CardFooter>
  </Card>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>