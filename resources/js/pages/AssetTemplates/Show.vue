<template>
  <Head :title="template.name" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <Heading :title="template.name" />
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            {{ t('asset_templates.template_details') }}
          </p>
        </div>
        <div class="flex items-center space-x-3">
          <Link 
            :href="route('asset-templates.edit', template.id)"
            class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2"
          >
            <Icon name="Edit" class="mr-2 h-4 w-4" />
            {{ t('common.edit') }}
          </Link>
          <Button
            variant="destructive"
            @click="confirmDelete"
          >
            <Icon name="Trash2" class="mr-2 h-4 w-4" />
            {{ t('common.delete') }}
          </Button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Information -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Basic Information -->
          <Card>
            <CardHeader>
              <CardTitle>{{ t('asset_templates.basic_information') }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ t('asset_templates.template_name') }}
                  </Label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ template.name }}
                  </p>
                </div>
                
                <div>
                  <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ t('asset_templates.manufacturer') }}
                  </Label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ template.manufacturer || '—' }}
                  </p>
                </div>

                <div>
                  <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ t('asset_templates.model_name') }}
                  </Label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ template.model_name || '—' }}
                  </p>
                </div>

                <div>
                  <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ t('asset_templates.model_number') }}
                  </Label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ template.model_number || '—' }}
                  </p>
                </div>

                <div>
                  <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ t('common.category') }}
                  </Label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ template.asset_category?.name || '—' }}
                  </p>
                </div>

                <div>
                  <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ t('common.company') }}
                  </Label>
                  <Badge 
                    :variant="template.is_global ? 'secondary' : 'default'"
                    class="mt-1"
                  >
                    {{ template.is_global ? t('asset_templates.global') : (template.company?.name_en || '—') }}
                  </Badge>
                </div>
              </div>

              <div v-if="template.default_notes">
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('asset_templates.default_notes') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">
                  {{ template.default_notes }}
                </p>
              </div>
            </CardContent>
          </Card>

          <!-- Specifications -->
          <Card v-if="template.specifications && Object.keys(template.specifications).length > 0">
            <CardHeader>
              <CardTitle>{{ t('asset_templates.specifications') }}</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="(value, key) in template.specifications" :key="key">
                  <Label class="text-sm font-medium text-gray-500 dark:text-gray-400 capitalize">
                    {{ key.replace(/_/g, ' ') }}
                  </Label>
                  <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    {{ value }}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Assets Using This Template -->
          <Card>
            <CardHeader>
              <CardTitle>{{ t('asset_templates.assets_using_template') }}</CardTitle>
              <CardDescription>{{ t('asset_templates.assets_using_template_description') }}</CardDescription>
            </CardHeader>
            <CardContent>
              <div v-if="assets.length === 0" class="text-center py-8">
                <Icon name="Package" class="h-12 w-12 text-gray-400 mx-auto mb-3" />
                <p class="text-sm text-gray-500">{{ t('asset_templates.no_assets_found') }}</p>
              </div>
              
              <div v-else class="space-y-4">
                <div v-for="asset in assets" :key="asset.id" class="border rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                  <div class="flex items-center justify-between">
                    <div class="flex-1">
                      <div class="flex items-center space-x-3">
                        <div class="flex-1">
                          <h4 class="font-medium text-gray-900 dark:text-gray-100">
                            {{ asset.asset_tag }}
                          </h4>
                          <p v-if="asset.model_name" class="text-sm text-gray-600 dark:text-gray-400">
                            {{ asset.model_name }}
                            <span v-if="asset.model_number" class="text-gray-500">({{ asset.model_number }})</span>
                          </p>
                        </div>
                        <Badge :variant="asset.status === 'assigned' ? 'default' : 'secondary'">
                          {{ getStatusLabel(asset.status) }}
                        </Badge>
                      </div>
                      
                      <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                          <span class="font-medium text-gray-500 dark:text-gray-400">{{ t('asset_templates.location') }}:</span>
                          <span class="ml-1 text-gray-900 dark:text-gray-100">
                            {{ asset.location?.name || '—' }}
                          </span>
                        </div>
                        <div>
                          <span class="font-medium text-gray-500 dark:text-gray-400">{{ t('asset_templates.employee') }}:</span>
                          <span class="ml-1 text-gray-900 dark:text-gray-100">
                            {{ getEmployeeName(asset.assigned_to) }}
                          </span>
                        </div>
                        <div>
                          <span class="font-medium text-gray-500 dark:text-gray-400">{{ t('common.company') }}:</span>
                          <span class="ml-1 text-gray-900 dark:text-gray-100">
                            {{ asset.company?.name_en || '—' }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Template Image -->
          <Card v-if="template.image_path" class="overflow-hidden">
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <Icon name="Image" class="h-5 w-5" />
                Template Image
              </CardTitle>
            </CardHeader>
            <CardContent class="p-0">
              <div class="relative group">
                <img 
                  :src="`/storage/${template.image_path}`" 
                  :alt="template.name" 
                  class="w-full h-auto max-h-80 object-cover transition-transform group-hover:scale-105"
                  @error="handleImageError"
                  @click="openImageModal"
                />
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all cursor-pointer flex items-center justify-center">
                  <Icon 
                    name="ZoomIn" 
                    class="h-8 w-8 text-white opacity-0 group-hover:opacity-100 transition-opacity" 
                  />
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Image Error State -->
          <Card v-else-if="template.image_path && imageError" class="border-red-200 dark:border-red-800">
            <CardContent class="p-6 text-center">
              <Icon name="ImageOff" class="h-12 w-12 text-gray-400 mx-auto mb-3" />
              <p class="text-sm text-gray-500">Unable to load template image</p>
            </CardContent>
          </Card>

          <!-- Usage Statistics -->
          <Card>
            <CardHeader>
              <CardTitle>{{ t('asset_templates.usage_statistics') }}</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="text-center">
                <div class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                  {{ template.usage_count }}
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  {{ t('asset_templates.times_used') }}
                </p>
              </div>
            </CardContent>
          </Card>

          <!-- Template Information -->
          <Card>
            <CardHeader>
              <CardTitle>{{ t('asset_templates.template_info') }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div>
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('common.created_at') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                  {{ new Date(template.created_at).toLocaleDateString() }}
                </p>
              </div>
              
              <div>
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('common.updated_at') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                  {{ new Date(template.updated_at).toLocaleDateString() }}
                </p>
              </div>

              <div v-if="template.created_by_user">
                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                  {{ t('common.created_by') }}
                </Label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                  {{ template.created_by_user.name }}
                </p>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>

    <!-- Image Modal -->
    <Dialog v-model:open="imageModal">
      <DialogContent class="max-w-4xl w-full">
        <DialogHeader>
          <DialogTitle>{{ template.name }}</DialogTitle>
        </DialogHeader>
        <div class="flex justify-center">
          <img 
            v-if="template.image_path"
            :src="`/storage/${template.image_path}`" 
            :alt="template.name" 
            class="max-w-full max-h-[70vh] object-contain rounded-lg"
          />
        </div>
      </DialogContent>
    </Dialog>

    <!-- Delete Confirmation Dialog -->
    <Dialog v-model:open="deleteDialog.show">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{{ t('asset_templates.delete_confirm') }}</DialogTitle>
          <DialogDescription>
            {{ t('asset_templates.delete_confirm_message') }}
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button variant="outline" @click="deleteDialog.show = false">
            {{ t('common.cancel') }}
          </Button>
          <Button variant="destructive" @click="handleDelete" :disabled="deleteDialog.loading">
            <Icon v-if="deleteDialog.loading" name="Loader2" class="h-4 w-4 mr-2 animate-spin" />
            {{ t('common.delete') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import Icon from '@/components/Icon.vue'
import Heading from '@/components/Heading.vue'
import type { BreadcrumbItem } from '@/types'

const { t } = useI18n()

interface Asset {
  id: string
  asset_tag: string
  model_name?: string
  model_number?: string
  serial_number?: string
  status: string
  condition?: string
  location?: {
    id: number
    name: string
    code: string
  }
  company?: {
    id: number
    name_en: string
    name_ar: string
  }
  assigned_to?: {
    id: number
    first_name: string
    last_name: string
  }
  asset_category?: {
    id: number
    name: string
  }
  created_at: string
}

interface AssetTemplate {
  id: number
  name: string
  manufacturer?: string
  model_name?: string
  model_number?: string
  default_notes?: string
  image_path?: string
  specifications?: Record<string, any>
  asset_category?: {
    id: number
    name: string
  }
  company?: {
    id: number
    name_en: string
  }
  created_by_user?: {
    id: number
    name: string
  }
  usage_count: number
  is_global: boolean
  created_at: string
  updated_at: string
}

interface Props {
  template: AssetTemplate
  assets: Asset[]
}

const props = defineProps<Props>()

const deleteDialog = ref({
  show: false,
  loading: false
})

const imageError = ref(false)
const imageModal = ref(false)

const breadcrumbs = computed((): BreadcrumbItem[] => [
  {
    title: t('nav.dashboard'),
    href: '/dashboard',
  },
  {
    title: t('nav.asset_templates'),
    href: '/asset-templates',
  },
  {
    title: props.template.name,
    href: `/asset-templates/${props.template.id}`,
  },
])

const confirmDelete = () => {
  deleteDialog.value.show = true
}

const handleDelete = () => {
  deleteDialog.value.loading = true
  
  router.delete(route('asset-templates.destroy', props.template.id), {
    onSuccess: () => {
      router.visit(route('asset-templates.index'))
    },
    onFinish: () => {
      deleteDialog.value.loading = false
    }
  })
}

const handleImageError = () => {
  imageError.value = true
}

const openImageModal = () => {
  imageModal.value = true
}

const getStatusLabel = (status: string) => {
  const statusMap: Record<string, string> = {
    'available': t('asset_templates.available'),
    'assigned': t('asset_templates.assigned'),
    'maintenance': t('asset_templates.maintenance'),
    'retired': t('asset_templates.retired'),
    'disposed': t('asset_templates.disposed'),
  }
  return statusMap[status] || status
}

const getEmployeeName = (employee?: { first_name: string; last_name: string }) => {
  if (!employee) return '—'
  return `${employee.first_name} ${employee.last_name}`
}
</script> 