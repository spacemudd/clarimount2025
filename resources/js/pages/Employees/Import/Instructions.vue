<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Select } from '@/components/ui/select';
import Icon from '@/components/Icon.vue';
import Heading from '@/components/Heading.vue';
import { useI18n } from 'vue-i18n';
import { computed, ref } from 'vue';
import type { Company, BreadcrumbItem } from '@/types';

const { t } = useI18n();

interface Props {
    companies: Company[];
    currentCompany?: Company;
    requiredFields: Record<string, string>;
    optionalFields: Record<string, string>;
}

const props = defineProps<Props>();

// State for selected company
const selectedCompanyId = ref<string>(props.currentCompany?.id?.toString() || props.companies[0]?.id?.toString() || '');

const selectedCompany = computed(() => {
    return props.companies.find(c => c.id.toString() === selectedCompanyId.value);
});

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
        title: t('employees.import_csv'),
        href: '/employees/import',
    },
]);

const downloadSampleCsvUrl = computed(() => {
    return selectedCompanyId.value 
        ? route('employees.import.sample-csv', { company_id: selectedCompanyId.value })
        : route('employees.import.sample-csv');
});

const downloadExistingCsvUrl = computed(() => {
    return selectedCompanyId.value 
        ? route('employees.export-csv', { company_id: selectedCompanyId.value })
        : route('employees.export-csv');
});

const uploadUrl = computed(() => {
    return selectedCompanyId.value 
        ? route('employees.import.upload', { company_id: selectedCompanyId.value })
        : route('employees.import.upload');
});
</script>

<template>
    <Head :title="t('employees.import_csv')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div>
                <Heading :title="t('employees.import_csv')" />
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ t('employees.import_instructions_subtitle') }}
                </p>
            </div>

            <!-- Company Selection -->
            <Card v-if="companies.length > 1">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Icon name="Building" class="h-5 w-5 text-blue-600" />
                        {{ t('employees.select_company') }}
                    </CardTitle>
                    <CardDescription>
                        {{ t('employees.select_company_description') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="max-w-sm">
                        <select 
                            v-model="selectedCompanyId"
                            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option value="">{{ t('employees.select_company_placeholder') }}</option>
                            <option 
                                v-for="company in companies" 
                                :key="company.id" 
                                :value="company.id.toString()"
                            >
                                {{ company.name_en }}
                                <span v-if="company.name_ar">
                                    ({{ company.name_ar }})
                                </span>
                            </option>
                        </select>
                    </div>
                    <div v-if="selectedCompany" class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <div class="flex items-center gap-2">
                            <Icon name="Building" class="h-4 w-4 text-blue-600" />
                            <div class="text-sm">
                                <span class="font-medium">{{ t('employees.selected_company') }}:</span>
                                {{ selectedCompany.name_en }}
                                <span v-if="selectedCompany.name_ar" class="text-gray-600 ml-1">
                                    ({{ selectedCompany.name_ar }})
                                </span>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Single Company Display -->
            <Card v-else-if="companies.length === 1">
                <CardContent class="pt-6">
                    <div class="flex items-center gap-2 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <Icon name="Building" class="h-4 w-4 text-blue-600" />
                        <div class="text-sm">
                            <span class="font-medium">{{ t('employees.importing_to_company') }}:</span>
                            {{ companies[0].name_en }}
                            <span v-if="companies[0].name_ar" class="text-gray-600 ml-1">
                                ({{ companies[0].name_ar }})
                            </span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Instructions Card -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Icon name="Info" class="h-5 w-5 text-blue-600" />
                        {{ t('employees.import_instructions') }}
                    </CardTitle>
                    <CardDescription>
                        {{ t('employees.import_instructions_description') }}
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <h4 class="font-medium text-sm">{{ t('employees.step_1') }}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ t('employees.step_1_description') }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <h4 class="font-medium text-sm">{{ t('employees.step_2') }}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ t('employees.step_2_description') }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <h4 class="font-medium text-sm">{{ t('employees.step_3') }}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ t('employees.step_3_description') }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <h4 class="font-medium text-sm">{{ t('employees.step_4') }}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ t('employees.step_4_description') }}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Required Fields Card -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Icon name="AlertCircle" class="h-5 w-5 text-red-600" />
                        {{ t('employees.required_fields') }}
                    </CardTitle>
                    <CardDescription>
                        {{ t('employees.required_fields_description') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div 
                            v-for="(description, field) in requiredFields" 
                            :key="field"
                            class="flex items-center gap-2 p-3 border rounded-lg bg-red-50 dark:bg-red-900/20"
                        >
                            <Badge variant="destructive" class="text-xs">{{ t('common.required') }}</Badge>
                            <div class="flex-1">
                                <div class="font-medium text-sm">{{ field }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">{{ description }}</div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Optional Fields Card -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Icon name="Settings" class="h-5 w-5 text-gray-600" />
                        {{ t('employees.optional_fields') }}
                    </CardTitle>
                    <CardDescription>
                        {{ t('employees.optional_fields_description') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div 
                            v-for="(description, field) in optionalFields" 
                            :key="field"
                            class="flex items-center gap-2 p-3 border rounded-lg"
                        >
                            <Badge variant="secondary" class="text-xs">{{ t('common.optional') }}</Badge>
                            <div class="flex-1">
                                <div class="font-medium text-sm">{{ field }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">{{ description }}</div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Important Notes Card -->
            <Card class="border-orange-200 bg-orange-50 dark:bg-orange-900/20">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-orange-800 dark:text-orange-200">
                        <Icon name="AlertTriangle" class="h-5 w-5" />
                        {{ t('employees.important_notes') }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-2">
                    <div class="flex items-start gap-2">
                        <Icon name="Clock" class="h-4 w-4 mt-0.5 text-orange-600" />
                        <p class="text-sm text-orange-800 dark:text-orange-200">
                            {{ t('employees.date_format_note') }}
                        </p>
                    </div>
                    <div class="flex items-start gap-2">
                        <Icon name="Users" class="h-4 w-4 mt-0.5 text-orange-600" />
                        <p class="text-sm text-orange-800 dark:text-orange-200">
                            {{ t('employees.update_note') }}
                        </p>
                    </div>
                    <div class="flex items-start gap-2">
                        <Icon name="Mail" class="h-4 w-4 mt-0.5 text-orange-600" />
                        <p class="text-sm text-orange-800 dark:text-orange-200">
                            {{ t('employees.email_unique_note') }}
                        </p>
                    </div>
                    <div class="flex items-start gap-2">
                        <Icon name="Database" class="h-4 w-4 mt-0.5 text-orange-600" />
                        <p class="text-sm text-orange-800 dark:text-orange-200">
                            {{ t('employees.validation_note') }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Download Section -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Icon name="Download" class="h-5 w-5 text-green-600" />
                        {{ t('employees.download_templates') }}
                    </CardTitle>
                    <CardDescription>
                        {{ t('employees.download_templates_description') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <Button variant="outline" asChild>
                            <a :href="downloadSampleCsvUrl" class="flex items-center gap-2" download>
                                <Icon name="FileSpreadsheet" class="h-4 w-4" />
                                {{ t('employees.download_sample_csv') }}
                            </a>
                        </Button>
                        <Button variant="outline" asChild>
                            <a :href="downloadExistingCsvUrl" class="flex items-center gap-2" download>
                                <Icon name="Users" class="h-4 w-4" />
                                {{ t('employees.download_existing_csv') }}
                            </a>
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Action Buttons -->
            <div class="flex justify-between">
                <Button variant="ghost" asChild>
                    <Link :href="route('employees.index')">
                        <Icon name="ArrowLeft" class="mr-2 rtl:mr-0 rtl:ml-2 h-4 w-4" />
                        {{ t('common.back') }}
                    </Link>
                </Button>
                <Button asChild>
                    <Link :href="uploadUrl">
                        {{ t('employees.proceed_to_upload') }}
                        <Icon name="ArrowRight" class="ml-2 rtl:ml-0 rtl:mr-2 h-4 w-4" />
                    </Link>
                </Button>
            </div>
        </div>
    </AppLayout>
</template> 