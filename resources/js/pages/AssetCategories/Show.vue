<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div 
            class="w-12 h-12 rounded-lg flex items-center justify-center text-white text-lg font-medium"
            :style="{ backgroundColor: category.color || '#6b7280' }"
          >
            <Icon 
              v-if="category.icon" 
              :name="category.icon" 
              class="h-6 w-6" 
            />
            <span v-else>{{ category.name?.charAt(0).toUpperCase() }}</span>
          </div>
          <div>
            <Heading :title="category.name" />
            <div class="flex items-center space-x-2 mt-1">
              <Badge v-if="category.code" variant="secondary">{{ category.code }}</Badge>
              <Badge v-if="category.parent" variant="outline">
                {{ t('asset_categories.child_of', { parent: category.parent.name }) }}
              </Badge>
            </div>
          </div>
        </div>
        <div class="flex items-center space-x-2">
          <Button variant="outline" asChild>
            <Link :href="route('asset-categories.edit', category.id)">
              <Icon name="edit" class="mr-2 h-4 w-4" />
              {{ t('common.edit') }}
            </Link>
          </Button>
          <Button variant="outline" @click="confirmDelete">
            <Icon name="trash-2" class="mr-2 h-4 w-4" />
            {{ t('common.delete') }}
          </Button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Details -->
          <Card>
            <CardHeader>
              <CardTitle>{{ t('asset_categories.details') }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div v-if="category.description">
                <Label>{{ t('asset_categories.form.description') }}</Label>
                <p class="text-gray-700 dark:text-gray-300 mt-1">{{ category.description }}</p>
              </div>
              
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <Label>{{ t('common.created_at') }}</Label>
                  <p class="text-gray-700 dark:text-gray-300 mt-1">{{ formatDate(category.created_at) }}</p>
                </div>
                <div>
                  <Label>{{ t('common.updated_at') }}</Label>
                  <p class="text-gray-700 dark:text-gray-300 mt-1">{{ formatDate(category.updated_at) }}</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Subcategories -->
          <Card v-if="category.children && category.children.length > 0">
            <CardHeader>
              <CardTitle>{{ t('asset_categories.subcategories') }}</CardTitle>
              <CardDescription>
                {{ t('asset_categories.subcategories_description', { count: category.children.length }) }}
              </CardDescription>
            </CardHeader>
            <CardContent class="p-0">
              <div class="divide-y">
                <div 
                  v-for="child in category.children" 
                  :key="child.id"
                  class="p-4 hover:bg-gray-50 transition-colors"
                >
                  <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                      <div 
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-sm font-medium"
                        :style="{ backgroundColor: child.color || '#6b7280' }"
                      >
                        <Icon 
                          v-if="child.icon" 
                          :name="child.icon" 
                          class="h-4 w-4" 
                        />
                        <span v-else>{{ child.name.charAt(0).toUpperCase() }}</span>
                      </div>
                      <div>
                        <h4 class="font-medium">{{ child.name }}</h4>
                        <p v-if="child.description" class="text-sm text-gray-600 mt-1">
                          {{ child.description }}
                        </p>
                        <div class="flex items-center space-x-4 mt-1 text-sm text-gray-500">
                          <span>{{ t('asset_categories.assets_count', { count: child.assets_count || 0 }) }}</span>
                          <Badge v-if="child.code" variant="secondary" class="text-xs">{{ child.code }}</Badge>
                        </div>
                      </div>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                      <Button
                        variant="ghost"
                        size="sm"
                        asChild
                      >
                        <Link :href="route('asset-categories.show', child.id)">
                          <Icon name="eye" class="h-4 w-4" />
                        </Link>
                      </Button>
                      <Button
                        variant="ghost"
                        size="sm"
                        asChild
                      >
                        <Link :href="route('asset-categories.edit', child.id)">
                          <Icon name="edit" class="h-4 w-4" />
                        </Link>
                      </Button>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="p-4 border-t bg-gray-50">
                <Button variant="outline" size="sm" asChild>
                  <Link :href="route('asset-categories.create', { parent: category.id })">
                    <Icon name="plus" class="h-4 w-4 mr-2" />
                    {{ t('asset_categories.add_subcategory') }}
                  </Link>
                </Button>
              </div>
            </CardContent>
          </Card>

          <!-- Recent Assets -->
          <Card v-if="recentAssets && recentAssets.length > 0">
            <CardHeader>
              <CardTitle>{{ t('asset_categories.recent_assets') }}</CardTitle>
              <CardDescription>{{ t('asset_categories.recent_assets_description') }}</CardDescription>
            </CardHeader>
            <CardContent class="p-0">
              <div class="divide-y">
                <div 
                  v-for="asset in recentAssets" 
                  :key="asset.id"
                  class="p-4 hover:bg-gray-50 transition-colors"
                >
                  <div class="flex items-center justify-between">
                    <div>
                      <h4 class="font-medium">{{ asset.model_name }}</h4>
                      <div class="flex items-center space-x-4 mt-1 text-sm text-gray-600">
                        <span>{{ asset.asset_tag }}</span>
                        <span v-if="asset.serial_number">{{ asset.serial_number }}</span>
                        <span v-if="asset.location">{{ asset.location.name }}</span>
                      </div>
                    </div>
                    <Badge :variant="getAssetStatusVariant(asset.status)">
                      {{ t(`assets.status_${asset.status}`) }}
                    </Badge>
                  </div>
                </div>
              </div>
              
              <div class="p-4 border-t bg-gray-50">
                <Button variant="outline" size="sm" asChild>
                  <Link :href="route('assets.index', { category: category.id })">
                    {{ t('asset_categories.view_all_assets') }}
                  </Link>
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Statistics -->
          <Card>
            <CardHeader>
              <CardTitle>{{ t('asset_categories.statistics') }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="flex items-center justify-between">
                <span class="text-gray-600">{{ t('asset_categories.total_assets') }}</span>
                <span class="font-semibold text-2xl">{{ statistics.total_assets }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-gray-600">{{ t('asset_categories.subcategories') }}</span>
                <span class="font-semibold text-2xl">{{ statistics.subcategories_count }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-gray-600">{{ t('asset_categories.recent_activity') }}</span>
                <span class="font-semibold text-2xl">{{ statistics.recent_activity }}</span>
              </div>
            </CardContent>
          </Card>

          <!-- Hierarchy -->
          <Card v-if="hierarchy && hierarchy.length > 0">
            <CardHeader>
              <CardTitle>{{ t('asset_categories.hierarchy') }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-2">
              <div
                v-for="(item, index) in hierarchy"
                :key="item.id"
                :class="[
                  'flex items-center space-x-2 p-2 rounded-lg transition-colors',
                  index === hierarchy.length - 1 ? 'bg-blue-50 border border-blue-200' : 'hover:bg-gray-50'
                ]"
              >
                <div 
                  class="w-6 h-6 rounded flex items-center justify-center text-white text-xs font-medium"
                  :style="{ backgroundColor: item.color || '#6b7280' }"
                >
                  <Icon 
                    v-if="item.icon" 
                    :name="item.icon" 
                    class="h-3 w-3" 
                  />
                  <span v-else>{{ item.name.charAt(0).toUpperCase() }}</span>
                </div>
                <span 
                  :class="[
                    'text-sm',
                    index === hierarchy.length - 1 ? 'font-medium text-blue-700' : 'text-gray-700'
                  ]"
                >
                  {{ item.name }}
                </span>
              </div>
            </CardContent>
          </Card>

          <!-- Quick Actions -->
          <Card>
            <CardHeader>
              <CardTitle>{{ t('asset_categories.quick_actions') }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-2">
              <Button variant="outline" size="sm" class="w-full justify-start" asChild>
                <Link :href="route('assets.create', { category: category.id })">
                  <Icon name="plus" class="h-4 w-4 mr-2" />
                  {{ t('asset_categories.create_asset') }}
                </Link>
              </Button>
              <Button variant="outline" size="sm" class="w-full justify-start" asChild>
                <Link :href="route('asset-categories.create', { parent: category.id })">
                  <Icon name="folder-plus" class="h-4 w-4 mr-2" />
                  {{ t('asset_categories.add_subcategory') }}
                </Link>
              </Button>
              <Button variant="outline" size="sm" class="w-full justify-start" asChild>
                <Link :href="route('assets.index', { category: category.id })">
                  <Icon name="list" class="h-4 w-4 mr-2" />
                  {{ t('asset_categories.view_assets') }}
                </Link>
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Dialog -->
    <Dialog v-model:open="showDeleteDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{{ t('asset_categories.delete.title') }}</DialogTitle>
          <DialogDescription>
            {{ t('asset_categories.delete.description', { name: category.name }) }}
          </DialogDescription>
        </DialogHeader>
        <div v-if="category.children && category.children.length > 0" class="my-4">
          <div class="p-4 bg-red-50 rounded-lg">
            <p class="text-sm text-red-700 font-medium mb-2">
              {{ t('asset_categories.delete.has_children_warning') }}
            </p>
            <ul class="text-sm text-red-600 list-disc list-inside">
              <li v-for="child in category.children" :key="child.id">
                {{ child.name }}
              </li>
            </ul>
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="showDeleteDialog = false">
            {{ t('common.cancel') }}
          </Button>
          <Button 
            variant="destructive" 
            @click="deleteCategory"
            :disabled="deleting"
          >
            {{ deleting ? t('common.deleting') : t('common.delete') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import type { AssetCategory, Asset, BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import Heading from '@/components/Heading.vue'
import Icon from '@/components/Icon.vue'

const { t } = useI18n()

interface Props {
  category: AssetCategory
  statistics: {
    total_assets: number
    subcategories_count: number
    recent_activity: number
  }
  hierarchy: AssetCategory[]
  recentAssets?: Asset[]
  company: any
}

const props = defineProps<Props>()

// Breadcrumbs
const breadcrumbs = computed((): BreadcrumbItem[] => {
  const items: BreadcrumbItem[] = [
    {
      title: t('nav.dashboard'),
      href: '/dashboard',
    },
    {
      title: t('asset_categories.title'),
      href: '/asset-categories',
    },
  ]
  
  // Add hierarchy breadcrumbs
  if (props.hierarchy) {
    props.hierarchy.forEach((item, index) => {
      if (index < props.hierarchy.length - 1) {
        items.push({
          title: item.name,
          href: `/asset-categories/${item.id}`,
        })
      }
    })
  }
  
  // Add current category
  items.push({
    title: props.category.name,
    href: `/asset-categories/${props.category.id}`,
  })
  
  return items
})

// Reactive state
const showDeleteDialog = ref(false)
const deleting = ref(false)

// Methods
const confirmDelete = () => {
  showDeleteDialog.value = true
}

const deleteCategory = () => {
  deleting.value = true
  
  router.delete(route('asset-categories.destroy', props.category.id), {
    onSuccess: () => {
      // Redirect to parent or index
      const redirectUrl = props.category.parent_id 
        ? route('asset-categories.show', props.category.parent_id)
        : route('asset-categories.index')
      router.visit(redirectUrl)
    },
    onFinish: () => {
      deleting.value = false
    }
  })
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString()
}

const getAssetStatusVariant = (status: string) => {
  switch (status) {
    case 'available':
      return 'default'
    case 'assigned':
      return 'secondary'
    case 'maintenance':
      return 'destructive'
    case 'retired':
      return 'outline'
    default:
      return 'secondary'
  }
}
</script> 