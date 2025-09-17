<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
            {{ $t('bayzat.title') }} - {{ getCompanyName(company) }}
          </h2>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ $t('bayzat.attendance_sync_settings') }}
          </p>
        </div>
        <div class="flex space-x-3">
          <Button 
            v-if="company.bayzat_config" 
            @click="testConnection" 
            variant="outline"
            :disabled="testing"
          >
            <template v-if="testing">
              <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-gray-600 mr-2"></div>
              {{ $t('bayzat.testing') }}
            </template>
            <template v-else>
              <Settings class="w-4 h-4 mr-2" />
              {{ $t('bayzat.test_connection') }}
            </template>
          </Button>
          <Button @click="$inertia.visit(route('bayzat-configs.index'))" variant="outline">
            {{ $t('common.back') }}
          </Button>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <!-- Configuration Form -->
        <Card>
          <CardHeader>
            <CardTitle>{{ $t('bayzat.api_settings') }}</CardTitle>
            <CardDescription>
              {{ $t('bayzat.configure_api_credentials') }}
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="saveConfiguration" class="space-y-6">
              <!-- API Key -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  {{ $t('bayzat.api_key') }} *
                </label>
                <div class="relative">
                  <Input
                    v-model="form.api_key"
                    :type="showApiKey ? 'text' : 'password'"
                    :placeholder="$t('bayzat.api_key_placeholder')"
                    required
                    class="pr-10"
                  />
                  <button
                    type="button"
                    @click="showApiKey = !showApiKey"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                  >
                    <component :is="showApiKey ? EyeOff : Eye" class="h-4 w-4 text-gray-400" />
                  </button>
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                  {{ $t('bayzat.api_key_help') }}
                </p>
              </div>

              <!-- API URL -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  {{ $t('bayzat.api_url') }}
                </label>
                <Input
                  v-model="form.api_url"
                  type="url"
                  :placeholder="$t('bayzat.api_url_placeholder')"
                />
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                  {{ $t('bayzat.api_url_help') }}
                </p>
              </div>

              <!-- Sync Frequency -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  {{ $t('bayzat.sync_frequency') }}
                </label>
                <Select v-model="form.sync_frequency">
                  <option value="manual">{{ $t('bayzat.manual') }}</option>
                  <option value="hourly">{{ $t('bayzat.hourly') }}</option>
                  <option value="daily">{{ $t('bayzat.daily') }}</option>
                </Select>
              </div>

              <!-- Enable/Disable -->
              <div class="flex items-center space-x-3">
                <input
                  id="is_enabled"
                  v-model="form.is_enabled"
                  type="checkbox"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label for="is_enabled" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                  {{ $t('bayzat.enable_sync') }}
                </label>
              </div>

              <!-- Error Display -->
              <div v-if="$page.props.errors.config" class="text-sm text-red-600 dark:text-red-400">
                {{ $page.props.errors.config }}
              </div>

              <!-- Submit Button -->
              <div class="flex justify-end space-x-3">
                <Button
                  type="submit"
                  :disabled="processing"
                >
                  <template v-if="processing">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                    {{ $t('common.saving') }}
                  </template>
                  <template v-else>
                    <Save class="w-4 h-4 mr-2" />
                    {{ company.bayzat_config ? $t('bayzat.update_configuration') : $t('bayzat.save_configuration') }}
                  </template>
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>

        <!-- Sync Statistics -->
        <Card v-if="company.bayzat_config">
          <CardHeader>
            <CardTitle>{{ $t('bayzat.sync_statistics') }}</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
              <div class="text-center">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                  {{ syncStats.total_syncs }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ $t('bayzat.total_syncs') }}
                </p>
              </div>
              <div class="text-center">
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                  {{ syncStats.successful_syncs }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ $t('bayzat.successful_syncs') }}
                </p>
              </div>
              <div class="text-center">
                <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                  {{ syncStats.failed_syncs }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ $t('bayzat.failed_syncs') }}
                </p>
              </div>
              <div class="text-center">
                <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                  {{ syncStats.pending_syncs }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ $t('bayzat.pending_syncs') }}
                </p>
              </div>
            </div>

            <div v-if="syncStats.last_sync_at" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
              <div class="flex items-center space-x-2">
                <Clock class="h-4 w-4 text-gray-500" />
                <span class="text-sm text-gray-600 dark:text-gray-400">
                  {{ $t('bayzat.last_sync') }}: {{ formatLastSync(syncStats.last_sync_at) }}
                </span>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Delete Configuration -->
        <Card v-if="company.bayzat_config" class="border-red-200 dark:border-red-800">
          <CardHeader>
            <CardTitle class="text-red-600 dark:text-red-400">{{ $t('bayzat.danger_zone') }}</CardTitle>
            <CardDescription>
              {{ $t('bayzat.delete_configuration_warning') }}
            </CardDescription>
          </CardHeader>
          <CardContent>
            <Button @click="deleteConfiguration" variant="destructive">
              <Trash2 class="w-4 h-4 mr-2" />
              {{ $t('bayzat.delete_configuration') }}
            </Button>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select } from '@/components/ui/select'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Settings, Save, Clock, Trash2, Eye, EyeOff } from 'lucide-vue-next'
import { ref, reactive } from 'vue'
import { useI18n } from 'vue-i18n'
import { router } from '@inertiajs/vue3'

const { t, locale } = useI18n()

const props = defineProps({
  company: Object,
  syncStats: Object,
})

const processing = ref(false)
const testing = ref(false)
const showApiKey = ref(false)

const form = reactive({
  api_key: props.company.bayzat_config?.api_key || '',
  api_url: props.company.bayzat_config?.api_url || 'https://integration.bayzat.com/attendance',
  sync_frequency: props.company.bayzat_config?.sync_frequency || 'manual',
  is_enabled: props.company.bayzat_config?.is_enabled || false,
})

const getCompanyName = (company) => {
  return locale.value === 'ar' ? company.name_ar : company.name_en
}

const formatLastSync = (lastSync) => {
  if (!lastSync) return t('bayzat.never_synced')
  return new Date(lastSync).toLocaleString()
}

const saveConfiguration = () => {
  processing.value = true
  
  const method = props.company.bayzat_config ? 'put' : 'post'
  const url = props.company.bayzat_config 
    ? route('bayzat-configs.update', props.company.id)
    : route('bayzat-configs.store', props.company.id)

  router[method](url, form, {
    preserveScroll: true,
    onFinish: () => {
      processing.value = false
    },
  })
}

const testConnection = () => {
  testing.value = true
  
  router.post(route('bayzat-configs.test', props.company.id), {}, {
    preserveScroll: true,
    onFinish: () => {
      testing.value = false
    },
  })
}

const deleteConfiguration = () => {
  if (confirm(t('bayzat.confirm_delete_configuration'))) {
    router.delete(route('bayzat-configs.destroy', props.company.id), {
      preserveScroll: true,
    })
  }
}
</script>
