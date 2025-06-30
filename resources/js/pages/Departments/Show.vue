<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Department {
    id: string
    name: string
    code: string
    description?: string
    company_id: string
    created_at: string
    updated_at: string
    company: {
        id: number
        name: string
    }
    employees?: any[]
}

interface Props {
    department: Department
}

const props = defineProps<Props>()

const deleteDepartment = () => {
    if (confirm('Are you sure you want to delete this department?')) {
        router.delete(`/departments/${props.department.id}`)
    }
}
</script>

<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold">{{ department.name }}</h1>
                    <p class="text-gray-600">Department Code: {{ department.code }}</p>
                </div>
                <div class="flex gap-2">
                    <a 
                        :href="`/departments/${department.id}/edit`" 
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                    >
                        Edit
                    </a>
                    <button 
                        @click="deleteDepartment"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                    >
                        Delete
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-lg border p-6">
                <h2 class="text-lg font-medium mb-4">Department Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Department Name</label>
                        <p class="text-sm">{{ department.name }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Department Code</label>
                        <p class="text-sm font-mono">{{ department.code }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Company</label>
                        <p class="text-sm">{{ department.company.name }}</p>
                    </div>
                    
                    <div>
                        <label class="text-sm font-medium text-gray-500">Created</label>
                        <p class="text-sm">{{ new Date(department.created_at).toLocaleDateString() }}</p>
                    </div>
                </div>

                <div v-if="department.description" class="mt-6">
                    <label class="text-sm font-medium text-gray-500">Description</label>
                    <p class="text-sm mt-1 whitespace-pre-wrap">{{ department.description }}</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 