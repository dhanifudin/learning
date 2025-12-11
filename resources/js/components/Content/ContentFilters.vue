<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { useDebounceFn } from '@vueuse/core'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible'
import Icon from '@/components/Icon.vue'

const props = defineProps({
  filters: {
    type: Object,
    default: () => ({})
  },
  subjects: {
    type: Array,
    default: () => []
  },
  contentTypes: {
    type: Array,
    default: () => []
  },
  difficultyLevels: {
    type: Array,
    default: () => []
  },
  targetLearningStyles: {
    type: Array,
    default: () => []
  },
  showAdvanced: {
    type: Boolean,
    default: true
  },
  routeUrl: {
    type: String,
    default: '/student/content'
  }
})

const searchQuery = ref(props.filters.search || '')
const selectedSubject = ref(props.filters.subject || '')
const selectedContentType = ref(props.filters.content_type || '')
const selectedDifficulty = ref(props.filters.difficulty_level || '')
const selectedLearningStyle = ref(props.filters.target_learning_style || '')
const isAdvancedOpen = ref(false)

const activeFiltersCount = computed(() => {
  let count = 0
  if (selectedSubject.value) count++
  if (selectedContentType.value) count++
  if (selectedDifficulty.value) count++
  if (selectedLearningStyle.value) count++
  return count
})

const hasActiveFilters = computed(() => {
  return searchQuery.value || activeFiltersCount.value > 0
})

// Debounced search function
const debouncedSearch = useDebounceFn(() => {
  applyFilters()
}, 300)

// Watch for search query changes
watch(searchQuery, () => {
  debouncedSearch()
})

const applyFilters = () => {
  const filters = {}
  
  if (searchQuery.value) filters.search = searchQuery.value
  if (selectedSubject.value) filters.subject = selectedSubject.value
  if (selectedContentType.value) filters.content_type = selectedContentType.value
  if (selectedDifficulty.value) filters.difficulty_level = selectedDifficulty.value
  if (selectedLearningStyle.value) filters.target_learning_style = selectedLearningStyle.value

  router.get(props.routeUrl, filters, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
}

const clearFilters = () => {
  searchQuery.value = ''
  selectedSubject.value = ''
  selectedContentType.value = ''
  selectedDifficulty.value = ''
  selectedLearningStyle.value = ''
  
  router.get(props.routeUrl, {}, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
}

const removeFilter = (filterType) => {
  switch (filterType) {
    case 'subject':
      selectedSubject.value = ''
      break
    case 'content_type':
      selectedContentType.value = ''
      break
    case 'difficulty_level':
      selectedDifficulty.value = ''
      break
    case 'target_learning_style':
      selectedLearningStyle.value = ''
      break
  }
  applyFilters()
}

const getFilterLabel = (type, value) => {
  const labels = {
    subject: value,
    content_type: value.charAt(0).toUpperCase() + value.slice(1),
    difficulty_level: value.charAt(0).toUpperCase() + value.slice(1),
    target_learning_style: value.charAt(0).toUpperCase() + value.slice(1)
  }
  return labels[type] || value
}
</script>

<template>
  <Card>
    <CardHeader class="pb-4">
      <CardTitle class="text-lg flex items-center justify-between">
        Filters
        <Badge v-if="activeFiltersCount > 0" variant="secondary">
          {{ activeFiltersCount }}
        </Badge>
      </CardTitle>
    </CardHeader>
    
    <CardContent class="space-y-4">
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
          />
        </div>
      </div>

      <!-- Active Filters -->
      <div v-if="hasActiveFilters" class="space-y-2">
        <Label>Active Filters</Label>
        <div class="flex flex-wrap gap-2">
          <Badge
            v-if="selectedSubject"
            variant="secondary"
            class="cursor-pointer"
            @click="removeFilter('subject')"
          >
            Subject: {{ getFilterLabel('subject', selectedSubject) }}
            <Icon name="x" class="w-3 h-3 ml-1" />
          </Badge>
          
          <Badge
            v-if="selectedContentType"
            variant="secondary"
            class="cursor-pointer"
            @click="removeFilter('content_type')"
          >
            Type: {{ getFilterLabel('content_type', selectedContentType) }}
            <Icon name="x" class="w-3 h-3 ml-1" />
          </Badge>
          
          <Badge
            v-if="selectedDifficulty"
            variant="secondary"
            class="cursor-pointer"
            @click="removeFilter('difficulty_level')"
          >
            Level: {{ getFilterLabel('difficulty_level', selectedDifficulty) }}
            <Icon name="x" class="w-3 h-3 ml-1" />
          </Badge>
          
          <Badge
            v-if="selectedLearningStyle"
            variant="secondary"
            class="cursor-pointer"
            @click="removeFilter('target_learning_style')"
          >
            Style: {{ getFilterLabel('target_learning_style', selectedLearningStyle) }}
            <Icon name="x" class="w-3 h-3 ml-1" />
          </Badge>
        </div>
      </div>

      <!-- Quick Filters -->
      <div class="space-y-2">
        <Label>Subject</Label>
        <div class="flex flex-wrap gap-2">
          <Button
            v-for="subject in subjects"
            :key="subject"
            variant="outline"
            size="sm"
            :class="selectedSubject === subject ? 'bg-blue-50 border-blue-300 text-blue-700' : ''"
            @click="selectedSubject = selectedSubject === subject ? '' : subject; applyFilters()"
          >
            {{ subject }}
          </Button>
        </div>
      </div>

      <!-- Advanced Filters -->
      <Collapsible v-if="showAdvanced" v-model:open="isAdvancedOpen">
        <CollapsibleTrigger class="flex items-center justify-between w-full p-2 hover:bg-gray-50 rounded">
          <Label class="cursor-pointer">Advanced Filters</Label>
          <Icon 
            name="chevronDown" 
            class="w-4 h-4 transition-transform duration-200"
            :class="isAdvancedOpen ? 'transform rotate-180' : ''"
          />
        </CollapsibleTrigger>
        
        <CollapsibleContent class="space-y-4 pt-2">
          <!-- Content Type -->
          <div class="space-y-2">
            <Label>Content Type</Label>
            <div class="grid grid-cols-2 gap-2">
              <Button
                v-for="type in contentTypes"
                :key="type"
                variant="outline"
                size="sm"
                :class="selectedContentType === type ? 'bg-blue-50 border-blue-300 text-blue-700' : ''"
                @click="selectedContentType = selectedContentType === type ? '' : type; applyFilters()"
              >
                <Icon :name="type === 'video' ? 'playCircle' : type === 'pdf' ? 'fileText' : type === 'audio' ? 'volume2' : type === 'interactive' ? 'monitor' : 'bookOpen'" class="w-4 h-4 mr-1" />
                {{ type.charAt(0).toUpperCase() + type.slice(1) }}
              </Button>
            </div>
          </div>

          <!-- Difficulty Level -->
          <div class="space-y-2">
            <Label>Difficulty Level</Label>
            <div class="flex gap-2">
              <Button
                v-for="level in difficultyLevels"
                :key="level"
                variant="outline"
                size="sm"
                :class="selectedDifficulty === level ? 'bg-blue-50 border-blue-300 text-blue-700' : ''"
                @click="selectedDifficulty = selectedDifficulty === level ? '' : level; applyFilters()"
              >
                {{ level.charAt(0).toUpperCase() + level.slice(1) }}
              </Button>
            </div>
          </div>

          <!-- Learning Style -->
          <div class="space-y-2">
            <Label>Learning Style</Label>
            <div class="grid grid-cols-2 gap-2">
              <Button
                v-for="style in targetLearningStyles"
                :key="style"
                variant="outline"
                size="sm"
                :class="selectedLearningStyle === style ? 'bg-blue-50 border-blue-300 text-blue-700' : ''"
                @click="selectedLearningStyle = selectedLearningStyle === style ? '' : style; applyFilters()"
              >
                {{ style === 'all' ? 'All Styles' : style.charAt(0).toUpperCase() + style.slice(1) }}
              </Button>
            </div>
          </div>
        </CollapsibleContent>
      </Collapsible>

      <!-- Clear Filters -->
      <Button
        v-if="hasActiveFilters"
        variant="outline"
        size="sm"
        class="w-full"
        @click="clearFilters"
      >
        <Icon name="xCircle" class="w-4 h-4 mr-2" />
        Clear All Filters
      </Button>
    </CardContent>
  </Card>
</template>