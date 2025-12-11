<script setup>
import { computed } from 'vue'
import ContentCard from './ContentCard.vue'

const props = defineProps({
  content: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  showActions: {
    type: Boolean,
    default: true
  },
  showMetrics: {
    type: Boolean,
    default: false
  },
  recommendations: {
    type: Array,
    default: () => []
  },
  completedContent: {
    type: Array,
    default: () => []
  },
  emptyMessage: {
    type: String,
    default: 'No content available'
  },
  emptyDescription: {
    type: String,
    default: 'Try adjusting your filters or search criteria'
  }
})

const contentWithMetadata = computed(() => {
  return props.content.map(content => {
    const recommendation = props.recommendations.find(rec => rec.content_id === content.id)
    const isCompleted = props.completedContent.includes(content.id)
    
    return {
      ...content,
      isRecommended: !!recommendation,
      hasCompleted: isCompleted,
      relevanceScore: recommendation?.relevance_score,
      recommendationReason: recommendation?.reason
    }
  })
})
</script>

<template>
  <div>
    <!-- Loading State -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <div
        v-for="n in 8"
        :key="n"
        class="animate-pulse"
      >
        <div class="bg-gray-200 h-48 rounded-t-lg"></div>
        <div class="p-4 space-y-2">
          <div class="h-4 bg-gray-200 rounded"></div>
          <div class="h-4 bg-gray-200 rounded w-3/4"></div>
          <div class="h-3 bg-gray-200 rounded w-1/2"></div>
        </div>
      </div>
    </div>

    <!-- Content Grid -->
    <div v-else-if="content.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <ContentCard
        v-for="content in contentWithMetadata"
        :key="content.id"
        :content="content"
        :show-actions="showActions"
        :show-metrics="showMetrics"
        :is-recommended="content.isRecommended"
        :has-completed="content.hasCompleted"
        :relevance-score="content.relevanceScore"
        :recommendation-reason="content.recommendationReason"
      />
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">
        {{ emptyMessage }}
      </h3>
      <p class="text-gray-500 max-w-md mx-auto">
        {{ emptyDescription }}
      </p>
    </div>
  </div>
</template>