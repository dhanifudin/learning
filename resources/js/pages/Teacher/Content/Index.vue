<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import ContentCard from '@/components/Content/ContentCard.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import Heading from '@/components/Heading.vue'
import Icon from '@/components/Icon.vue'

const props = defineProps({
  content: {
    type: Object,
    required: true
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  statistics: {
    type: Object,
    required: true
  },
  teacher: {
    type: Object,
    required: true
  },
  gradeLevels: {
    type: Array,
    required: true
  },
  contentTypes: {
    type: Array,
    required: true
  },
  difficultyLevels: {
    type: Array,
    required: true
  },
  targetLearningStyles: {
    type: Array,
    required: true
  }
})

// Local filter state
const searchQuery = ref(props.filters.search || '')
const selectedGradeLevel = ref(props.filters.grade_level || '')
const selectedContentType = ref(props.filters.content_type || '')
const selectedStatus = ref(props.filters.is_active !== undefined ? (props.filters.is_active ? 'active' : 'inactive') : '')

const hasFilters = computed(() => {
  return searchQuery.value || selectedGradeLevel.value || selectedContentType.value || selectedStatus.value
})

const applyFilters = () => {
  const filters = {}
  
  if (searchQuery.value) filters.search = searchQuery.value
  if (selectedGradeLevel.value) filters.grade_level = selectedGradeLevel.value
  if (selectedContentType.value) filters.content_type = selectedContentType.value
  if (selectedStatus.value) {
    filters.is_active = selectedStatus.value === 'active'
  }

  router.get('/dashboard', filters, {
    preserveState: true,
    preserveScroll: true
  })
}

const clearFilters = () => {
  searchQuery.value = ''
  selectedGradeLevel.value = ''
  selectedContentType.value = ''
  selectedStatus.value = ''
  
  router.get('/dashboard', {}, {
    preserveState: true,
    preserveScroll: true
  })
}
</script>

<template>
  <Head title="Content Management" />

  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <Heading>Content Management</Heading>
          <p class="text-gray-600 mt-1">
            Manage learning materials for {{ teacher.subject || 'your subject' }}
          </p>
        </div>
        <div class="flex items-center gap-4">
          <Button
            as="Link"
            href="/dashboard/create"
            variant="default"
          >
            <Icon name="plus" class="w-4 h-4 mr-2" />
            Create Content
          </Button>
        </div>
      </div>

      <!-- Statistics Overview -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <Card>
          <CardContent class="p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Total Content</p>
                <p class="text-2xl font-bold">{{ content.total || 0 }}</p>
              </div>
              <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                <Icon name="bookOpen" class="w-4 h-4 text-blue-600" />
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Active Content</p>
                <p class="text-2xl font-bold">{{ statistics?.active_content || 0 }}</p>
              </div>
              <div class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
                <Icon name="checkCircle" class="w-4 h-4 text-green-600" />
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Total Views</p>
                <p class="text-2xl font-bold">{{ statistics?.total_views || 0 }}</p>
              </div>
              <div class="h-8 w-8 bg-yellow-100 rounded-full flex items-center justify-center">
                <Icon name="eye" class="w-4 h-4 text-yellow-600" />
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Avg Rating</p>
                <p class="text-2xl font-bold">{{ Number(statistics?.avg_rating || 0).toFixed(1) }}</p>
              </div>
              <div class="h-8 w-8 bg-purple-100 rounded-full flex items-center justify-center">
                <Icon name="star" class="w-4 h-4 text-purple-600" />
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Filters -->
      <Card>
        <CardHeader>
          <CardTitle class="text-lg">Filters</CardTitle>
          <CardDescription>Filter and search your content</CardDescription>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Search -->
            <div class="space-y-2">
              <Label for="search">Search</Label>
              <div class="relative">
                <Icon name="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                <Input
                  id="search"
                  v-model="searchQuery"
                  placeholder="Search content..."
                  class="pl-10"
                  @input="applyFilters"
                />
              </div>
            </div>

            <!-- Grade Level -->
            <div class="space-y-2">
              <Label for="grade-level">Grade Level</Label>
              <select
                id="grade-level"
                v-model="selectedGradeLevel"
                @change="applyFilters"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">All Grades</option>
                <option v-for="level in gradeLevels" :key="level" :value="level">
                  Grade {{ level }}
                </option>
              </select>
            </div>

            <!-- Content Type -->
            <div class="space-y-2">
              <Label for="content-type">Content Type</Label>
              <select
                id="content-type"
                v-model="selectedContentType"
                @change="applyFilters"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">All Types</option>
                <option v-for="type in contentTypes" :key="type" :value="type">
                  {{ type.charAt(0).toUpperCase() + type.slice(1) }}
                </option>
              </select>
            </div>

            <!-- Status -->
            <div class="space-y-2">
              <Label for="status">Status</Label>
              <select
                id="status"
                v-model="selectedStatus"
                @change="applyFilters"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>

            <!-- Clear Filters -->
            <div class="flex items-end">
              <Button
                v-if="hasFilters"
                @click="clearFilters"
                variant="outline"
                class="w-full"
              >
                <Icon name="xCircle" class="w-4 h-4 mr-2" />
                Clear
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Content Grid -->
      <div class="space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold">
            Your Content
          </h2>
          <Badge variant="outline">
            {{ content.total || 0 }} {{ content.total === 1 ? 'item' : 'items' }}
          </Badge>
        </div>

        <div v-if="content.data && content.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="item in content.data" :key="item.id" class="relative">
            <ContentCard
              :content="item"
              :show-actions="true"
              :show-metrics="true"
              class="h-full"
            />
            <!-- Teacher-specific actions overlay -->
            <div class="absolute top-2 right-2 flex gap-1">
              <Badge 
                :variant="item.is_active ? 'default' : 'secondary'"
                class="text-xs"
              >
                {{ item.is_active ? 'Active' : 'Inactive' }}
              </Badge>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-12 bg-gray-50 rounded-lg">
          <Icon name="bookOpen" class="w-16 h-16 mx-auto text-gray-400 mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">No content found</h3>
          <p class="text-gray-600 mb-6">
            {{ hasFilters ? 'Try adjusting your filters or search terms.' : 'Get started by creating your first content item.' }}
          </p>
          <Button
            v-if="!hasFilters"
            as="Link"
            href="/dashboard/create"
            variant="default"
          >
            <Icon name="plus" class="w-4 h-4 mr-2" />
            Create Your First Content
          </Button>
        </div>

        <!-- Pagination -->
        <div v-if="content.links && content.links.length > 3" class="flex justify-center mt-8">
          <nav class="flex items-center gap-2">
            <Button
              v-if="content.prev_page_url"
              as="Link"
              :href="content.prev_page_url"
              variant="outline"
              size="sm"
            >
              <Icon name="chevronLeft" class="w-4 h-4 mr-1" />
              Previous
            </Button>

            <div class="flex items-center gap-1">
              <template v-for="(link, index) in content.links" :key="index">
                <Button
                  v-if="link.url && !link.label.includes('Previous') && !link.label.includes('Next')"
                  as="Link"
                  :href="link.url"
                  :variant="link.active ? 'default' : 'outline'"
                  size="sm"
                  v-html="link.label"
                />
              </template>
            </div>

            <Button
              v-if="content.next_page_url"
              as="Link"
              :href="content.next_page_url"
              variant="outline"
              size="sm"
            >
              Next
              <Icon name="chevronRight" class="w-4 h-4 ml-1" />
            </Button>
          </nav>
        </div>
      </div>
    </div>
  </AppLayout>
</template>