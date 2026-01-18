<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <Heading :title="t('asset_categories.title')" />
        <Button asChild class="bg-blue-600 hover:bg-blue-700 text-white font-semibold">
          <Link :href="route('asset-categories.create')">
            <Icon name="plus" class="mr-2 h-4 w-4" />
            {{ t('asset_categories.create') }}
          </Link>
        </Button>
      </div>

      <!-- Search and Filters -->
      <Card>
        <CardContent class="p-6">
          <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
              <Input
                v-model="search"
                type="text"
                :placeholder="t('asset_categories.search_placeholder')"
                class="w-full"
              />
            </div>
            <div class="flex gap-2">
              <Button 
                variant="outline"
                @click="clearFilters"
              >
                {{ t('common.clear') }}
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Categories Tree -->
      <Card>
        <CardHeader>
          <CardTitle>{{ t('asset_categories.list') }}</CardTitle>
        </CardHeader>
        <CardContent class="p-0">
          <div v-if="filteredCategories.length > 0" class="divide-y">
            <div 
              v-for="category in filteredCategories" 
              :key="category.id"
              class="p-6 hover:bg-gray-50 transition-colors"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <!-- Indentation for nested categories -->
                  <div 
                    :style="{ paddingLeft: `${(category.depth || 0) * 20}px` }"
                    class="flex items-center space-x-3"
                  >
                    <!-- Icon -->
                    <div 
                      class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-sm font-medium"
                      :style="{ backgroundColor: category.color || '#6b7280' }"
                    >
                      <Icon 
                        v-if="category.icon" 
                        :name="category.icon" 
                        class="h-4 w-4" 
                      />
                      <span v-else>{{ category.name.charAt(0).toUpperCase() }}</span>
                    </div>
                    
                    <!-- Category Info -->
                    <div>
                      <div class="flex items-center space-x-2">
                        <h4 class="font-medium text-gray-900">{{ category.name }}</h4>
                        <Badge v-if="category.code" variant="secondary">{{ category.code }}</Badge>
                      </div>
                      <p v-if="category.description" class="text-sm text-gray-600 mt-1">
                        {{ category.description }}
                      </p>
                      <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                        <span>{{ t('asset_categories.assets_count', { count: category.assets_count || 0 }) }}</span>
                        <span>{{ t('common.created_at') }}: {{ formatDate(category.created_at) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Actions -->
                <div class="flex items-center space-x-2">
                  <Button
                    variant="ghost"
                    size="sm"
                    asChild
                  >
                    <Link :href="route('asset-categories.show', category.id)">
                      <Icon name="eye" class="h-4 w-4" />
                    </Link>
                  </Button>
                  <Button
                    variant="ghost"
                    size="sm"
                    asChild
                  >
                    <Link :href="route('asset-categories.edit', category.id)">
                      <Icon name="edit" class="h-4 w-4" />
                    </Link>
                  </Button>
                  <Button
                    variant="ghost"
                    size="sm"
                    @click="confirmDelete(category)"
                    class="text-red-600 hover:text-red-700"
                  >
                    <Icon name="trash-2" class="h-4 w-4" />
                  </Button>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Empty State -->
          <div v-else class="p-12 text-center">
            <Icon name="folder" class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-4 text-lg font-medium text-gray-900">{{ t('asset_categories.empty.title') }}</h3>
            <p class="mt-2 text-gray-600">{{ t('asset_categories.empty.description') }}</p>
            <Button 
              asChild
              class="mt-4"
            >
              <Link :href="route('asset-categories.create')">
                <Icon name="plus" class="h-4 w-4 mr-2" />
                {{ t('asset_categories.create_first') }}
              </Link>
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Delete Confirmation Dialog -->
    <Dialog v-model:open="showDeleteDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{{ t('asset_categories.delete.title') }}</DialogTitle>
          <DialogDescription>
            {{ t('asset_categories.delete.description', { name: categoryToDelete?.name }) }}
          </DialogDescription>
        </DialogHeader>
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
import type { AssetCategory, BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import Heading from '@/components/Heading.vue'
import Icon from '@/components/Icon.vue'

const { t } = useI18n()

interface Props {
  categories: AssetCategory[]
  company: any
}

const props = defineProps<Props>()

// Breadcrumbs
const breadcrumbs = computed((): BreadcrumbItem[] => [
  {
    title: t('nav.dashboard'),
    href: '/dashboard',
  },
  {
    title: t('asset_categories.title'),
    href: '/asset-categories',
  },
])

// Reactive state
const search = ref('')
const showDeleteDialog = ref(false)
const categoryToDelete = ref<AssetCategory | null>(null)
const deleting = ref(false)

// Computed
const filteredCategories = computed(() => {
  if (!search.value) return props.categories
  
  const query = search.value.toLowerCase()
  return props.categories.filter(category => 
    category.name.toLowerCase().includes(query) ||
    category.code?.toLowerCase().includes(query) ||
    category.description?.toLowerCase().includes(query)
  )
})

// Methods
const clearFilters = () => {
  search.value = ''
}

const confirmDelete = (category: AssetCategory) => {
  categoryToDelete.value = category
  showDeleteDialog.value = true
}

const deleteCategory = () => {
  if (!categoryToDelete.value) return
  
  deleting.value = true
  
  router.delete(route('asset-categories.destroy', categoryToDelete.value.id), {
    onSuccess: () => {
      showDeleteDialog.value = false
      categoryToDelete.value = null
    },
    onFinish: () => {
      deleting.value = false
    }
  })
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString()
}
</script> 