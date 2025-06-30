<template>
    <AppLayout>
        <div class="container max-w-4xl mx-auto py-8">
            <div class="space-y-6">
                <div class="space-y-2">
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                    <Heading :title="t('employees.edit_employee')" />
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('employees.employee_details') }}</CardTitle>
                        <CardDescription>
                            {{ t('employees.employee_details_description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Basic Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium">{{ t('employees.basic_information') }}</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <Label for="employee_id">{{ t('employees.employee_id') }}</Label>
                                        <Input
                                            id="employee_id"
                                            v-model="form.employee_id"
                                            type="text"
                                            :placeholder="t('employees.employee_id_placeholder')"
                                            :disabled="form.processing"
                                        />
                                        <InputError :message="form.errors.employee_id" />
                                    </div>

                                    <div>
                                        <Label for="first_name">{{ t('employees.first_name') }}</Label>
                                        <Input
                                            id="first_name"
                                            v-model="form.first_name"
                                            type="text"
                                            :placeholder="t('employees.first_name_placeholder')"
                                            required
                                            :disabled="form.processing"
                                        />
                                        <InputError :message="form.errors.first_name" />
                                    </div>

                                    <div>
                                        <Label for="last_name">{{ t('employees.last_name') }}</Label>
                                        <Input
                                            id="last_name"
                                            v-model="form.last_name"
                                            type="text"
                                            :placeholder="t('employees.last_name_placeholder')"
                                            required
                                            :disabled="form.processing"
                                        />
                                        <InputError :message="form.errors.last_name" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <Label for="email">{{ t('employees.email') }}</Label>
                                        <Input
                                            id="email"
                                            v-model="form.email"
                                            type="email"
                                            :placeholder="t('employees.email_placeholder')"
                                            required
                                            :disabled="form.processing"
                                        />
                                        <InputError :message="form.errors.email" />
                                    </div>

                                    <div>
                                        <Label for="location_id">{{ t('employees.location') }}</Label>
                                        <select 
                                            id="location_id"
                                            v-model="form.location_id"
                                            :disabled="form.processing"
                                            required
                                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            <option value="">{{ t('employees.select_location') }}</option>
                                            <option v-for="location in locations" :key="location.id" :value="location.id">
                                                {{ location.display_name || `${location.code}: ${location.name}` }}
                                            </option>
                                        </select>
                                        <InputError :message="form.errors.location_id" />
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium">{{ t('employees.contact_information') }}</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <Label for="phone">{{ t('employees.phone') }}</Label>
                                        <Input
                                            id="phone"
                                            v-model="form.phone"
                                            type="text"
                                            :placeholder="t('employees.phone_placeholder')"
                                            :disabled="form.processing"
                                        />
                                        <InputError :message="form.errors.phone" />
                                    </div>

                                    <div>
                                        <Label for="mobile">{{ t('employees.mobile') }}</Label>
                                        <Input
                                            id="mobile"
                                            v-model="form.mobile"
                                            type="text"
                                            :placeholder="t('employees.mobile_placeholder')"
                                            :disabled="form.processing"
                                        />
                                        <InputError :message="form.errors.mobile" />
                                    </div>
                                </div>
                            </div>

                            <!-- Job Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium">{{ t('employees.job_information') }}</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <Label for="department">{{ t('employees.department') }}</Label>
                                        <div class="relative">
                                            <div class="flex">
                                                <input 
                                                    v-model="departmentSearchQuery"
                                                    type="text" 
                                                    :placeholder="t('employees.department_placeholder')"
                                                    :disabled="form.processing"
                                                    class="flex h-10 w-full rounded-l-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                                    @focus="showDepartmentDropdown = departmentSearchQuery.length >= 2"
                                                    @blur="handleDepartmentBlur"
                                                >
                                                <button
                                                    v-if="selectedDepartment"
                                                    type="button"
                                                    @click="clearDepartment"
                                                    :disabled="form.processing"
                                                    class="px-3 py-2 bg-gray-100 border border-l-0 border-gray-300 rounded-r-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
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
                        
                                            <div v-if="selectedDepartment" class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded-md">
                                                <div class="text-sm font-medium text-blue-800">{{ t('common.selected') }}: {{ selectedDepartment.name }}</div>
                                                <div class="text-xs text-blue-600">{{ selectedDepartment.company_name }}</div>
                                            </div>
                                        </div>
                                        <InputError :message="form.errors.department_id" />
                                    </div>

                                    <div>
                                        <Label for="job_title">{{ t('employees.job_title') }}</Label>
                                        <Input
                                            id="job_title"
                                            v-model="form.job_title"
                                            type="text"
                                            :placeholder="t('employees.job_title_placeholder')"
                                            :disabled="form.processing"
                                        />
                                        <InputError :message="form.errors.job_title" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <Label for="manager">{{ t('employees.manager') }}</Label>
                                        <Input
                                            id="manager"
                                            v-model="form.manager"
                                            type="text"
                                            :placeholder="t('employees.manager_placeholder')"
                                            :disabled="form.processing"
                                        />
                                        <InputError :message="form.errors.manager" />
                                    </div>

                                    <div>
                                        <Label for="employment_status">{{ t('employees.employment_status') }}</Label>
                                        <select 
                                            id="employment_status"
                                            v-model="form.employment_status"
                                            :disabled="form.processing"
                                            required
                                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            <option value="active">{{ t('employees.status_active') }}</option>
                                            <option value="inactive">{{ t('employees.status_inactive') }}</option>
                                            <option value="terminated">{{ t('employees.status_terminated') }}</option>
                                        </select>
                                        <InputError :message="form.errors.employment_status" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <Label for="hire_date">{{ t('employees.hire_date') }}</Label>
                                        <Input
                                            id="hire_date"
                                            v-model="form.hire_date"
                                            type="date"
                                            :disabled="form.processing"
                                        />
                                        <InputError :message="form.errors.hire_date" />
                                    </div>

                                    <div>
                                        <Label for="termination_date">{{ t('employees.termination_date') }}</Label>
                                        <Input
                                            id="termination_date"
                                            v-model="form.termination_date"
                                            type="date"
                                            :disabled="form.processing"
                                        />
                                        <InputError :message="form.errors.termination_date" />
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium">{{ t('employees.additional_information') }}</h3>
                                
                                <div>
                                    <Label for="notes">{{ t('employees.notes') }}</Label>
                                    <textarea
                                        id="notes"
                                        v-model="form.notes"
                                        :placeholder="t('employees.notes_placeholder')"
                                        :disabled="form.processing"
                                        rows="4"
                                        class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    ></textarea>
                                    <InputError :message="form.errors.notes" />
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-6 border-t">
                                <Button variant="outline" asChild>
                                    <Link :href="route('employees.show', employee.id)">
                                        {{ t('common.cancel') }}
                                    </Link>
                                </Button>
                                <Button type="submit" :disabled="form.processing">
                                    <Icon v-if="form.processing" name="LoaderCircle" class="mr-2 h-4 w-4 animate-spin" />
                                    {{ form.processing ? t('common.saving') : t('common.save') }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed, ref, watch, onMounted } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/layouts/AppLayout.vue';
import { 
    Card, 
    CardContent, 
    CardDescription, 
    CardHeader, 
    CardTitle 
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import Heading from '@/components/Heading.vue';
import Icon from '@/components/Icon.vue';
import type { Employee, Location } from '@/types';
import type { BreadcrumbItem } from '@/types';

interface Department {
    id: number
    name: string
    company_name: string
}

interface Props {
    employee: Employee;
    locations: Location[];
}

const props = defineProps<Props>();
const { t } = useI18n();

const form = useForm({
    employee_id: props.employee.employee_id,
    first_name: props.employee.first_name,
    last_name: props.employee.last_name,
    email: props.employee.email,
    phone: props.employee.phone || '',
    mobile: props.employee.mobile || '',
    department: props.employee.department || '',
    department_id: (props.employee as any).department_id || null,
    job_title: props.employee.job_title || '',
    manager: props.employee.manager || '',
    hire_date: props.employee.hire_date || '',
    termination_date: props.employee.termination_date || '',
    employment_status: props.employee.employment_status || 'active',
    location_id: (props.employee as any).location_id,
    notes: props.employee.notes || '',
});

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
    departmentSearchQuery.value = department.name || ''
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

// Initialize department search if employee has a department_id
onMounted(async () => {
    if (form.department_id) {
        try {
            const response = await fetch(`/api/departments/search?q=`)
            if (response.ok) {
                const departments = await response.json()
                const currentDepartment = departments.find((dept: Department) => dept.id === form.department_id)
                if (currentDepartment) {
                    selectedDepartment.value = currentDepartment
                    departmentSearchQuery.value = currentDepartment.name
                }
            }
        } catch (error) {
            console.error('Error loading current department:', error)
        }
    }
})

const breadcrumbs = computed((): BreadcrumbItem[] => [
    {
        title: t('nav.dashboard'),
        href: '/dashboard',
    },
    {
        title: t('employees.title'),
        href: '/employees',
    },
    {
        title: props.employee.full_name,
        href: `/employees/${props.employee.id}`,
    },
    {
        title: t('employees.edit_employee'),
        href: `/employees/${props.employee.id}/edit`,
    },
]);

const submit = () => {
    form.put(route('employees.update', props.employee.id));
};
</script> 