<script setup lang="ts">
import { ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

interface Department {
    id: number
    name: string
    company_name: string
}

const form = useForm({
    employee_id: '',
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    mobile: '',
    department: '',
    department_id: null as number | null,
    job_title: '',
    manager: '',
    hire_date: '',
    notes: '',
})

// Department search state
const departmentSearchQuery = ref('')
const departmentSearchResults = ref<Department[]>([])
const departmentSearchLoading = ref(false)
const showDepartmentDropdown = ref(false)
const selectedDepartment = ref<Department | null>(null)

// Debounced department search
let departmentSearchTimeout: ReturnType<typeof setTimeout> | null = null
const searchDepartments = (query: string) => {
    if (departmentSearchTimeout) {
        clearTimeout(departmentSearchTimeout)
    }
    
    departmentSearchTimeout = setTimeout(async () => {
        if (query.length < 2) {
            departmentSearchResults.value = []
            return
        }
        
        departmentSearchLoading.value = true
        
        try {
            const response = await fetch(`/api/departments/search?q=${encodeURIComponent(query)}`)
            if (response.ok) {
                departmentSearchResults.value = await response.json()
            }
        } catch (error) {
            console.error('Error searching departments:', error)
        } finally {
            departmentSearchLoading.value = false
        }
    }, 300)
}

// Watch for department search changes
watch(departmentSearchQuery, (newQuery) => {
    if (newQuery.length >= 2) {
        showDepartmentDropdown.value = true
        searchDepartments(newQuery)
    } else {
        showDepartmentDropdown.value = false
        departmentSearchResults.value = []
    }
})

const selectDepartment = (department: Department) => {
    selectedDepartment.value = department
    form.department_id = department.id
    departmentSearchQuery.value = department.name
    showDepartmentDropdown.value = false
}

const clearDepartment = () => {
    selectedDepartment.value = null
    form.department_id = null
    departmentSearchQuery.value = ''
    departmentSearchResults.value = []
    showDepartmentDropdown.value = false
}

const handleDepartmentBlur = () => {
    setTimeout(() => {
        showDepartmentDropdown.value = false
    }, 200)
}

const submit = () => {
    form.post('/employees')
}
</script>

<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto p-6">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ t('employees.create_employee') }}</h1>
                <p class="text-gray-600">{{ t('employees.create_employee_description') }}</p>
            </div>

            <form @submit.prevent="submit" class="space-y-8">
                <!-- Basic Information Section -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ t('employees.basic_information') }}</h2>
                        <p class="text-sm text-gray-600">{{ t('employees.employee_details_description') }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Employee ID -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('employees.employee_id') }}</label>
                            <input 
                                v-model="form.employee_id" 
                                type="text" 
                                :placeholder="t('employees.employee_id_placeholder')"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            <div v-if="form.errors.employee_id" class="text-red-500 text-sm mt-1">{{ form.errors.employee_id }}</div>
                        </div>

                        <!-- First Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('employees.first_name') }} *</label>
                            <input 
                                v-model="form.first_name" 
                                type="text" 
                                required
                                :placeholder="t('employees.first_name_placeholder')"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            <div v-if="form.errors.first_name" class="text-red-500 text-sm mt-1">{{ form.errors.first_name }}</div>
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('employees.last_name') }} *</label>
                            <input 
                                v-model="form.last_name" 
                                type="text" 
                                required
                                :placeholder="t('employees.last_name_placeholder')"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            <div v-if="form.errors.last_name" class="text-red-500 text-sm mt-1">{{ form.errors.last_name }}</div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('employees.email') }} *</label>
                            <input 
                                v-model="form.email" 
                                type="email" 
                                required
                                :placeholder="t('employees.email_placeholder')"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information Section -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ t('employees.contact_information') }}</h2>
                        <p class="text-sm text-gray-600">{{ t('employees.contact_information') }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Phone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('employees.phone') }}</label>
                            <input 
                                v-model="form.phone" 
                                type="text" 
                                :placeholder="t('employees.phone_placeholder')"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            <div v-if="form.errors.phone" class="text-red-500 text-sm mt-1">{{ form.errors.phone }}</div>
                        </div>

                        <!-- Mobile -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('employees.mobile') }}</label>
                            <input 
                                v-model="form.mobile" 
                                type="text" 
                                :placeholder="t('employees.mobile_placeholder')"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            <div v-if="form.errors.mobile" class="text-red-500 text-sm mt-1">{{ form.errors.mobile }}</div>
                        </div>
                    </div>
                </div>

                <!-- Job Information Section -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ t('employees.job_information') }}</h2>
                        <p class="text-sm text-gray-600">{{ t('employees.job_information') }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Department -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('employees.department') }}</label>
                            <div class="relative">
                                <div class="flex">
                                    <input 
                                        v-model="departmentSearchQuery"
                                        type="text" 
                                        :placeholder="t('employees.department_placeholder')"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        @focus="showDepartmentDropdown = departmentSearchQuery.length >= 2"
                                        @blur="handleDepartmentBlur"
                                    >
                                    <button
                                        v-if="selectedDepartment"
                                        type="button"
                                        @click="clearDepartment"
                                        class="px-3 py-2 bg-gray-100 border border-l-0 border-gray-300 rounded-r-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        :title="t('common.clear')"
                                    >
                                        ✕
                                    </button>
                                </div>
                                
                                <!-- Department Dropdown -->
                                <div v-if="showDepartmentDropdown" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                                    <div v-if="departmentSearchLoading" class="px-3 py-2 text-gray-500">
                                        {{ t('common.searching') }}...
                                    </div>
                                    <div v-else-if="departmentSearchResults.length === 0 && departmentSearchQuery.length >= 2" class="px-3 py-2 text-gray-500">
                                        {{ t('employees.no_departments_found') }}
                                    </div>
                                    <div v-else>
                                        <button
                                            v-for="department in departmentSearchResults"
                                            :key="department.id"
                                            type="button"
                                            @click="selectDepartment(department)"
                                            class="w-full px-3 py-2 text-left hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                                        >
                                            <div class="font-medium">{{ department.name }}</div>
                                            <div class="text-sm text-gray-500">{{ department.company_name }}</div>
                                        </button>
                                    </div>
                                </div>
                                
                                <div v-if="selectedDepartment" class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                    <div class="text-sm font-medium text-blue-800">{{ t('common.selected') }}: {{ selectedDepartment.name }}</div>
                                    <div class="text-xs text-blue-600">{{ selectedDepartment.company_name }}</div>
                                </div>
                            </div>
                            <div v-if="form.errors.department_id" class="text-red-500 text-sm mt-1">{{ form.errors.department_id }}</div>
                        </div>

                        <!-- Job Title -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('employees.job_title') }}</label>
                            <input 
                                v-model="form.job_title" 
                                type="text" 
                                :placeholder="t('employees.job_title_placeholder')"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            <div v-if="form.errors.job_title" class="text-red-500 text-sm mt-1">{{ form.errors.job_title }}</div>
                        </div>

                        <!-- Manager -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('employees.manager') }}</label>
                            <input 
                                v-model="form.manager" 
                                type="text" 
                                :placeholder="t('employees.manager_placeholder')"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            <div v-if="form.errors.manager" class="text-red-500 text-sm mt-1">{{ form.errors.manager }}</div>
                        </div>

                        <!-- Hire Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('employees.hire_date') }}</label>
                            <input 
                                v-model="form.hire_date" 
                                type="date" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            <div v-if="form.errors.hire_date" class="text-red-500 text-sm mt-1">{{ form.errors.hire_date }}</div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information Section -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ t('employees.additional_information') }}</h2>
                        <p class="text-sm text-gray-600">{{ t('employees.additional_information') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('employees.notes') }}</label>
                        <textarea 
                            v-model="form.notes" 
                            rows="4"
                            :placeholder="t('employees.notes_placeholder')"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        ></textarea>
                        <div v-if="form.errors.notes" class="text-red-500 text-sm mt-1">{{ form.errors.notes }}</div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                    <a href="/employees" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        {{ t('common.cancel') }}
                    </a>
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <span v-if="form.processing">{{ t('common.creating') }}</span>
                        <span v-else>{{ t('employees.create_employee') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template> 