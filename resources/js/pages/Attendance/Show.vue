<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
            {{ $t('attendance.import_details') }}
          </h2>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ props.import.filename }}
          </p>
        </div>
        <div class="flex space-x-3">
          <Button @click="retryFailedSync" v-if="hasFailedSyncs" variant="outline">
            <RotateCcw class="w-4 h-4 mr-2" />
            {{ $t('attendance.retry_sync') }}
          </Button>
          <Button @click="router.visit(route('attendance.index'))" variant="outline">
            {{ $t('common.back') }}
          </Button>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <!-- Import Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <Card>
            <CardContent class="p-6">
              <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg dark:bg-blue-900/20">
                  <FileText class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                    {{ $t('attendance.total_records') }}
                  </p>
                  <p class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ props.import.total_records }}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="p-6">
              <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg dark:bg-green-900/20">
                  <CheckCircle class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                    {{ $t('attendance.valid_records') }}
                  </p>
                  <p class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ props.import.successful_records }}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="p-6">
              <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded-lg dark:bg-red-900/20">
                  <XCircle class="w-6 h-6 text-red-600 dark:text-red-400" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                    {{ $t('attendance.invalid_records') }}
                  </p>
                  <p class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ props.import.failed_records }}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="p-6">
              <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg dark:bg-purple-900/20">
                  <BarChart3 class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                    {{ $t('attendance.success_rate') }}
                  </p>
                  <p class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ props.import.success_rate }}%
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Sync Progress by Company -->
        <Card>
          <CardHeader>
            <CardTitle>{{ $t('attendance.sync_progress') }}</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="syncProgress.length === 0" class="text-center py-8">
              <p class="text-gray-500 dark:text-gray-400">
                {{ $t('attendance.no_sync_batches') }}
              </p>
            </div>
            <div v-else class="space-y-4">
              <div
                v-for="progress in syncProgress"
                :key="progress.company_id"
                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
              >
                <div class="flex items-center justify-between mb-3">
                  <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                      {{ progress.company_name }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                      {{ progress.total_records }} {{ $t('attendance.total_records').toLowerCase() }}
                    </p>
                  </div>
                  <div class="flex items-center space-x-3">
                    <Badge :variant="getSyncStatusVariant(progress.status)">
                      {{ getSyncStatusText(progress.status) }}
                    </Badge>
                    <Button
                      v-if="progress.status === 'failed'"
                      @click="retryBatch(progress.company_id)"
                      size="sm"
                      variant="outline"
                    >
                      <RotateCcw class="w-4 h-4 mr-1" />
                      {{ $t('attendance.retry_batch') }}
                    </Button>
                  </div>
                </div>

                <!-- Progress Bar -->
                <div class="mb-3">
                  <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                    <span>{{ $t('attendance.completion_percentage') }}</span>
                    <span>{{ progress.completion_percentage }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div
                      class="h-2 rounded-full transition-all duration-300"
                      :class="getProgressBarColor(progress.status)"
                      :style="{ width: progress.completion_percentage + '%' }"
                    ></div>
                  </div>
                </div>

                <!-- Sync Statistics -->
                <div class="grid grid-cols-3 gap-4 text-sm">
                  <div>
                    <p class="text-gray-500 dark:text-gray-400">{{ $t('attendance.synced') }}</p>
                    <p class="font-medium text-green-600 dark:text-green-400">
                      {{ progress.synced_records }}
                    </p>
                  </div>
                  <div>
                    <p class="text-gray-500 dark:text-gray-400">{{ $t('attendance.failed_sync') }}</p>
                    <p class="font-medium text-red-600 dark:text-red-400">
                      {{ progress.failed_records }}
                    </p>
                  </div>
                  <div>
                    <p class="text-gray-500 dark:text-gray-400">{{ $t('attendance.success_rate') }}</p>
                    <p class="font-medium text-gray-900 dark:text-white">
                      {{ progress.success_rate }}%
                    </p>
                  </div>
                </div>

                <!-- Error Message -->
                <div v-if="progress.error_message" class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 rounded-md">
                  <p class="text-sm text-red-600 dark:text-red-400">
                    {{ progress.error_message }}
                  </p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Validation Summary -->
        <Card v-if="validationSummary.total_errors > 0">
          <CardHeader>
            <CardTitle>{{ $t('attendance.validation_errors') }}</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <!-- Error Categories -->
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div
                  v-for="(count, type) in validationSummary.error_types"
                  :key="type"
                  class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg"
                >
                  <p class="text-sm font-medium text-red-800 dark:text-red-200">
                    {{ $t(`attendance.${type}`) }}
                  </p>
                  <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                    {{ count }}
                  </p>
                </div>
              </div>

              <!-- Unmapped Departments -->
              <div v-if="validationSummary.unmapped_departments.length > 0">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                  {{ $t('attendance.unmapped_departments') }}
                </h4>
                <div class="flex flex-wrap gap-2">
                  <Badge
                    v-for="department in validationSummary.unmapped_departments"
                    :key="department"
                    variant="destructive"
                  >
                    {{ department }}
                  </Badge>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Import Records Table -->
        <Card>
          <CardHeader>
            <CardTitle>{{ $t('attendance.import_records') }}</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                      {{ $t('employees.employee_id') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                      {{ $t('employees.first_name') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                      {{ $t('employees.department') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                      {{ $t('common.date') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                      {{ $t('attendance.sync_status') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                      {{ $t('common.status') }}
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                  <tr
                    v-for="record in props.import.records.slice(0, 50)"
                    :key="record.id"
                    class="hover:bg-gray-50 dark:hover:bg-gray-800"
                  >
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                      {{ record.csv_employee_id }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                      {{ record.first_name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      {{ record.fingerprint_department }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                      {{ formatDate(record.date) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <Badge :variant="getSyncStatusVariant(record.bayzat_sync_status)">
                        {{ getSyncStatusText(record.bayzat_sync_status) }}
                      </Badge>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <Badge :variant="record.is_valid ? 'success' : 'destructive'">
                        {{ record.is_valid ? $t('common.valid') : $t('common.invalid') }}
                      </Badge>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-if="props.import.records.length > 50" class="mt-4 text-center">
              <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $t('common.showing_first_n_records', { count: 50 }) }}
              </p>
            </div>
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
import { Badge } from '@/components/ui/badge'
import {
  FileText,
  CheckCircle,
  XCircle,
  BarChart3,
  RotateCcw
} from 'lucide-vue-next'
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { router } from '@inertiajs/vue3'

const { t } = useI18n()

const props = defineProps({
  import: Object,
  syncProgress: Array,
  validationSummary: Object,
})

const hasFailedSyncs = computed(() => {
  return props.syncProgress.some(progress => progress.status === 'failed')
})

const getSyncStatusVariant = (status) => {
  switch (status) {
    case 'synced':
    case 'completed':
      return 'success'
    case 'syncing':
    case 'processing':
      return 'warning'
    case 'failed':
      return 'destructive'
    default:
      return 'secondary'
  }
}

const getSyncStatusText = (status) => {
  return t(`attendance.${status}`, status)
}

const getProgressBarColor = (status) => {
  switch (status) {
    case 'completed':
      return 'bg-green-500'
    case 'processing':
      return 'bg-yellow-500'
    case 'failed':
      return 'bg-red-500'
    default:
      return 'bg-gray-500'
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const retryFailedSync = () => {
  router.post(route('attendance.retry', props.import.id), {}, {
    preserveScroll: true,
  })
}

const retryBatch = (companyId) => {
  // Find the batch for this company
  const batch = props.import.sync_batches.find(b => b.company_id === companyId)
  if (batch) {
    router.post(route('attendance.batch.retry', batch.id), {}, {
      preserveScroll: true,
    })
  }
}
</script>
