<script setup>
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import Heading from '@/components/Heading.vue'
import Icon from '@/components/Icon.vue'

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
})
</script>

<template>
  <Head title="Admin Dashboard" />

  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div>
        <Heading>Admin Dashboard</Heading>
        <p class="text-gray-600 mt-2">
          Welcome, {{ user.name }}! Manage the learning platform from here.
        </p>
      </div>

      <!-- Admin Overview Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Users</CardTitle>
            <Icon name="users" class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">--</div>
            <p class="text-xs text-muted-foreground">Students, Teachers, Admins</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Content Library</CardTitle>
            <Icon name="bookOpen" class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">--</div>
            <p class="text-xs text-muted-foreground">Learning materials</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">System Health</CardTitle>
            <Icon name="activity" class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-green-600">Good</div>
            <p class="text-xs text-muted-foreground">All systems operational</p>
          </CardContent>
        </Card>
      </div>

      <!-- Quick Actions -->
      <Card>
        <CardHeader>
          <CardTitle>Quick Actions</CardTitle>
          <CardDescription>
            Common administrative tasks
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="flex items-center gap-3 p-3 rounded-lg border">
              <Icon name="userPlus" class="h-8 w-8 text-blue-600" />
              <div>
                <div class="font-medium">User Management</div>
                <div class="text-sm text-gray-600">Manage students and teachers</div>
              </div>
            </div>
            
            <div class="flex items-center gap-3 p-3 rounded-lg border">
              <Icon name="settings" class="h-8 w-8 text-green-600" />
              <div>
                <div class="font-medium">System Settings</div>
                <div class="text-sm text-gray-600">Configure platform settings</div>
              </div>
            </div>
            
            <div class="flex items-center gap-3 p-3 rounded-lg border">
              <Icon name="barChart" class="h-8 w-8 text-purple-600" />
              <div>
                <div class="font-medium">Analytics</div>
                <div class="text-sm text-gray-600">View platform statistics</div>
              </div>
            </div>
            
            <div class="flex items-center gap-3 p-3 rounded-lg border">
              <Icon name="bookOpen" class="h-8 w-8 text-orange-600" />
              <div>
                <div class="font-medium">Content Library</div>
                <div class="text-sm text-gray-600">Manage learning content</div>
              </div>
            </div>
            
            <div class="flex items-center gap-3 p-3 rounded-lg border">
              <Icon name="clipboard" class="h-8 w-8 text-red-600" />
              <div>
                <div class="font-medium">Survey Management</div>
                <div class="text-sm text-gray-600">Configure learning style surveys</div>
              </div>
            </div>
            
            <div class="flex items-center gap-3 p-3 rounded-lg border">
              <Icon name="database" class="h-8 w-8 text-gray-600" />
              <div>
                <div class="font-medium">Database Tools</div>
                <div class="text-sm text-gray-600">Run seeders and maintenance</div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- System Information -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Recent Activity</CardTitle>
            <CardDescription>Latest system events</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-3">
              <div class="flex items-center gap-3 text-sm">
                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                <span class="text-gray-600">System started successfully</span>
                <span class="text-gray-400 ml-auto">Just now</span>
              </div>
              <div class="flex items-center gap-3 text-sm">
                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                <span class="text-gray-600">Routes configured</span>
                <span class="text-gray-400 ml-auto">2 min ago</span>
              </div>
              <div class="flex items-center gap-3 text-sm">
                <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                <span class="text-gray-600">Dashboard unified</span>
                <span class="text-gray-400 ml-auto">3 min ago</span>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Platform Information</CardTitle>
            <CardDescription>System details</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-3 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-600">Laravel Version</span>
                <span class="font-mono">{{ $page.props.laravel_version || '12.x' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Vue.js Version</span>
                <span class="font-mono">3.x</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Environment</span>
                <span class="font-mono">{{ $page.props.app_env || 'local' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Debug Mode</span>
                <span class="font-mono">{{ $page.props.app_debug ? 'Enabled' : 'Disabled' }}</span>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>