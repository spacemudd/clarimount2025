<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Department {
    id: string
    name: string
    code: string
    description?: string
    company_id: string
    created_at: string
    updated_at: string
}

interface Props {
    department: Department
}

const props = defineProps<Props>()

const form = useForm({
    name: props.department.name,
    code: props.department.code,
    description: props.department.description || '',
})

const submit = () => {
    form.put(`/departments/${props.department.id}`)
}
</script>

<template>
    <AppLayout>
        <div class="max-w-2xl mx-auto p-6">
            <h1 class="text-2xl font-bold mb-6">Edit Department</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Department Name *</label>
                    <input 
                        v-model="form.name" 
                        type="text" 
                        required
                        placeholder="e.g., Information Technology"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                    <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Department Code *</label>
                    <input 
                        v-model="form.code" 
                        type="text" 
                        required
                        placeholder="e.g., DEPT001 or IT"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                    <div class="text-xs text-gray-500 mt-1">
                        Department code must be unique within your company
                    </div>
                    <div v-if="form.errors.code" class="text-red-500 text-sm mt-1">{{ form.errors.code }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Description</label>
                    <textarea 
                        v-model="form.description" 
                        rows="3"
                        placeholder="Optional description of the department"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    ></textarea>
                    <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</div>
                </div>

                <div class="flex gap-4 pt-4">
                    <a :href="`/departments/${department.id}`" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                    >
                        <span v-if="form.processing">Updating...</span>
                        <span v-else>Update Department</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template> 