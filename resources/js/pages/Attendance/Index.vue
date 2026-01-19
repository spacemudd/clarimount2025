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
        <Button @click="router.visit(route('attendance.create', company.id))" class="gap-2">
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
                <Button @click="router.visit(route('attendance.create', company.id))" class="gap-2">
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

        <!-- Fingerprint Attendance Section -->
        <Card class="mb-8">
          <CardHeader>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
              <CardTitle class="flex items-center gap-2">
                <Fingerprint class="w-5 h-5" />
                {{ $t('attendance.fingerprint_title') }}
              </CardTitle>
              <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full sm:w-auto">
                <!-- Search Input -->
                <div class="relative w-full sm:w-64">
                  <div class="absolute inset-y-0 left-0 rtl:left-auto rtl:right-0 pl-3 rtl:pl-0 rtl:pr-3 flex items-center pointer-events-none">
                    <Icon name="Search" class="h-4 w-4 text-gray-400" />
                  </div>
                  <Input
                    v-model="searchQuery"
                    :placeholder="$t('attendance.search_employee')"
                    class="w-full pl-10 rtl:pl-3 rtl:pr-10"
                  />
                </div>
                <!-- Date Input -->
                <div class="flex items-center gap-2">
                  <Label for="attendance-date" class="text-sm whitespace-nowrap">
                    {{ $t('attendance.select_date') }}
                  </Label>
                  <Input
                    id="attendance-date"
                    v-model="selectedDateInput"
                    type="date"
                    @change="handleDateChange"
                    class="w-40"
                  />
                </div>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <!-- Fingerprint Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
              <Card>
                <CardContent class="p-4">
                  <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg dark:bg-green-900/20">
                      <Fingerprint class="w-5 h-5 text-green-600 dark:text-green-400" />
                    </div>
                    <div class="ml-4">
                      <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        {{ $t('attendance.present_today') }}
                      </p>
                      <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ fingerprintStats?.present_count || 0 }}
                      </p>
                    </div>
                  </div>
                </CardContent>
              </Card>
              <Card>
                <CardContent class="p-4">
                  <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg dark:bg-blue-900/20">
                      <Clock class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div class="ml-4">
                      <p class="text-sm font-medium text-gray-600 dark:text-gray-400">
                        {{ $t('attendance.total_punches') }}
                      </p>
                      <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ fingerprintStats?.total_punches || 0 }}
                      </p>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>

            <!-- Fingerprint Attendance Table -->
            <div v-if="fingerprintAttendance && fingerprintAttendance.data && fingerprintAttendance.data.length > 0">
              <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                      <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700">
                        {{ $t('attendance.employee_name') }}
                      </th>
                      <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700">
                        {{ $t('attendance.device_pin') }}
                      </th>
                      <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700">
                        {{ $t('attendance.check_in') }}
                      </th>
                      <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700">
                        {{ $t('attendance.check_out') }}
                      </th>
                      <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700">
                        {{ $t('attendance.punch_count') }}
                      </th>
                      <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        {{ $t('attendance.device_name') }}
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="record in fingerprintAttendance.data" :key="record.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                      <td class="px-6 py-4 text-center border-r border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col items-center justify-center">
                          <div class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ getEmployeeName(record) }}
                          </div>
                          <div v-if="record.emp_code" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ record.emp_code }}
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 text-center border-r border-gray-200 dark:border-gray-700">
                        <div class="flex justify-center">
                          <Badge variant="secondary" class="px-3 py-1">
                            {{ record.device_pin }}
                          </Badge>
                        </div>
                      </td>
                      <td class="px-6 py-4 text-center border-r border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col items-center justify-center space-y-1">
                          <div class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ formatDateTime(record.first_punch) }}
                          </div>
                          <Badge v-if="record.first_verify_mode !== null" variant="outline" class="text-xs mt-1">
                            {{ $t('attendance.verify_mode') }}: {{ getVerifyModeName(record.first_verify_mode) }}
                          </Badge>
                        </div>
                      </td>
                      <td class="px-6 py-4 text-center border-r border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col items-center justify-center space-y-1">
                          <div class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ formatDateTime(record.last_punch) }}
                          </div>
                          <Badge v-if="record.last_verify_mode !== null" variant="outline" class="text-xs mt-1">
                            {{ $t('attendance.verify_mode') }}: {{ getVerifyModeName(record.last_verify_mode) }}
                          </Badge>
                        </div>
                      </td>
                      <td class="px-6 py-4 text-center border-r border-gray-200 dark:border-gray-700">
                        <div class="flex justify-center">
                          <Badge variant="info" class="px-3 py-1">
                            {{ record.punch_count || 0 }}
                          </Badge>
                        </div>
                      </td>
                      <td class="px-6 py-4 text-center">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                          {{ record.device_name || record.serial_number || '-' }}
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Pagination -->
              <div v-if="fingerprintAttendance.links" class="mt-6 flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                  <Button v-if="fingerprintAttendance.prev_page_url" @click="router.visit(fingerprintAttendance.prev_page_url)" variant="outline">
                    {{ $t('common.previous') }}
                  </Button>
                  <Button v-if="fingerprintAttendance.next_page_url" @click="router.visit(fingerprintAttendance.next_page_url)" variant="outline">
                    {{ $t('common.next') }}
                  </Button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                  <div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                      {{ $t('common.showing') }}
                      <span class="font-medium">{{ fingerprintAttendance.from || 0 }}</span>
                      {{ $t('common.to') }}
                      <span class="font-medium">{{ fingerprintAttendance.to || 0 }}</span>
                      {{ $t('common.of') }}
                      <span class="font-medium">{{ fingerprintAttendance.total || 0 }}</span>
                      {{ $t('common.results') }}
                    </p>
                  </div>
                  <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                      <template v-for="link in fingerprintAttendance.links" :key="link.label">
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
            <div v-else class="text-center py-12">
              <Fingerprint class="w-12 h-12 text-gray-400 mx-auto mb-4" />
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                {{ $t('attendance.no_fingerprint_records') }}
              </h3>
              <p class="text-gray-600 dark:text-gray-400">
                {{ $t('attendance.no_fingerprint_records_description') }}
              </p>
            </div>
          </CardContent>
        </Card>

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
              <Button @click="router.visit(route('attendance.create', company.id))">
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
                            <DropdownMenuItem @click="router.visit(route('attendance.show', [company.id, importItem.id]))">
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
import Icon from '@/components/Icon.vue'
import {
  FileText,
  Clock,
  AlertTriangle,
  Plus,
  Eye,
  RotateCcw,
  MoreVertical,
  Fingerprint,
  Calendar
} from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'
import { router } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const { t } = useI18n()

const props = defineProps({
  company: Object,
  imports: Object,
  syncStats: Object,
  fingerprintAttendance: Object,
  selectedDate: String,
  fingerprintStats: Object,
  filters: Object,
})

const selectedDateInput = ref(props.selectedDate || new Date().toISOString().split('T')[0])
const searchQuery = ref(props.filters?.search || '')

// Debounced search
let searchTimeout
watch(searchQuery, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    router.get(route('attendance.index', props.company.id), { 
      date: selectedDateInput.value,
      search: searchQuery.value || undefined
    }, {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    })
  }, 300)
})

const handleDateChange = () => {
  router.get(route('attendance.index', props.company.id), { 
    date: selectedDateInput.value,
    search: searchQuery.value || undefined
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const formatDateTime = (dateTime) => {
  if (!dateTime) return '-'
  try {
    const date = new Date(dateTime)
    // Convert UTC to Asia/Riyadh timezone
    const formatter = new Intl.DateTimeFormat('en-US', {
      timeZone: 'Asia/Riyadh',
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: false
    })
    
    const parts = formatter.formatToParts(date)
    const year = parts.find(p => p.type === 'year')?.value
    const month = parts.find(p => p.type === 'month')?.value
    const day = parts.find(p => p.type === 'day')?.value
    const hour = parts.find(p => p.type === 'hour')?.value
    const minute = parts.find(p => p.type === 'minute')?.value
    const second = parts.find(p => p.type === 'second')?.value
    
    // Format: HH:MM:SS (date in separate line or smaller)
    return `${hour}:${minute}:${second}`
  } catch (e) {
    return '-'
  }
}

const formatDateOnly = (dateTime) => {
  if (!dateTime) return '-'
  try {
    const date = new Date(dateTime)
    const formatter = new Intl.DateTimeFormat('en-US', {
      timeZone: 'Asia/Riyadh',
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
    })
    
    const parts = formatter.formatToParts(date)
    const year = parts.find(p => p.type === 'year')?.value
    const month = parts.find(p => p.type === 'month')?.value
    const day = parts.find(p => p.type === 'day')?.value
    
    return `${year}-${month}-${day}`
  } catch (e) {
    return '-'
  }
}

const getVerifyModeName = (mode) => {
  if (mode === null || mode === undefined) return null
  
  const modes = {
    0: t('attendance.verify_mode_password'),
    1: t('attendance.verify_mode_fingerprint'),
    2: t('attendance.verify_mode_rfid'),
    3: t('attendance.verify_mode_face'),
    4: t('attendance.verify_mode_fingerprint_password'),
    15: t('attendance.verify_mode_palm'),
  }
  
  return modes[mode] || t('attendance.verify_mode_unknown', { mode })
}

const getEmployeeName = (record) => {
  if (record.first_name && record.last_name) {
    return `${record.first_name} ${record.last_name}`
  }
  return t('attendance.unknown_employee', { pin: record.device_pin })
}

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
  router.post(route('attendance.retry', [props.company.id, importId]), {}, {
    preserveScroll: true,
    onSuccess: () => {
      // Handle success
    }
  })
}
</script>
