<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <Heading :title="t('asset_categories.create')" />

      <div class="max-w-2xl">
        <form @submit.prevent="submit" class="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>{{ t('asset_categories.form.category_details') }}</CardTitle>
              <CardDescription>{{ t('asset_categories.form.category_details_description') }}</CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
              <!-- Name -->
              <div>
                <Label for="name" class="required">{{ t('asset_categories.form.name') }}</Label>
                <Input
                  id="name"
                  v-model="form.name"
                  type="text"
                  :placeholder="t('asset_categories.form.name_placeholder')"
                  :class="{ 'border-red-500': form.errors.name }"
                />
                <InputError :message="form.errors.name" />
              </div>

              <!-- Code -->
              <div>
                <Label for="code">{{ t('asset_categories.form.code') }}</Label>
                <Input
                  id="code"
                  v-model="form.code"
                  type="text"
                  :placeholder="t('asset_categories.form.code_placeholder')"
                  :class="{ 'border-red-500': form.errors.code }"
                />
                <InputError :message="form.errors.code" />
                <p class="text-sm text-gray-600 mt-1">{{ t('asset_categories.form.code_help') }}</p>
              </div>

              <!-- Parent Category -->
              <div>
                <Label for="parent_id">{{ t('asset_categories.form.parent_category') }}</Label>
                <select
                  id="parent_id"
                  v-model="form.parent_id"
                  class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  :class="{ 'border-red-500': form.errors.parent_id }"
                >
                  <option value="">{{ t('asset_categories.form.no_parent') }}</option>
                  <option 
                    v-for="category in parentCategories" 
                    :key="category.id" 
                    :value="category.id"
                  >
                    {{ '—'.repeat(category.depth || 0) }} {{ category.name }}
                  </option>
                </select>
                <InputError :message="form.errors.parent_id" />
                <p class="text-sm text-gray-600 mt-1">{{ t('asset_categories.form.parent_help') }}</p>
              </div>

              <!-- Description -->
              <div>
                <Label for="description">{{ t('asset_categories.form.description') }}</Label>
                <textarea
                  id="description"
                  v-model="form.description"
                  rows="3"
                  class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  :placeholder="t('asset_categories.form.description_placeholder')"
                  :class="{ 'border-red-500': form.errors.description }"
                ></textarea>
                <InputError :message="form.errors.description" />
              </div>

              <!-- Icon and Color -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <Label for="icon">{{ t('asset_categories.form.icon') }}</Label>
                  <Input
                    id="icon"
                    v-model="form.icon"
                    type="text"
                    :placeholder="t('asset_categories.form.icon_placeholder')"
                    :class="{ 'border-red-500': form.errors.icon }"
                  />
                  <InputError :message="form.errors.icon" />
                  <p class="text-sm text-gray-600 mt-1">{{ t('asset_categories.form.icon_help') }}</p>
                </div>

                <div>
                  <Label for="color">{{ t('asset_categories.form.color') }}</Label>
                  <div class="flex space-x-2">
                    <Input
                      id="color"
                      v-model="form.color"
                      type="color"
                      class="w-16 h-10"
                      :class="{ 'border-red-500': form.errors.color }"
                    />
                    <Input
                      v-model="form.color"
                      type="text"
                      class="flex-1"
                      :placeholder="t('asset_categories.form.color_placeholder')"
                      :class="{ 'border-red-500': form.errors.color }"
                    />
                  </div>
                  <InputError :message="form.errors.color" />
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Actions -->
          <div class="flex items-center justify-between">
            <Button
              type="button"
              variant="outline"
              asChild
            >
              <Link :href="route('asset-categories.index')">
                {{ t('common.cancel') }}
              </Link>
            </Button>
            
            <Button
              type="submit"
              :disabled="form.processing"
            >
              <Icon v-if="form.processing" name="LoaderCircle" class="mr-2 h-4 w-4 animate-spin" />
              {{ form.processing ? t('common.creating') : t('common.create') }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'
import type { AssetCategory, BreadcrumbItem } from '@/types'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import Heading from '@/components/Heading.vue'
import InputError from '@/components/InputError.vue'
import Icon from '@/components/Icon.vue'

const { t } = useI18n()

interface Props {
  parentCategories: AssetCategory[]
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
  {
    title: t('asset_categories.create'),
    href: '/asset-categories/create',
  },
])

const form = useForm({
  name: '',
  code: '',
  description: '',
  icon: '',
  color: '#6b7280',
  parent_id: '' as string
})

const submit = () => {
  form.post(route('asset-categories.store'))
}
</script>

<style scoped>
.required::after {
  content: ' *';
  color: rgb(239 68 68);
}
</style> 