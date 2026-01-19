<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
            {{ $t('attendance.import_attendance') }}
          </h2>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ $t('attendance.import_description') }}
          </p>
        </div>
        <Button @click="$inertia.visit(route('attendance.index', props.company.id))" variant="outline">
          {{ $t('common.back') }}
        </Button>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <Card>
          <CardHeader>
            <CardTitle>{{ $t('attendance.upload_csv') }}</CardTitle>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              {{ $t('attendance.file_requirements') }}
            </p>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="submitForm" class="space-y-6">
              <!-- File Upload -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  {{ $t('attendance.upload_csv') }}
                </label>
                <div
                  @drop="handleDrop"
                  @dragover.prevent
                  @dragenter.prevent
                  class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-md hover:border-gray-400 dark:hover:border-gray-500 transition-colors"
                  :class="{ 'border-blue-400 bg-blue-50 dark:bg-blue-900/10': isDragOver }"
                >
                  <div class="space-y-1 text-center">
                    <Upload class="mx-auto h-12 w-12 text-gray-400" />
                    <div class="flex text-sm text-gray-600 dark:text-gray-400">
                      <label
                        for="file-upload"
                        class="relative cursor-pointer bg-white dark:bg-gray-900 rounded-md font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500"
                      >
                        <span>{{ $t('attendance.upload_csv') }}</span>
                        <input
                          id="file-upload"
                          ref="fileInput"
                          name="file-upload"
                          type="file"
                          accept=".csv"
                          class="sr-only"
                          @change="handleFileSelect"
                        />
                      </label>
                      <p class="pl-1">{{ $t('common.or_drag_and_drop') }}</p>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      CSV {{ $t('common.up_to') }} 10MB
                    </p>
                  </div>
                </div>

                <!-- Selected File Display -->
                <div v-if="selectedFile" class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-md">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center">
                      <FileText class="w-5 h-5 text-gray-400 mr-2" />
                      <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                          {{ selectedFile.name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                          {{ formatFileSize(selectedFile.size) }}
                        </p>
                      </div>
                    </div>
                    <Button @click="removeFile" variant="ghost" size="sm">
                      <X class="w-4 h-4" />
                    </Button>
                  </div>
                </div>

                <!-- Error Display -->
                <div v-if="$page.props.errors.file" class="mt-2 text-sm text-red-600 dark:text-red-400">
                  {{ $page.props.errors.file }}
                </div>
              </div>

              <!-- Template Download -->
              <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-md">
                <div class="flex items-start">
                  <Info class="w-5 h-5 text-blue-400 mt-0.5 mr-3" />
                  <div class="flex-1">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                      {{ $t('attendance.download_template') }}
                    </h3>
                    <p class="mt-1 text-sm text-blue-700 dark:text-blue-300">
                      {{ $t('attendance.template_help_text') }}
                    </p>
                    <div class="mt-3">
                      <Button @click="downloadTemplate" variant="outline" size="sm">
                        <Download class="w-4 h-4 mr-2" />
                        {{ $t('attendance.download_template') }}
                      </Button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Submit Button -->
              <div class="flex justify-end space-x-3">
                <Button
                  @click="$inertia.visit(route('attendance.index', props.company.id))"
                  type="button"
                  variant="outline"
                >
                  {{ $t('common.cancel') }}
                </Button>
                <Button
                  type="submit"
                  :disabled="!selectedFile || processing"
                >
                  <template v-if="processing">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                    {{ $t('attendance.processing_import') }}
                  </template>
                  <template v-else>
                    <Upload class="w-4 h-4 mr-2" />
                    {{ $t('attendance.import_attendance') }}
                  </template>
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
  Upload,
  FileText,
  X,
  Info,
  Download
} from 'lucide-vue-next'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { router } from '@inertiajs/vue3'

const { t } = useI18n()

const props = defineProps({
  company: Object,
})

const selectedFile = ref(null)
const processing = ref(false)
const isDragOver = ref(false)
const fileInput = ref(null)

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file && file.type === 'text/csv') {
    selectedFile.value = file
  } else {
    alert(t('attendance.invalid_file_type'))
  }
}

const handleDrop = (event) => {
  event.preventDefault()
  isDragOver.value = false
  
  const files = event.dataTransfer.files
  if (files.length > 0) {
    const file = files[0]
    if (file.type === 'text/csv' || file.name.endsWith('.csv')) {
      selectedFile.value = file
    } else {
      alert(t('attendance.invalid_file_type'))
    }
  }
}

const removeFile = () => {
  selectedFile.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const downloadTemplate = () => {
  window.location.href = route('attendance.template', props.company.id)
}

const submitForm = () => {
  if (!selectedFile.value) {
    alert(t('attendance.select_file_first'))
    return
  }

  processing.value = true
  
  const formData = new FormData()
  formData.append('file', selectedFile.value)

  router.post(route('attendance.store', props.company.id), formData, {
    preserveScroll: true,
    onFinish: () => {
      processing.value = false
    },
    onError: () => {
      processing.value = false
    }
  })
}
</script>
