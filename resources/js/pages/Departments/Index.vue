<script setup lang="ts">
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

interface Department {
    id: string
    name: string
    code: string
    description?: string
    company_id: string
    created_at: string
    updated_at: string
}

interface Company {
    id: number
    name_en: string
    name_ar: string
    company_email: string
}

interface Props {
    departments: {
        data: Department[]
        links: any[]
        meta: any
    }
    companies: Company[]
    filters?: {
        search?: string
    }
}

const props = defineProps<Props>()

const search = ref(props.filters?.search || '')

// Debounced search
let searchTimeout: number
watch(search, () => {
    clearTimeout(searchTimeout)
    searchTimeout = window.setTimeout(() => {
        router.get('/departments', {
            search: search.value || undefined,
        }, {
            preserveState: true,
            replace: true,
        })
    }, 300)
})

const clearFilters = () => {
    search.value = ''
}
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto p-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">{{ t('departments.title') }}</h1>
                <a 
                    href="/departments/create" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                >
                    {{ t('departments.create_department') }}
                </a>
            </div>

            <!-- Search and Filters -->
            <div class="flex gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                <div class="flex-1">
                    <input
                        v-model="search"
                        type="text"
                        :placeholder="t('departments.search_placeholder')"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                </div>
                <button 
                    v-if="search"
                    @click="clearFilters" 
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                >
                    {{ t('departments.clear') }}
                </button>
            </div>

            <!-- Empty State -->
            <div v-if="departments.data.length === 0" class="text-center py-12">
                <div class="text-gray-400 text-6xl mb-4">🏢</div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    {{ search ? t('departments.no_departments_found') : t('departments.no_departments') }}
                </h3>
                <p class="text-gray-600 mb-6">
                    {{ search ? t('departments.try_adjusting_search') : companies.length > 0 ? t('departments.create_first_department') : t('departments.create_company_first') }}
                </p>
                <a 
                    v-if="!search && companies.length > 0"
                    href="/departments/create" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                >
                    {{ t('departments.create_department') }}
                </a>
                <a 
                    v-else-if="!search && companies.length === 0"
                    href="/companies/create" 
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                >
                    {{ t('departments.create_company') }}
                </a>
            </div>

            <!-- Departments Table -->
            <div v-else class="bg-white rounded-lg border overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ t('departments.department') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ t('departments.code') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ t('departments.description') }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ t('departments.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="department in departments.data" :key="department.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ department.name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500 font-mono">{{ department.code }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-500">{{ department.description || '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex gap-2">
                                    <a 
                                        :href="`/departments/${department.id}`" 
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        {{ t('departments.view') }}
                                    </a>
                                    <a 
                                        :href="`/departments/${department.id}/edit`" 
                                        class="text-gray-600 hover:text-gray-900"
                                    >
                                        {{ t('departments.edit') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="departments.links.length > 3" class="px-6 py-3 border-t">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-700">
                            {{ t('departments.showing_results', { 
                                from: departments.meta.from || 0, 
                                to: departments.meta.to || 0, 
                                total: departments.meta.total 
                            }) }}
                        </div>
                        <div class="flex gap-1">
                            <a
                                v-for="link in departments.links"
                                :key="link.label"
                                :href="link.url"
                                v-html="link.label"
                                class="px-3 py-2 text-sm border rounded"
                                :class="{
                                    'bg-blue-600 text-white border-blue-600': link.active,
                                    'text-gray-700 border-gray-300 hover:bg-gray-50': !link.active && link.url,
                                    'text-gray-400 border-gray-200 cursor-not-allowed': !link.url
                                }"
                            ></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 