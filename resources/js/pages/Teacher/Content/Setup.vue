<script setup>
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Alert, AlertDescription } from '@/components/ui/alert'
import Heading from '@/components/Heading.vue'
import Icon from '@/components/Icon.vue'

const props = defineProps({
  user: {
    type: Object,
    required: true
  },
  message: {
    type: String,
    default: 'Your teacher profile is not set up yet.'
  }
})
</script>

<template>
  <Head title="Teacher Setup Required" />

  <AppLayout>
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header -->
      <div class="text-center">
        <Icon name="userCheck" class="w-16 h-16 mx-auto text-gray-400 mb-4" />
        <Heading>Teacher Profile Setup Required</Heading>
        <p class="text-gray-600 mt-2">
          Welcome, {{ user.name }}! Let's get your teacher profile set up.
        </p>
      </div>

      <!-- Setup Information -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Icon name="info" class="w-5 h-5 text-blue-600" />
            Setup Required
          </CardTitle>
          <CardDescription>
            Your teacher profile needs to be configured before you can access the content management system.
          </CardDescription>
        </CardHeader>
        <CardContent class="space-y-6">
          <Alert>
            <Icon name="alertCircle" class="h-4 w-4" />
            <AlertDescription>
              {{ message }}
            </AlertDescription>
          </Alert>

          <!-- What happens next -->
          <div class="space-y-4">
            <h3 class="text-lg font-semibold">What happens next?</h3>
            <div class="space-y-3">
              <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                  <span class="text-sm font-medium text-blue-600">1</span>
                </div>
                <div>
                  <h4 class="font-medium">Contact Administrator</h4>
                  <p class="text-sm text-gray-600">
                    Reach out to your system administrator to set up your teacher profile.
                  </p>
                </div>
              </div>
              
              <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                  <span class="text-sm font-medium text-blue-600">2</span>
                </div>
                <div>
                  <h4 class="font-medium">Profile Configuration</h4>
                  <p class="text-sm text-gray-600">
                    Your administrator will set up your subject area, department, and teacher number.
                  </p>
                </div>
              </div>
              
              <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                  <span class="text-sm font-medium text-blue-600">3</span>
                </div>
                <div>
                  <h4 class="font-medium">Access Content Management</h4>
                  <p class="text-sm text-gray-600">
                    Once set up, you'll have full access to create and manage learning content for your students.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Temporary Access -->
          <div class="border-t pt-6">
            <h3 class="text-lg font-semibold mb-3">Need immediate access?</h3>
            <p class="text-sm text-gray-600 mb-4">
              If you need to get started right away, you can use one of the pre-configured teacher accounts:
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
              <div class="bg-gray-50 p-3 rounded">
                <strong>Mathematics Teacher:</strong><br>
                Email: siti.nurhaliza@learning.test<br>
                Password: password<br>
                Subject: Matematika
              </div>
              <div class="bg-gray-50 p-3 rounded">
                <strong>Physics Teacher:</strong><br>
                Email: ahmad.rizky@learning.test<br>
                Password: password<br>
                Subject: Fisika
              </div>
              <div class="bg-gray-50 p-3 rounded">
                <strong>Chemistry Teacher:</strong><br>
                Email: budi.hartono@learning.test<br>
                Password: password<br>
                Subject: Kimia
              </div>
              <div class="bg-gray-50 p-3 rounded">
                <strong>Biology Teacher:</strong><br>
                Email: rina.wijaya@learning.test<br>
                Password: password<br>
                Subject: Biologi
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center justify-center gap-4 pt-6">
            <Button
              as="Link"
              href="/dashboard"
              variant="outline"
            >
              <Icon name="arrowLeft" class="w-4 h-4 mr-2" />
              Back to Dashboard
            </Button>
            
            <Button
              as="Link"
              href="/login"
              variant="default"
            >
              <Icon name="logOut" class="w-4 h-4 mr-2" />
              Try Different Account
            </Button>
          </div>
        </CardContent>
      </Card>

      <!-- Developer Instructions -->
      <Card class="border-orange-200 bg-orange-50">
        <CardHeader>
          <CardTitle class="flex items-center gap-2 text-orange-800">
            <Icon name="code" class="w-5 h-5" />
            For Developers
          </CardTitle>
        </CardHeader>
        <CardContent class="text-sm text-orange-700">
          <p class="mb-3">
            If you're setting up this system, make sure to run the database seeders to create teacher accounts:
          </p>
          <div class="bg-orange-100 p-3 rounded font-mono text-xs">
            php artisan db:seed --class=UsersSeeder
          </div>
          <p class="mt-3 text-xs text-orange-600">
            This will create sample teacher accounts with proper relationships to the User model.
          </p>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>