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
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
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

interface AssetTemplate {
  id: number
  name: string
  manufacturer?: string
  model_name?: string
  model_number?: string
  default_notes?: string
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
}

const props = defineProps<Props>()

const deleteDialog = ref({
  show: false,
  loading: false
})

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
</script> 