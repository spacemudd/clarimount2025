<script setup lang="ts">
import { ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

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
        <div class="max-w-2xl mx-auto p-6">
            <h1 class="text-2xl font-bold mb-6">Create Employee</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Employee ID</label>
                    <input 
                        v-model="form.employee_id" 
                        type="text" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                    <div v-if="form.errors.employee_id" class="text-red-500 text-sm mt-1">{{ form.errors.employee_id }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">First Name *</label>
                    <input 
                        v-model="form.first_name" 
                        type="text" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                    <div v-if="form.errors.first_name" class="text-red-500 text-sm mt-1">{{ form.errors.first_name }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Last Name *</label>
                    <input 
                        v-model="form.last_name" 
                        type="text" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                    <div v-if="form.errors.last_name" class="text-red-500 text-sm mt-1">{{ form.errors.last_name }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Email *</label>
                    <input 
                        v-model="form.email" 
                        type="email" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                    <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Phone</label>
                    <input 
                        v-model="form.phone" 
                        type="text" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                    <div v-if="form.errors.phone" class="text-red-500 text-sm mt-1">{{ form.errors.phone }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Mobile</label>
                    <input 
                        v-model="form.mobile" 
                        type="text" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                    <div v-if="form.errors.mobile" class="text-red-500 text-sm mt-1">{{ form.errors.mobile }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Department</label>
                    <div class="relative">
                        <div class="flex">
                            <input 
                                v-model="departmentSearchQuery"
                                type="text" 
                                placeholder="Search for a department..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                @focus="showDepartmentDropdown = departmentSearchQuery.length >= 2"
                                @blur="handleDepartmentBlur"
                            >
                            <button
                                v-if="selectedDepartment"
                                type="button"
                                @click="clearDepartment"
                                class="px-3 py-2 bg-gray-100 border border-l-0 border-gray-300 rounded-r-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                ✕
                            </button>
                        </div>
                        
                        <!-- Department Dropdown -->
                        <div v-if="showDepartmentDropdown" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                            <div v-if="departmentSearchLoading" class="px-3 py-2 text-gray-500">
                                Searching...
                            </div>
                            <div v-else-if="departmentSearchResults.length === 0 && departmentSearchQuery.length >= 2" class="px-3 py-2 text-gray-500">
                                No departments found
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
                        
                        <div v-if="selectedDepartment" class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-md">
                            <div class="text-sm font-medium text-blue-800">Selected: {{ selectedDepartment.name }}</div>
                            <div class="text-xs text-blue-600">{{ selectedDepartment.company_name }}</div>
                        </div>
                    </div>
                    <div v-if="form.errors.department_id" class="text-red-500 text-sm mt-1">{{ form.errors.department_id }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Job Title</label>
                    <input 
                        v-model="form.job_title" 
                        type="text" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                    <div v-if="form.errors.job_title" class="text-red-500 text-sm mt-1">{{ form.errors.job_title }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Manager</label>
                    <input 
                        v-model="form.manager" 
                        type="text" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                    <div v-if="form.errors.manager" class="text-red-500 text-sm mt-1">{{ form.errors.manager }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Hire Date</label>
                    <input 
                        v-model="form.hire_date" 
                        type="date" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                    <div v-if="form.errors.hire_date" class="text-red-500 text-sm mt-1">{{ form.errors.hire_date }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Notes</label>
                    <textarea 
                        v-model="form.notes" 
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    ></textarea>
                    <div v-if="form.errors.notes" class="text-red-500 text-sm mt-1">{{ form.errors.notes }}</div>
                </div>

                <div class="flex gap-4 pt-4">
                    <a href="/employees" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                    >
                        <span v-if="form.processing">Saving...</span>
                        <span v-else>Create Employee</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template> 