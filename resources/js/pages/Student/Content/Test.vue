<script setup>
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
  message: String,
  user: Object,
  student: Object,
  content: Object
})
</script>

<template>
  <Head title="Content Test" />

  <AppLayout>
    <div class="space-y-6">
      <h1 class="text-3xl font-bold">Content Test Page</h1>
      
      <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-4">Debug Information</h2>
        
        <div class="space-y-2">
          <p><strong>Message:</strong> {{ message }}</p>
          <p><strong>User:</strong> {{ user?.name }} ({{ user?.email }})</p>
          <p><strong>User Role:</strong> {{ user?.role }}</p>
          <p><strong>Student:</strong> {{ student ? 'Found' : 'Not found' }}</p>
          <p><strong>Content Count:</strong> {{ content?.total || 0 }}</p>
        </div>

        <div v-if="content?.data" class="mt-4">
          <h3 class="font-semibold">Sample Content:</h3>
          <ul class="list-disc list-inside">
            <li v-for="item in content.data.slice(0, 3)" :key="item.id">
              {{ item.title }} - {{ item.subject }}
            </li>
          </ul>
        </div>
      </div>
    </div>
  </AppLayout>
</template>