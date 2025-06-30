<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import Icon from '@/components/Icon.vue';
import Heading from '@/components/Heading.vue';
import { useI18n } from 'vue-i18n';
import { computed, ref, watch } from 'vue';
import type { Employee, Company, BreadcrumbItem } from '@/types';

const { t } = useI18n();

interface Props {
    employees: {
        data: Employee[];
        links: any[];
        meta: any;
    };
    company: Company;
    filters?: {
        search?: string;
        status?: string;
        department?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const departmentFilter = ref(props.filters?.department || '');

const breadcrumbs = computed((): BreadcrumbItem[] => [
    {
        title: t('nav.dashboard'),
        href: '/dashboard',
    },
    {
        title: t('employees.title'),
        href: '/employees',
    },
]);

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'active':
            return 'default';
        case 'inactive':
            return 'secondary';
        case 'terminated':
            return 'destructive';
        default:
            return 'secondary';
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
};

const uniqueDepartments = computed(() => {
    const departments = props.employees.data
        .map(emp => emp.department)
        .filter(Boolean)
        .filter((dept, index, arr) => arr.indexOf(dept) === index);
    return departments.sort();
});

// Debounced search
let searchTimeout: number;
watch([search, statusFilter, departmentFilter], () => {
    clearTimeout(searchTimeout);
    searchTimeout = window.setTimeout(() => {
        router.get('/employees', {
            search: search.value || undefined,
            status: statusFilter.value || undefined,
            department: departmentFilter.value || undefined,
        }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    departmentFilter.value = '';
};
</script>

<template>
    <Head :title="t('employees.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <div class="flex items-center justify-between my-2">
                <Heading :title="t('employees.my_employees')" />
                <Button asChild>
                    <Link :href="route('employees.create')">
                        <Icon name="Plus" class="mr-2 rtl:mr-0 rtl:ml-2 h-4 w-4" />
                        {{ t('employees.create_employee') }}
                    </Link>
                </Button>
            </div>

            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 rtl:left-auto rtl:right-0 pl-3 rtl:pl-0 rtl:pr-3 flex items-center pointer-events-none">
                        <Icon name="Search" class="h-4 w-4 text-gray-400" />
                    </div>
                    <Input
                        v-model="search"
                        :placeholder="t('employees.search_placeholder')"
                        class="w-full pl-10 rtl:pl-3 rtl:pr-10"
                    />
                </div>
                
                <select 
                    v-model="statusFilter"
                    class="flex h-10 w-full sm:w-48 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <option value="">{{ t('employees.filter_by_status') }}</option>
                    <option value="active">{{ t('employees.status_active') }}</option>
                    <option value="inactive">{{ t('employees.status_inactive') }}</option>
                    <option value="terminated">{{ t('employees.status_terminated') }}</option>
                </select>

                <select 
                    v-model="departmentFilter"
                    class="flex h-10 w-full sm:w-48 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <option value="">{{ t('employees.filter_by_department') }}</option>
                    <option v-for="dept in uniqueDepartments" :key="dept" :value="dept">
                        {{ dept }}
                    </option>
                </select>

                <Button variant="ghost" @click="clearFilters" v-if="search || statusFilter || departmentFilter">
                    <Icon name="X" class="mr-2 rtl:mr-0 rtl:ml-2 h-4 w-4" />
                    {{ t('common.clear') }}
                </Button>
            </div>

            <div v-if="employees.data.length === 0" class="text-center py-12">
                <Icon name="Users" class="mx-auto h-12 w-12 text-gray-400 mb-4" />
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                    {{ t('employees.no_employees') }}
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    {{ search || statusFilter || departmentFilter ? t('employees.no_employees_found') : t('employees.create_first_employee') }}
                </p>
                <Button asChild v-if="!search && !statusFilter && !departmentFilter">
                    <Link :href="route('employees.create')">
                        <Icon name="Plus" class="mr-2 rtl:mr-0 rtl:ml-2 h-4 w-4" />
                        {{ t('employees.create_employee') }}
                    </Link>
                </Button>
            </div>

            <!-- Employees Table -->
            <div v-else class="bg-white dark:bg-gray-800 rounded-lg border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr class="text-left rtl:text-right">
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('employees.employee') }}
                                </th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('employees.job_information') }}
                                </th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('employees.contact') }}
                                </th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('employees.status') }}
                                </th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('employees.assets') }}
                                </th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('common.actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="employee in employees.data" :key="employee.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    {{ (employee.first_name?.[0] || '') + (employee.last_name?.[0] || '') }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4 rtl:ml-0 rtl:mr-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ employee.first_name }} {{ employee.last_name }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400 font-mono">
                                                {{ employee.employee_id }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ employee.job_title || '-' }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ employee.department || '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ employee.email }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ employee.phone || '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <Badge :variant="getStatusVariant(employee.employment_status)">
                                        {{ t(`employees.status_${employee.employment_status}`) }}
                                    </Badge>
                                    <div v-if="employee.hire_date" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ t('employees.since') }} {{ formatDate(employee.hire_date) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <div class="flex items-center">
                                        <div class="flex items-center mr-3 rtl:mr-0 rtl:ml-3">
                                            <Icon name="Package" class="h-4 w-4 mr-1 rtl:mr-0 rtl:ml-1" />
                                            <span>{{ employee.assets_count || 0 }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <Icon name="Ticket" class="h-4 w-4 mr-1 rtl:mr-0 rtl:ml-1" />
                                            <span>{{ employee.reported_tickets_count || 0 }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right rtl:text-left text-sm font-medium">
                                    <div class="flex justify-end rtl:justify-start">
                                        <Button variant="ghost" size="sm" asChild class="mr-2 rtl:mr-0 rtl:ml-2">
                                            <Link :href="route('employees.show', employee.id)">
                                                <Icon name="Eye" class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="sm" asChild>
                                            <Link :href="route('employees.edit', employee.id)">
                                                <Icon name="Pencil" class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination would go here if needed -->
        </div>
    </AppLayout>
</template> 