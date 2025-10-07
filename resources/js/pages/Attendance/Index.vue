<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
            {{ $t('attendance.title') }}
          </h2>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ $t('attendance.import_description') }}
          </p>
        </div>
        <Button @click="router.visit(route('attendance.create'))" class="gap-2">
          <Plus class="w-4 h-4" />
          {{ $t('attendance.import_attendance') }}
        </Button>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Quick Actions -->
        <div class="mb-6">
          <Card>
            <CardContent class="p-6">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ $t('attendance.import_attendance') }}
                  </h3>
                  <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $t('attendance.import_description') }}
                  </p>
                </div>
                <Button @click="router.visit(route('attendance.create'))" class="gap-2">
                  <Plus class="w-4 h-4" />
                  {{ $t('attendance.import_attendance') }}
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <Card>
            <CardContent class="p-6">
              <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg dark:bg-blue-900/20">
                  <FileText class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                    {{ $t('attendance.total_imports') }}
                  </p>
                  <p class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ syncStats.total_imports }}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="p-6">
              <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg dark:bg-yellow-900/20">
                  <Clock class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                    {{ $t('attendance.pending_sync') }}
                  </p>
                  <p class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ syncStats.pending_syncs }}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="p-6">
              <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded-lg dark:bg-red-900/20">
                  <AlertTriangle class="w-6 h-6 text-red-600 dark:text-red-400" />
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                    {{ $t('attendance.failed_sync') }}
                  </p>
                  <p class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ syncStats.failed_syncs }}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Imports Table -->
        <Card>
          <CardHeader>
            <CardTitle>{{ $t('attendance.import_history') }}</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="imports.data.length === 0" class="text-center py-12">
              <FileText class="w-12 h-12 text-gray-400 mx-auto mb-4" />
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                {{ $t('attendance.no_imports') }}
              </h3>
              <p class="text-gray-600 dark:text-gray-400 mb-4">
                {{ $t('attendance.create_first_import') }}
              </p>
              <Button @click="router.visit(route('attendance.create'))">
                {{ $t('attendance.import_attendance') }}
              </Button>
            </div>

            <div v-else>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        {{ $t('common.name') }}
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        {{ $t('attendance.total_records') }}
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        {{ $t('attendance.sync_status') }}
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        {{ $t('common.status') }}
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        {{ $t('common.created_at') }}
                      </th>
                      <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        {{ $t('common.actions') }}
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="importItem in imports.data" :key="importItem.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <FileText class="w-5 h-5 text-gray-400 mr-3" />
                          <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                              {{ importItem.filename }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                              {{ $t('common.by') }} {{ importItem.user.name }}
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900 dark:text-white">
                          {{ importItem.total_records }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                          {{ importItem.valid_records_count }} {{ $t('attendance.valid_records').toLowerCase() }}
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex space-x-2">
                          <Badge v-for="batch in importItem.sync_batches" :key="batch.id" :variant="getSyncStatusVariant(batch.status)">
                            {{ batch.company.name }}
                          </Badge>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <Badge :variant="getStatusVariant(importItem.status)">
                          {{ getStatusText(importItem.status) }}
                        </Badge>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ formatDate(importItem.created_at) }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <DropdownMenu>
                          <DropdownMenuTrigger as-child>
                            <Button variant="ghost" size="sm">
                              <MoreVertical class="w-4 h-4" />
                            </Button>
                          </DropdownMenuTrigger>
                          <DropdownMenuContent align="end">
                            <DropdownMenuItem @click="router.visit(route('attendance.show', importItem.id))">
                              <Eye class="w-4 h-4 mr-2" />
                              {{ $t('common.view') }}
                            </DropdownMenuItem>
                            <DropdownMenuItem v-if="hasFailedSyncs(importItem)" @click="retrySync(importItem.id)">
                              <RotateCcw class="w-4 h-4 mr-2" />
                              {{ $t('attendance.retry_sync') }}
                            </DropdownMenuItem>
                          </DropdownMenuContent>
                        </DropdownMenu>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Pagination -->
              <div v-if="imports.links" class="mt-6 flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                  <Button v-if="imports.prev_page_url" @click="router.visit(imports.prev_page_url)" variant="outline">
                    {{ $t('common.previous') }}
                  </Button>
                  <Button v-if="imports.next_page_url" @click="router.visit(imports.next_page_url)" variant="outline">
                    {{ $t('common.next') }}
                  </Button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                  <div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                      {{ $t('common.showing') }}
                      <span class="font-medium">{{ imports.from }}</span>
                      {{ $t('common.to') }}
                      <span class="font-medium">{{ imports.to }}</span>
                      {{ $t('common.of') }}
                      <span class="font-medium">{{ imports.total }}</span>
                      {{ $t('common.results') }}
                    </p>
                  </div>
                  <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                      <template v-for="link in imports.links" :key="link.label">
                        <Button
                          v-if="link.url"
                          @click="router.visit(link.url)"
                          :variant="link.active ? 'default' : 'outline'"
                          size="sm"
                          class="mr-1"
                        >
                          <span v-html="link.label"></span>
                        </Button>
                        <Button v-else variant="outline" size="sm" disabled class="mr-1">
                          <span v-html="link.label"></span>
                        </Button>
                      </template>
                    </nav>
                  </div>
                </div>
              </div>
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
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import {
  FileText,
  Clock,
  AlertTriangle,
  Plus,
  Eye,
  RotateCcw,
  MoreVertical
} from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'
import { router } from '@inertiajs/vue3'

const { t } = useI18n()

defineProps({
  imports: Object,
  syncStats: Object,
})

const getStatusVariant = (status) => {
  switch (status) {
    case 'completed':
      return 'success'
    case 'processing':
      return 'warning'
    case 'failed':
      return 'destructive'
    default:
      return 'secondary'
  }
}

const getSyncStatusVariant = (status) => {
  switch (status) {
    case 'completed':
      return 'success'
    case 'processing':
      return 'warning'
    case 'failed':
      return 'destructive'
    default:
      return 'secondary'
  }
}

const getStatusText = (status) => {
  return t(`common.status_${status}`, status)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const hasFailedSyncs = (importItem) => {
  return importItem.sync_batches.some(batch => batch.status === 'failed')
}

const retrySync = (importId) => {
  router.post(route('attendance.retry', importId), {}, {
    preserveScroll: true,
    onSuccess: () => {
      // Handle success
    }
  })
}
</script>
