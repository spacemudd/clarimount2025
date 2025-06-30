<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Company {
    id: number
    name_en: string
    name_ar: string
    company_email: string
}

interface Props {
    companies: Company[]
}

const props = defineProps<Props>()

const form = useForm({
    name: '',
    code: '',
    company_id: props.companies.length === 1 ? props.companies[0].id : '',
    description: '',
})

const submit = () => {
    form.post('/departments')
}
</script>

<template>
    <AppLayout>
        <div class="max-w-2xl mx-auto p-6">
            <h1 class="text-2xl font-bold mb-6">Create Department</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div v-if="companies.length === 0" class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                    <p class="text-yellow-800">You need to create a company first before adding departments.</p>
                    <a href="/companies/create" class="text-yellow-900 underline font-medium">Create Company</a>
                </div>

                <div v-if="companies.length > 1">
                    <label class="block text-sm font-medium mb-1">Company *</label>
                    <select 
                        v-model="form.company_id" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    >
                        <option value="">Select a company</option>
                        <option v-for="company in companies" :key="company.id" :value="company.id">
                            {{ company.name_en }}
                        </option>
                    </select>
                    <div v-if="form.errors.company_id" class="text-red-500 text-sm mt-1">{{ form.errors.company_id }}</div>
                </div>

                <div v-else-if="companies.length === 1" class="bg-blue-50 border border-blue-200 rounded-md p-3">
                    <p class="text-blue-800 text-sm">
                        Department will be created for: <strong>{{ companies[0].name_en }}</strong>
                    </p>
                    <div v-if="form.errors.company_id" class="text-red-500 text-sm mt-1">{{ form.errors.company_id }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Department Name *</label>
                    <input 
                        v-model="form.name" 
                        type="text" 
                        required
                        placeholder="e.g., Information Technology"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md"
                        :disabled="companies.length === 0"
                    >
                    <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Department Code</label>
                    <input 
                        v-model="form.code" 
                        type="text" 
                        placeholder="Leave empty for auto-generated code (e.g., DEPT001)"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md"
                        :disabled="companies.length === 0"
                    >
                    <div class="text-xs text-gray-500 mt-1">
                        If left empty, a code like DEPT001, DEPT002, etc. will be automatically generated
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
                        :disabled="companies.length === 0"
                    ></textarea>
                    <div v-if="form.errors.description" class="text-red-500 text-sm mt-1">{{ form.errors.description }}</div>
                </div>

                <div class="flex gap-4 pt-4">
                    <a href="/departments" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        :disabled="form.processing || companies.length === 0 || !form.company_id || !form.name"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
                    >
                        <span v-if="form.processing">Creating...</span>
                        <span v-else>Create Department</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template> 