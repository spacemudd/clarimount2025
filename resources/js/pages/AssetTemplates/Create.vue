<template>
  <app-layout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
          {{ t('asset_templates.create_template') }}
        </h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
          {{ t('asset_templates.create_template_description') }}
        </p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle>{{ t('asset_templates.template_details') }}</CardTitle>
          </CardHeader>
          <CardContent class="space-y-6">
            <!-- Template Name -->
            <div>
              <Label for="name">{{ t('asset_templates.template_name') }} *</Label>
              <Input
                id="name"
                v-model="form.name"
                required
                :placeholder="t('asset_templates.template_name_placeholder')"
                class="mt-1"
              />
              <InputError v-if="errors.name" :message="errors.name" class="mt-1" />
            </div>

            <!-- Manufacturer -->
            <div>
              <Label for="manufacturer">{{ t('asset_templates.manufacturer') }}</Label>
              <Input
                id="manufacturer"
                v-model="form.manufacturer"
                :placeholder="t('asset_templates.manufacturer_placeholder')"
                class="mt-1"
              />
              <InputError v-if="errors.manufacturer" :message="errors.manufacturer" class="mt-1" />
            </div>

            <!-- Model Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <Label for="model_name">{{ t('asset_templates.model_name') }}</Label>
                <Input
                  id="model_name"
                  v-model="form.model_name"
                  :placeholder="t('asset_templates.model_name_placeholder')"
                  class="mt-1"
                />
                <InputError v-if="errors.model_name" :message="errors.model_name" class="mt-1" />
              </div>
              <div>
                <Label for="model_number">{{ t('asset_templates.model_number') }}</Label>
                <Input
                  id="model_number"
                  v-model="form.model_number"
                  :placeholder="t('asset_templates.model_number_placeholder')"
                  class="mt-1"
                />
                <InputError v-if="errors.model_number" :message="errors.model_number" class="mt-1" />
              </div>
            </div>

            <!-- Category -->
            <div>
              <Label for="asset_category_id">{{ t('asset_templates.category') }}</Label>
              <select
                id="asset_category_id"
                v-model="form.asset_category_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
              >
                <option value="">{{ t('asset_templates.category_placeholder') }}</option>
                <option 
                  v-for="category in categories" 
                  :key="category.id" 
                  :value="category.id"
                >
                  {{ '—'.repeat(Math.max(0, category.depth || 0)) }} {{ category.name }}
                </option>
              </select>
              <InputError v-if="errors.asset_category_id" :message="errors.asset_category_id" class="mt-1" />
            </div>

            <!-- Notes -->
            <div>
              <Label for="default_notes">{{ t('asset_templates.default_notes') }}</Label>
              <textarea
                id="default_notes"
                v-model="form.default_notes"
                rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                :placeholder="t('asset_templates.default_notes_placeholder')"
              />
              <InputError v-if="errors.default_notes" :message="errors.default_notes" class="mt-1" />
            </div>

            <!-- Image Upload -->
            <div>
              <Label for="image">{{ t('asset_templates.template_image') }}</Label>
              <input
                id="image"
                type="file"
                accept="image/*"
                @change="handleImageChange"
                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-300"
              />
              <p class="mt-1 text-sm text-gray-500">{{ t('asset_templates.image_description') }}</p>
              <InputError v-if="errors.image" :message="errors.image" class="mt-1" />
              
              <!-- Image Preview -->
              <div v-if="imagePreview" class="mt-4">
                <img 
                  :src="imagePreview" 
                  :alt="t('asset_templates.image_preview')" 
                  class="max-w-xs max-h-48 rounded-lg border border-gray-300 dark:border-gray-600"
                />
                <button 
                  type="button" 
                  @click="removeImage"
                  class="mt-2 text-sm text-red-600 hover:text-red-800 dark:text-red-400"
                >
                  {{ t('asset_templates.remove_image') }}
                </button>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Actions -->
        <div class="mt-6 flex items-center gap-4">
          <Button type="submit" :disabled="processing">
            <Icon v-if="processing" name="loader-2" class="h-4 w-4 mr-2 animate-spin" />
            {{ t('asset_templates.create_template') }}
          </Button>
          <Button variant="outline" as="Link" :href="route('asset-templates.index')">
            {{ t('common.cancel') }}
          </Button>
        </div>
      </form>
    </div>
  </app-layout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from '@/components/InputError.vue'
import Icon from '@/components/Icon.vue'

interface Props {
  categories: Array<{
    id: number
    name: string
    depth: number
  }>
}

const props = defineProps<Props>()
const { t } = useI18n()

const form = useForm({
  name: '',
  manufacturer: '',
  model_name: '',
  model_number: '',
  asset_category_id: '',
  specifications: {},
  default_notes: '',
  image: null as File | null,
})

const processing = ref(false)
const errors = ref<Record<string, string>>({})
const imagePreview = ref<string | null>(null)

const handleSubmit = () => {
  processing.value = true
  errors.value = {}
  
  form.post(route('asset-templates.store'), {
    onSuccess: () => {
      processing.value = false
    },
    onError: (formErrors) => {
      errors.value = formErrors
      processing.value = false
    },
    onFinish: () => {
      processing.value = false
    }
  })
}

const handleImageChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  
  if (file) {
    form.image = file
    
    // Create preview URL
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

const removeImage = () => {
  form.image = null
  imagePreview.value = null
  
  // Clear the file input
  const input = document.getElementById('image') as HTMLInputElement
  if (input) {
    input.value = ''
  }
}
</script> 