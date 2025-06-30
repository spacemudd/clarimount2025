<template>
  <app-layout>
    <div class="p-6">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
          Edit Template
        </h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
          Update this asset template
        </p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="max-w-2xl">
        <Card>
          <CardHeader>
            <CardTitle>Template Details</CardTitle>
          </CardHeader>
          <CardContent class="space-y-6">
            <!-- Template Name -->
            <div>
              <Label for="name">Template Name *</Label>
              <Input
                id="name"
                v-model="form.name"
                required
                placeholder="Dell Latitude 7420 Laptop"
                class="mt-1"
              />
              <InputError v-if="errors.name" :message="errors.name" class="mt-1" />
            </div>

            <!-- Manufacturer -->
            <div>
              <Label for="manufacturer">Manufacturer</Label>
              <Input
                id="manufacturer"
                v-model="form.manufacturer"
                placeholder="Dell, HP, Apple..."
                class="mt-1"
              />
              <InputError v-if="errors.manufacturer" :message="errors.manufacturer" class="mt-1" />
            </div>

            <!-- Model Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <Label for="model_name">Model Name</Label>
                <Input
                  id="model_name"
                  v-model="form.model_name"
                  placeholder="Latitude 7420"
                  class="mt-1"
                />
                <InputError v-if="errors.model_name" :message="errors.model_name" class="mt-1" />
              </div>
              <div>
                <Label for="model_number">Model Number</Label>
                <Input
                  id="model_number"
                  v-model="form.model_number"
                  placeholder="LAT7420-001"
                  class="mt-1"
                />
                <InputError v-if="errors.model_number" :message="errors.model_number" class="mt-1" />
              </div>
            </div>

            <!-- Category -->
            <div>
              <Label for="asset_category_id">Category</Label>
              <select
                id="asset_category_id"
                v-model="form.asset_category_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
              >
                <option value="">Select a category</option>
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

            <!-- Company -->
            <div>
              <Label for="company_id">Company</Label>
              <select
                id="company_id"
                v-model="form.company_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
              >
                <option value="">Select a company</option>
                <option 
                  v-for="company in companies" 
                  :key="company.id" 
                  :value="company.id"
                >
                  {{ company.name_en }}
                </option>
              </select>
              <InputError v-if="errors.company_id" :message="errors.company_id" class="mt-1" />
            </div>

            <!-- Notes -->
            <div>
              <Label for="default_notes">Default Notes</Label>
              <textarea
                id="default_notes"
                v-model="form.default_notes"
                rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                placeholder="Additional notes about this template"
              />
              <InputError v-if="errors.default_notes" :message="errors.default_notes" class="mt-1" />
            </div>

            <!-- Global Template -->
            <div class="flex items-center space-x-2">
              <input
                id="is_global"
                v-model="form.is_global"
                type="checkbox"
                class="rounded border-gray-300 text-blue-600 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
              />
              <Label for="is_global" class="text-sm font-medium">
                Make this template available to all companies (Global)
              </Label>
              <InputError v-if="errors.is_global" :message="errors.is_global" class="mt-1" />
            </div>
          </CardContent>
        </Card>

        <!-- Actions -->
        <div class="mt-6 flex items-center gap-4">
          <Button type="submit" :disabled="processing">
            <Icon v-if="processing" name="loader-2" class="h-4 w-4 mr-2 animate-spin" />
            Update Template
          </Button>
          <Button variant="outline" as="Link" :href="route('asset-templates.show', template.id)">
            Cancel
          </Button>
        </div>
      </form>
    </div>
  </app-layout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from '@/components/InputError.vue'
import Icon from '@/components/Icon.vue'

interface Props {
  template: {
    id: number
    name: string
    manufacturer?: string
    model_name?: string
    model_number?: string
    asset_category_id: number
    company_id?: number
    default_notes?: string
    is_global: boolean
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
}

const props = defineProps<Props>()

const form = useForm({
  name: props.template.name,
  manufacturer: props.template.manufacturer || '',
  model_name: props.template.model_name || '',
  model_number: props.template.model_number || '',
  asset_category_id: props.template.asset_category_id,
  company_id: props.template.company_id || '',
  specifications: {},
  default_notes: props.template.default_notes || '',
  is_global: props.template.is_global,
})

const processing = ref(false)
const errors = ref<Record<string, string>>({})

const handleSubmit = () => {
  processing.value = true
  errors.value = {}
  
  form.put(route('asset-templates.update', props.template.id), {
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
</script> 