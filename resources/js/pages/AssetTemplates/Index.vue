<template>
  <app-layout>
    <div class="p-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl">
            {{ t('asset_templates.title') }}
          </h1>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            {{ t('asset_templates.description') }}
          </p>
        </div>
        <Link 
          :href="route('asset-templates.create')"
          class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 w-full sm:w-auto"
        >
          <Icon name="plus" class="h-4 w-4 mr-2" />
          {{ t('asset_templates.create_template') }}
        </Link>
      </div>

      <!-- Filters -->
      <Card class="mb-6">
        <CardContent class="p-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Search -->
            <div>
              <Label for="search">{{ t('common.search') }}</Label>
              <Input
                id="search"
                v-model="searchTerm"
                :placeholder="t('asset_templates.search_templates')"
                class="mt-1"
                @input="debouncedSearch"
              />
            </div>

            <!-- Category Filter -->
            <div>
              <Label for="category">{{ t('common.category') }}</Label>
              <select
                id="category"
                v-model="selectedCategory"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                @change="handleFilter"
              >
                <option value="">{{ t('asset_templates.all_categories') }}</option>
                <option 
                  v-for="category in categories" 
                  :key="category.id" 
                  :value="category.id"
                >
                  {{ '—'.repeat(Math.max(0, category.depth || 0)) }} {{ category.name }}
                </option>
              </select>
            </div>

            <!-- Company Filter -->
            <div>
              <Label for="company">{{ t('common.company') }}</Label>
              <select
                id="company"
                v-model="selectedCompany"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                @change="handleFilter"
              >
                <option value="">{{ t('asset_templates.all_companies') }}</option>
                <option value="global">{{ t('asset_templates.global_templates') }}</option>
                <option 
                  v-for="company in companies" 
                  :key="company.id" 
                  :value="company.id"
                >
                  {{ company.name_en }}
                </option>
              </select>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Templates Table -->
      <Card>
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    {{ t('asset_templates.template') }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    {{ t('common.category') }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    {{ t('common.company') }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    {{ t('asset_templates.usage') }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    {{ t('common.actions') }}
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-if="templates.data.length === 0">
                  <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                    <Icon name="template" class="h-12 w-12 mx-auto mb-4 text-gray-300 dark:text-gray-600" />
                    <p class="text-lg font-medium">{{ t('asset_templates.no_templates_found') }}</p>
                    <p class="mt-1">{{ t('asset_templates.create_first_template') }}</p>
                    <Link 
                      :href="route('asset-templates.create')"
                      class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-3 mt-4"
                    >
                      <Icon name="plus" class="h-4 w-4 mr-2" />
                      {{ t('asset_templates.create_template') }}
                    </Link>
                  </td>
                </tr>
                <tr 
                  v-for="template in templates.data" 
                  :key="template.id"
                  class="hover:bg-gray-50 dark:hover:bg-gray-800"
                >
                  <td class="px-6 py-4">
                    <div class="flex items-start">
                      <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                          {{ template.name }}
                        </div>
                        <div v-if="template.manufacturer || template.model_name" class="text-sm text-gray-500 dark:text-gray-400">
                          {{ [template.manufacturer, template.model_name].filter(Boolean).join(' ') }}
                          <span v-if="template.model_number" class="text-xs">({{ template.model_number }})</span>
                        </div>
                        <div v-if="template.formatted_specifications" class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                          {{ template.formatted_specifications }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span v-if="template.asset_category" class="text-sm text-gray-900 dark:text-gray-100">
                      {{ template.asset_category.name }}
                    </span>
                    <span v-else class="text-sm text-gray-500 dark:text-gray-400">—</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Badge 
                      :variant="template.is_global ? 'secondary' : 'default'"
                      class="text-xs"
                    >
                      {{ template.is_global ? t('asset_templates.global') : (template.company?.name_en || '—') }}
                    </Badge>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    {{ template.usage_count }} {{ $t('messages.times_used') }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center space-x-2">
                      <Button 
                        as="Link" 
                        :href="route('asset-templates.show', template.id)"
                        variant="ghost" 
                        size="sm"
                      >
                        <Icon name="eye" class="h-4 w-4" />
                      </Button>
                      <Button 
                        as="Link" 
                        :href="route('asset-templates.edit', template.id)"
                        variant="ghost" 
                        size="sm"
                      >
                        <Icon name="edit" class="h-4 w-4" />
                      </Button>
                      <Button 
                        variant="ghost" 
                        size="sm"
                        class="text-red-600 hover:text-red-900"
                        @click="confirmDelete(template)"
                      >
                        <Icon name="trash" class="h-4 w-4" />
                      </Button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>

      <!-- Pagination -->
      <div v-if="templates.last_page > 1" class="mt-6 flex items-center justify-between">
        <div class="text-sm text-gray-700 dark:text-gray-300">
          {{ $t('messages.showing') }} {{ templates.from }}-{{ templates.to }} {{ $t('messages.of') }} {{ templates.total }}
        </div>
        <div class="flex space-x-1">
          <Button
            v-for="link in templates.links"
            :key="link.label"
            variant="ghost"
            size="sm"
            :disabled="!link.url"
            :class="{
              'bg-blue-50 text-blue-700 dark:bg-blue-900 dark:text-blue-300': link.active
            }"
            @click="handlePagination(link.url)"
            v-html="link.label"
          />
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Dialog -->
    <Dialog v-model:open="deleteDialog.show">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{{ $t('messages.delete_template') }}</DialogTitle>
          <DialogDescription>
            {{ $t('messages.delete_template_confirmation') }}
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button variant="outline" @click="deleteDialog.show = false">
            {{ $t('messages.cancel') }}
          </Button>
          <Button variant="destructive" @click="handleDelete" :disabled="deleteDialog.loading">
            <Icon v-if="deleteDialog.loading" name="loader-2" class="h-4 w-4 mr-2 animate-spin" />
            {{ $t('messages.delete') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </app-layout>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { debounce } from 'lodash'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import Icon from '@/components/Icon.vue'

const { t } = useI18n()

interface AssetTemplate {
  id: number
  name: string
  manufacturer?: string
  model_name?: string
  model_number?: string
  formatted_specifications?: string
  asset_category?: {
    id: number
    name: string
  }
  company?: {
    id: number
    name_en: string
  }
  usage_count: number
  is_global: boolean
}

interface Props {
  templates: {
    data: AssetTemplate[]
    links: Array<{
      url: string | null
      label: string
      active: boolean
    }>
    from: number
    to: number
    total: number
    last_page: number
  }
  companies: Array<{
    id: number
    name_en: string
  }>
  categories: Array<{
    id: number
    name: string
    depth: number
  }>
  filters: {
    search?: string
    category_id?: string
    company_id?: string
  }
}

const props = defineProps<Props>()

// Search and filters
const searchTerm = ref(props.filters.search || '')
const selectedCategory = ref(props.filters.category_id || '')
const selectedCompany = ref(props.filters.company_id || '')

// Delete dialog
const deleteDialog = ref({
  show: false,
  template: null as AssetTemplate | null,
  loading: false
})

// Debounced search
const debouncedSearch = debounce(() => {
  handleFilter()
}, 300)

const handleFilter = () => {
  const filters: Record<string, any> = {}
  
  if (searchTerm.value) filters.search = searchTerm.value
  if (selectedCategory.value) filters.category_id = selectedCategory.value
  if (selectedCompany.value) filters.company_id = selectedCompany.value

  router.get(route('asset-templates.index'), filters, {
    preserveState: true,
    replace: true
  })
}

const handlePagination = (url: string | null) => {
  if (!url) return
  router.get(url, {}, { preserveState: true })
}

const confirmDelete = (template: AssetTemplate) => {
  deleteDialog.value.template = template
  deleteDialog.value.show = true
}

const handleDelete = () => {
  if (!deleteDialog.value.template) return
  
  deleteDialog.value.loading = true
  
  router.delete(route('asset-templates.destroy', deleteDialog.value.template.id), {
    onSuccess: () => {
      deleteDialog.value.show = false
      deleteDialog.value.template = null
    },
    onFinish: () => {
      deleteDialog.value.loading = false
    }
  })
}
</script> 