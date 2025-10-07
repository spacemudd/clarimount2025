<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
            {{ $t('bayzat.configurations') }}
          </h2>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ $t('bayzat.manage_company_configurations') }}
          </p>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div v-if="companies.length === 0" class="text-center py-12">
          <Settings class="w-12 h-12 text-gray-400 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
            {{ $t('bayzat.no_companies') }}
          </h3>
          <p class="text-gray-600 dark:text-gray-400 mb-4">
            {{ $t('bayzat.create_companies_first') }}
          </p>
          <Button @click="$inertia.visit(route('companies.create'))">
            {{ $t('companies.create_company') }}
          </Button>
        </div>

        <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          <Card v-for="company in companies" :key="company.id">
            <CardHeader>
              <CardTitle class="flex items-center justify-between">
                <span>{{ getCompanyName(company) }}</span>
                <Badge :variant="company.bayzat_config?.is_enabled ? 'default' : 'secondary'">
                  {{ company.bayzat_config?.is_enabled ? $t('bayzat.enabled') : $t('bayzat.disabled') }}
                </Badge>
              </CardTitle>
              <CardDescription>
                {{ getCompanyDescription(company) }}
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div class="space-y-3">
                <!-- Configuration Status -->
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $t('common.status') }}
                  </span>
                  <div class="flex items-center space-x-2">
                    <component 
                      :is="company.bayzat_config?.is_enabled ? CheckCircle : XCircle" 
                      class="h-4 w-4"
                      :class="company.bayzat_config?.is_enabled ? 'text-green-500' : 'text-gray-400'"
                    />
                    <span class="text-sm">
                      {{ company.bayzat_config ? (company.bayzat_config.is_enabled ? $t('bayzat.configured') : $t('bayzat.configured_disabled')) : $t('bayzat.not_configured') }}
                    </span>
                  </div>
                </div>

                <!-- Last Sync -->
                <div v-if="company.bayzat_config" class="flex items-center justify-between">
                  <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $t('bayzat.last_sync') }}
                  </span>
                  <span class="text-sm">
                    {{ formatLastSync(company.bayzat_config.last_sync_at) }}
                  </span>
                </div>

                <!-- Sync Frequency -->
                <div v-if="company.bayzat_config" class="flex items-center justify-between">
                  <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $t('bayzat.sync_frequency') }}
                  </span>
                  <span class="text-sm">
                    {{ $t(`bayzat.${company.bayzat_config.sync_frequency}`) }}
                  </span>
                </div>
              </div>
            </CardContent>
            <CardFooter>
              <Button @click="$inertia.visit(route('bayzat-configs.show', company.id))" class="w-full" variant="outline">
                <Settings class="mr-2 h-4 w-4" />
                {{ company.bayzat_config ? $t('bayzat.manage_settings') : $t('bayzat.configure_bayzat') }}
              </Button>
            </CardFooter>
          </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Settings, CheckCircle, XCircle } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'

const { t, locale } = useI18n()

defineProps({
  companies: Array,
})

const getCompanyName = (company) => {
  return locale.value === 'ar' ? company.name_ar : company.name_en
}

const getCompanyDescription = (company) => {
  const description = locale.value === 'ar' ? company.description_ar : company.description_en
  return description || ''
}

const formatLastSync = (lastSync) => {
  if (!lastSync) return t('bayzat.never_synced')
  return new Date(lastSync).toLocaleString()
}
</script>
