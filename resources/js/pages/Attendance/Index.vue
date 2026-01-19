<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-6">
          <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
              {{ $t('attendance.title') }}
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
              {{ $t('attendance.fingerprint_attendance') }}
            </p>
          </div>
          <Button @click="router.visit(route('attendance.late', props.company.id))" variant="destructive" class="gap-2 cursor-pointer">
            <AlertTriangle class="w-4 h-4" />
            {{ $t('attendance.view_late') }}
          </Button>
        </div>

        <!-- Fingerprint Attendance Section -->
        <Card class="mb-8">
          <CardHeader>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
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
              <CardTitle class="flex items-center gap-2">
                <Fingerprint class="w-5 h-5" />
                {{ $t('attendance.fingerprint_title') }}
              </CardTitle>
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
                      <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700">
                        {{ $t('attendance.attendance_status') }}
                      </th>
                      <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700">
                        {{ $t('attendance.attendance_late_minutes') }}
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
                      <td class="px-6 py-4 text-center border-r border-gray-200 dark:border-gray-700">
                        <div class="flex justify-center">
                          <Badge :variant="getStatusVariant(record.status_ar)" class="px-3 py-1">
                            {{ record.status_ar || '-' }}
                          </Badge>
                        </div>
                      </td>
                      <td class="px-6 py-4 text-center border-r border-gray-200 dark:border-gray-700">
                        <div class="flex justify-center">
                          <span class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ formatLateMinutes(record.late_minutes, record.status_ar) }}
                          </span>
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
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                      <template v-for="link in fingerprintAttendance.links" :key="link.label">
                        <Button
                          v-if="link.url"
                          @click="router.visit(link.url)"
                          :variant="link.active ? 'default' : 'outline'"
                          size="sm"
                          class="mr-1"
                        >
                          <span v-html="translatePaginationLabel(link.label)"></span>
                        </Button>
                        <Button v-else variant="outline" size="sm" disabled class="mr-1">
                          <span v-html="translatePaginationLabel(link.label)"></span>
                        </Button>
                      </template>
                    </nav>
                  </div>
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
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
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
import type { BreadcrumbItem } from '@/types'

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

const breadcrumbs = computed((): BreadcrumbItem[] => [
  {
    title: t('nav.dashboard'),
    href: '/dashboard',
  },
  {
    title: t('companies.title'),
    href: '/companies',
  },
  {
    title: props.company?.name_ar || props.company?.name_en || t('companies.title'),
    href: `/companies/${props.company?.id}`,
  },
  {
    title: t('attendance.title'),
    href: `/companies/${props.company?.id}/attendance`,
  },
])

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

const clearFilters = () => {
  selectedDateInput.value = new Date().toISOString().split('T')[0]
  searchQuery.value = ''
  router.get(route('attendance.index', props.company.id), { 
    date: selectedDateInput.value,
    search: undefined
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
  // For attendance status (Arabic)
  if (status === 'في الموعد') {
    return 'success'
  }
  if (status === 'متأخر') {
    return 'destructive'
  }
  if (status === 'غائب') {
    return 'warning'
  }
  if (status === 'إجازة') {
    return 'secondary'
  }
  if (status === 'غير محدد') {
    return 'outline'
  }
  
  // For import status (English)
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

const formatLateMinutes = (lateMinutes, status) => {
  if (lateMinutes === null || lateMinutes === undefined) {
    return '-'
  }
  if (status === 'غير محدد' || status === 'غائب') {
    return '-'
  }
  return lateMinutes.toString()
}

const translatePaginationLabel = (label) => {
  if (!label) return ''
  // Replace English pagination labels with translated ones
  return label
    .replace(/&laquo; Previous/g, `&laquo; ${t('common.previous')}`)
    .replace(/Next &raquo;/g, `${t('common.next')} &raquo;`)
    .replace(/&laquo;/g, '&laquo;')
    .replace(/&raquo;/g, '&raquo;')
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
