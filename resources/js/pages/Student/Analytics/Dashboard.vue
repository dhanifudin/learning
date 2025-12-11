<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import Heading from '@/components/Heading.vue'
import Icon from '@/components/Icon.vue'

const props = defineProps({
    analytics: Object,
    recentFeedback: Array,
    predictions: Array,
    period: String,
    learningProfile: Object
})

const selectedPeriod = ref(props.period)

// Computed properties for analytics display
const engagementScore = computed(() => {
    return Math.round(props.analytics?.engagement?.score || 0)
})

const performanceScore = computed(() => {
    return Math.round(props.analytics?.performance?.avg_score || 0)
})

const studyHours = computed(() => {
    return (props.analytics?.time_metrics?.total_hours || 0).toFixed(1)
})

const improvementTrend = computed(() => {
    const trend = props.analytics?.performance?.improvement_trend || 0
    return {
        value: Math.abs(trend).toFixed(1),
        direction: trend > 0 ? 'up' : trend < 0 ? 'down' : 'stable',
        color: trend > 0 ? 'green' : trend < 0 ? 'red' : 'gray'
    }
})

// Methods
const updatePeriod = (newPeriod) => {
    selectedPeriod.value = newPeriod
    window.location.href = `?period=${newPeriod}`
}

const getFeedbackIcon = (sentiment) => {
    switch (sentiment) {
        case 'positive': return 'star'
        case 'constructive': return 'lightBulb'
        default: return 'document'
    }
}

const getFeedbackColor = (sentiment) => {
    switch (sentiment) {
        case 'positive': return 'text-green-600'
        case 'constructive': return 'text-blue-600'
        default: return 'text-gray-600'
    }
}
</script>

<template>
    <Head title="My Analytics" />
    
    <AppLayout>
        <div class="space-y-6">
            
            <!-- Header Section -->
            <div class="flex justify-between items-center">
                <div>
                    <Heading>My Learning Analytics</Heading>
                    <p class="text-gray-600 mt-1">
                        Track your progress and learning insights
                    </p>
                </div>
                
                <!-- Period Selector -->
                <div class="flex gap-2">
                    <Button 
                        v-for="period in ['day', 'week', 'month', 'quarter']" 
                        :key="period"
                        :variant="selectedPeriod === period ? 'default' : 'outline'"
                        size="sm"
                        @click="updatePeriod(period)"
                    >
                        {{ period.charAt(0).toUpperCase() + period.slice(1) }}
                    </Button>
                </div>
            </div>

            <!-- Key Metrics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Engagement Score -->
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Engagement Score</p>
                                <p class="text-2xl font-bold">{{ engagementScore }}%</p>
                            </div>
                            <div class="h-8 w-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <Icon name="trendingUp" class="w-4 h-4 text-blue-600" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Performance Score -->
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Average Score</p>
                                <p class="text-2xl font-bold">{{ performanceScore }}%</p>
                            </div>
                            <div class="h-8 w-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <Icon name="target" class="w-4 h-4 text-green-600" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Study Hours -->
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Study Hours</p>
                                <p class="text-2xl font-bold">{{ studyHours }}h</p>
                            </div>
                            <div class="h-8 w-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                <Icon name="clock" class="w-4 h-4 text-orange-600" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Improvement Trend -->
                <Card>
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Improvement</p>
                                <p class="text-2xl font-bold">
                                    {{ improvementTrend.direction === 'up' ? '+' : 
                                       improvementTrend.direction === 'down' ? '-' : '' }}{{ improvementTrend.value }}%
                                </p>
                            </div>
                            <div :class="[
                                'h-8 w-8 rounded-lg flex items-center justify-center',
                                improvementTrend.color === 'green' ? 'bg-green-100' :
                                improvementTrend.color === 'red' ? 'bg-red-100' : 'bg-gray-100'
                            ]">
                                <Icon 
                                    :name="improvementTrend.direction === 'up' ? 'trendingUp' : 
                                           improvementTrend.direction === 'down' ? 'trendingDown' : 'minus'"
                                    :class="[
                                        'w-4 h-4',
                                        improvementTrend.color === 'green' ? 'text-green-600' :
                                        improvementTrend.color === 'red' ? 'text-red-600' : 'text-gray-600'
                                    ]"
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Learning Style and Activity Summary -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Learning Style Profile -->
                <Card v-if="learningProfile">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Icon name="brain" class="w-5 h-5" />
                            Learning Style Profile
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="text-center">
                                <Badge 
                                    :variant="learningProfile.dominant_style === 'visual' ? 'default' : 'secondary'"
                                    class="text-lg px-3 py-1"
                                >
                                    {{ learningProfile.dominant_style?.charAt(0).toUpperCase() + learningProfile.dominant_style?.slice(1) || 'Not Assessed' }}
                                </Badge>
                            </div>
                            
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span>Visual</span>
                                    <span>{{ learningProfile.visual_score || 0 }}/5</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div 
                                        class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                        :style="{ width: `${((learningProfile.visual_score || 0) / 5) * 100}%` }"
                                    ></div>
                                </div>
                                
                                <div class="flex justify-between text-sm">
                                    <span>Auditory</span>
                                    <span>{{ learningProfile.auditory_score || 0 }}/5</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div 
                                        class="bg-green-600 h-2 rounded-full transition-all duration-300"
                                        :style="{ width: `${((learningProfile.auditory_score || 0) / 5) * 100}%` }"
                                    ></div>
                                </div>
                                
                                <div class="flex justify-between text-sm">
                                    <span>Kinesthetic</span>
                                    <span>{{ learningProfile.kinesthetic_score || 0 }}/5</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div 
                                        class="bg-orange-600 h-2 rounded-full transition-all duration-300"
                                        :style="{ width: `${((learningProfile.kinesthetic_score || 0) / 5) * 100}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Activity Summary -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Icon name="activity" class="w-5 h-5" />
                            Recent Activity
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Content Views</span>
                                <span class="font-medium">{{ analytics.engagement?.content_views || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Completions</span>
                                <span class="font-medium">{{ analytics.engagement?.completions || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Study Sessions</span>
                                <span class="font-medium">{{ analytics.engagement?.sessions || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Total Assessments</span>
                                <span class="font-medium">{{ analytics.performance?.total_assessments || 0 }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Feedback -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Icon name="messageCircle" class="w-5 h-5" />
                            Recent Feedback
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div v-if="recentFeedback.length === 0" class="text-center py-4">
                                <Icon name="messageCircle" class="w-8 h-8 text-gray-400 mx-auto mb-2" />
                                <p class="text-sm text-gray-500">No recent feedback</p>
                            </div>
                            
                            <div 
                                v-for="feedback in recentFeedback.slice(0, 3)" 
                                :key="feedback.id"
                                class="border-l-4 border-blue-400 bg-blue-50 p-3 rounded-r"
                            >
                                <div class="flex items-start gap-2">
                                    <Icon 
                                        :name="getFeedbackIcon(feedback.sentiment)" 
                                        :class="['w-4 h-4 mt-0.5', getFeedbackColor(feedback.sentiment)]"
                                    />
                                    <div class="flex-1">
                                        <p class="text-sm text-blue-800 line-clamp-2">
                                            {{ feedback.feedback_text?.substring(0, 100) }}...
                                        </p>
                                        <p class="text-xs text-blue-600 mt-1">
                                            {{ new Date(feedback.created_at).toLocaleDateString() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <Button 
                                v-if="recentFeedback.length > 0" 
                                variant="outline" 
                                size="sm" 
                                class="w-full mt-3"
                                as="Link"
                                href="/dashboard"
                            >
                                View All Feedback
                            </Button>
                        </div>
                    </CardContent>
                </Card>

            </div>

            <!-- Quick Actions -->
            <Card>
                <CardHeader>
                    <CardTitle>Quick Actions</CardTitle>
                    <CardDescription>Explore more detailed analytics and reports</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <Button variant="outline" class="h-auto p-4 flex flex-col items-center gap-2" disabled>
                            <Icon name="barChart" class="w-6 h-6" />
                            <span>Detailed Performance</span>
                            <span class="text-xs text-gray-500">Coming Soon</span>
                        </Button>
                        
                        <Button variant="outline" class="h-auto p-4 flex flex-col items-center gap-2" disabled>
                            <Icon name="map" class="w-6 h-6" />
                            <span>Learning Journey</span>
                            <span class="text-xs text-gray-500">Coming Soon</span>
                        </Button>
                        
                        <Button variant="outline" class="h-auto p-4 flex flex-col items-center gap-2" disabled>
                            <Icon name="clock" class="w-6 h-6" />
                            <span>Study Patterns</span>
                            <span class="text-xs text-gray-500">Coming Soon</span>
                        </Button>
                        
                        <Button variant="outline" class="h-auto p-4 flex flex-col items-center gap-2" disabled>
                            <Icon name="download" class="w-6 h-6" />
                            <span>Download Report</span>
                            <span class="text-xs text-gray-500">Coming Soon</span>
                        </Button>
                    </div>
                </CardContent>
            </Card>

        </div>
    </AppLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>