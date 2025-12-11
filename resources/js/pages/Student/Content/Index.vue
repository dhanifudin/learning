<script setup>
import { Head, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import ContentGrid from '@/components/Content/ContentGrid.vue'
import ContentFilters from '@/components/Content/ContentFilters.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import Heading from '@/components/Heading.vue'
import Icon from '@/components/Icon.vue'
// Route functions - using hardcoded URLs for now
const getContentUrl = () => '/student/content'
const getRecommendationsUrl = () => '/student/content/recommendations'
const getRecentUrl = () => '/student/content/recent'

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
  subjects: {
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
  recommendations: {
    type: Array,
    default: () => []
  },
  completedContent: {
    type: Array,
    default: () => []
  }
})

const progressPercentage = computed(() => {
  const total = props.statistics.total
  const completed = props.statistics.completed
  return total > 0 ? Math.round((completed / total) * 100) : 0
})

const hasFilters = computed(() => {
  return Object.keys(props.filters).some(key => props.filters[key])
})
</script>

<template>
  <Head title="Content Library" />

  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <Heading>Content Library</Heading>
          <p class="text-gray-600 mt-1">
            Discover learning materials tailored to your needs
          </p>
        </div>
        <div class="flex items-center gap-4">
          <Button
            as="Link"
            :href="getRecommendationsUrl()"
            variant="outline"
          >
            <Icon name="star" class="w-4 h-4 mr-2" />
            My Recommendations
          </Button>
          <Button
            as="Link"
            :href="getRecentUrl()"
            variant="outline"
          >
            <Icon name="clock" class="w-4 h-4 mr-2" />
            Recent Content
          </Button>
        </div>
      </div>

      <!-- Progress Overview -->
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
                <p class="text-sm font-medium text-gray-600">Completed</p>
                <p class="text-2xl font-bold">{{ statistics.completed }}</p>
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
                <p class="text-sm font-medium text-gray-600">Progress</p>
                <p class="text-2xl font-bold">{{ progressPercentage }}%</p>
              </div>
              <div class="h-8 w-8 bg-yellow-100 rounded-full flex items-center justify-center">
                <Icon name="trendingUp" class="w-4 h-4 text-yellow-600" />
              </div>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
              <div 
                class="bg-yellow-600 h-2 rounded-full transition-all duration-300"
                :style="{ width: `${progressPercentage}%` }"
              ></div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Recommendations</p>
                <p class="text-2xl font-bold">{{ recommendations.length }}</p>
              </div>
              <div class="h-8 w-8 bg-purple-100 rounded-full flex items-center justify-center">
                <Icon name="star" class="w-4 h-4 text-purple-600" />
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Main Content Area -->
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Filters Sidebar -->
        <div class="lg:col-span-1">
          <ContentFilters
            :filters="filters"
            :subjects="subjects"
            :content-types="contentTypes"
            :difficulty-levels="difficultyLevels"
            :target-learning-styles="['visual', 'auditory', 'kinesthetic', 'all']"
            :route-url="getContentUrl()"
          />
        </div>

        <!-- Content Grid -->
        <div class="lg:col-span-3">
          <div class="space-y-4">
            <!-- Results Header -->
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4">
                <h2 class="text-lg font-semibold">
                  {{ hasFilters ? 'Search Results' : 'All Content' }}
                </h2>
                <Badge variant="outline">
                  {{ content.total }} {{ content.total === 1 ? 'item' : 'items' }}
                </Badge>
              </div>

              <!-- Sort Options -->
              <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">Sort by:</label>
                <select 
                  class="text-sm border rounded px-2 py-1"
                  @change="$event.target.value && router.get(getContentUrl(), { ...filters, sort: $event.target.value }, { preserveState: true })"
                >
                  <option value="">Relevance</option>
                  <option value="newest">Newest</option>
                  <option value="rating">Highest Rated</option>
                  <option value="popular">Most Popular</option>
                </select>
              </div>
            </div>

            <!-- Active Filters Summary -->
            <div v-if="hasFilters" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <Icon name="filter" class="w-4 h-4 text-blue-600" />
                  <span class="text-sm font-medium text-blue-900">Filters applied</span>
                </div>
                <Button
                  as="Link"
                  :href="getContentUrl()"
                  variant="outline"
                  size="sm"
                >
                  Clear all
                </Button>
              </div>
              <div class="mt-2 flex flex-wrap gap-2">
                <Badge v-if="filters.search" variant="secondary">
                  Search: "{{ filters.search }}"
                </Badge>
                <Badge v-if="filters.subject" variant="secondary">
                  Subject: {{ filters.subject }}
                </Badge>
                <Badge v-if="filters.content_type" variant="secondary">
                  Type: {{ filters.content_type }}
                </Badge>
                <Badge v-if="filters.difficulty_level" variant="secondary">
                  Level: {{ filters.difficulty_level }}
                </Badge>
              </div>
            </div>

            <!-- Content Grid -->
            <ContentGrid
              :content="content.data"
              :recommendations="recommendations"
              :completed-content="completedContent"
              :loading="false"
              empty-message="No content found"
              :empty-description="hasFilters ? 'Try adjusting your filters or search terms.' : 'New content will appear here as it becomes available.'"
            />

            <!-- Pagination -->
            <div v-if="content.links && content.links.length > 3" class="flex justify-center mt-8">
              <nav class="flex items-center gap-2">
                <Button
                  v-if="content.prev_page_url"
                  as="Link"
                  :href="content.prev_page_url"
                  variant="outline"
                  size="sm"
                  :preserve-state="true"
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
                      :preserve-state="true"
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
                  :preserve-state="true"
                >
                  Next
                  <Icon name="chevronRight" class="w-4 h-4 ml-1" />
                </Button>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>