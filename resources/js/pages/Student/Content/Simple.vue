<script setup>
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  content: Object,
  filters: Object,
  statistics: Object,
  subjects: Array,
  contentTypes: Array,
  difficultyLevels: Array,
  recommendations: Array,
  completedContent: Array
})
</script>

<template>
  <Head title="Content Library" />

  <AppLayout>
    <div class="space-y-6">
      <h1 class="text-3xl font-bold">Content Library (Simple)</h1>
      
      <!-- Debug Information -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-4">Debug Info</h2>
        <div class="space-y-2">
          <p><strong>Total Content:</strong> {{ content?.total || 0 }}</p>
          <p><strong>Completed:</strong> {{ statistics?.completed || 0 }}</p>
          <p><strong>Subjects Available:</strong> {{ subjects?.length || 0 }}</p>
        </div>
      </div>

      <!-- Simple Content List -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-4">Available Content</h2>
        
        <div v-if="content?.data?.length > 0" class="space-y-4">
          <div 
            v-for="item in content.data" 
            :key="item.id"
            class="border p-4 rounded-lg"
          >
            <h3 class="font-semibold">{{ item.title }}</h3>
            <p class="text-gray-600">{{ item.subject }} - {{ item.topic }}</p>
            <p class="text-sm text-gray-500">
              Grade {{ item.grade_level }} | {{ item.content_type }} | {{ item.difficulty_level }}
            </p>
          </div>
        </div>
        
        <div v-else class="text-center py-8">
          <p class="text-gray-500">No content available</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>