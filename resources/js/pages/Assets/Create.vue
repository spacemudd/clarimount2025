<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import Icon from '@/components/Icon.vue';
import Heading from '@/components/Heading.vue';
import { useI18n } from 'vue-i18n';
import { computed, ref } from 'vue';
import type { AssetCategory, Location, Company, BreadcrumbItem } from '@/types';
import BarcodeScanner from '@/components/BarcodeScanner.vue';

const { t } = useI18n();

interface AssetTemplate {
    id: number;
    name: string;
    manufacturer?: string;
    model_name?: string;
    model_number?: string;
    asset_category?: {
        id: number;
        name: string;
    };
    company?: {
        id: number;
        name_en: string;
    };
    is_global: boolean;
    display_name: string;
    category_name?: string;
}

interface Props {
    companies: Company[];
    currentCompany?: Company;
    categories: AssetCategory[];
    locations: Location[];
}

const props = defineProps<Props>();
const page = usePage();

// Function to get CSRF token
const getCsrfToken = (): string => {
    const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    return metaToken || '';
};

const currentStep = ref(1);
const totalSteps = 3;
const isLocationDialogOpen = ref(false);
const showBarcodeScanner = ref(false);

// Location search state
const searchQuery = ref('');
const searchResults = ref<Array<{
    id: number;
    name: string;
    code: string;
    building?: string;
    office_number?: string;
    address?: string;
    city?: string;
    company_name: string;
    display_name: string;
    full_address?: string;
}>>([]);
const isSearching = ref(false);
const showSearchResults = ref(false);
const selectedLocation = ref<typeof searchResults.value[0] | null>(null);

// Template search state
const templateSearchQuery = ref('');
const templateSearchResults = ref<AssetTemplate[]>([]);
const isTemplateSearching = ref(false);
const showTemplateSearchResults = ref(false);
const selectedTemplate = ref<AssetTemplate | null>(null);

// Company search state
const companySearchQuery = ref('');
const companySearchResults = ref<Array<{
    id: number;
    name_en: string;
    name_ar: string;
    company_email: string;
    display_name: string;
}>>([]);
const isCompanySearching = ref(false);
const showCompanySearchResults = ref(false);
const selectedCompany = ref<typeof companySearchResults.value[0] | null>(null);

// Department search state
const departmentSearchQuery = ref('');
const departmentSearchResults = ref<Array<{
    id: string;
    name: string;
    code: string;
    description?: string;
    company_name: string;
    display_name: string;
}>>([]);
const isDepartmentSearching = ref(false);
const showDepartmentSearchResults = ref(false);
const selectedDepartment = ref<typeof departmentSearchResults.value[0] | null>(null);

// Employee search state
const employeeSearchQuery = ref('');
const employeeSearchResults = ref<Array<{
    id: number;
    employee_id?: string;
    first_name: string;
    last_name: string;
    email: string;
    job_title?: string;
    department?: string;
    company_name: string;
    display_name: string;
}>>([]);
const isEmployeeSearching = ref(false);
const showEmployeeSearchResults = ref(false);
const selectedEmployee = ref<typeof employeeSearchResults.value[0] | null>(null);

// File upload state
const selectedFile = ref<File | null>(null);
const filePreview = ref<string | null>(null);
const fileError = ref<string | null>(null);

const form = useForm({
    location_id: '',
    asset_template_id: '',
    company_id: props.currentCompany?.id || '',
    department_id: '',
    assigned_to: '',
    serial_number: '',
    condition: 'good',
    image: null as File | null,
});

const locationForm = useForm({
    company_id: props.currentCompany?.id || (props.companies.length === 1 ? props.companies[0].id : ''),
    name: '',
    building: '',
    office_number: '',
    address: '',
    city: '',
    state: '',
    postal_code: '',
    country: '',
});

const breadcrumbs = computed((): BreadcrumbItem[] => [
    {
        title: t('nav.dashboard'),
        href: '/dashboard',
    },
    {
        title: t('nav.assets'),
        href: '/assets',
    },
    {
        title: t('assets.create_asset'),
        href: '/assets/create',
    },
]);

const canGoNext = computed(() => {
    switch (currentStep.value) {
        case 1:
            return selectedLocation.value !== null;
        case 2:
            return selectedTemplate.value !== null;
        case 3:
            return true; // File upload is optional
        default:
            return false;
    }
});

// Debounced search function for locations
let searchTimeout: number;
const searchLocations = async () => {
    if (searchQuery.value.length < 2) {
        searchResults.value = [];
        showSearchResults.value = false;
        return;
    }

    isSearching.value = true;
    
    try {
        const response = await fetch(`/api/locations/search?q=${encodeURIComponent(searchQuery.value)}`);
        const locations = await response.json();
        searchResults.value = locations;
        showSearchResults.value = true;
    } catch (error) {
        console.error('Location search failed:', error);
        searchResults.value = [];
    } finally {
        isSearching.value = false;
    }
};

const handleSearchInput = () => {
    clearTimeout(searchTimeout);
    searchTimeout = window.setTimeout(searchLocations, 300);
};

const selectLocation = (location: typeof searchResults.value[0]) => {
    selectedLocation.value = location;
    form.location_id = location.id.toString();
    searchQuery.value = location.display_name;
    showSearchResults.value = false;
};

const clearLocationSelection = () => {
    selectedLocation.value = null;
    form.location_id = '';
    searchQuery.value = '';
    searchResults.value = [];
    showSearchResults.value = false;
};

const hideSearchResults = () => {
    setTimeout(() => {
        showSearchResults.value = false;
    }, 200);
};

// Template search functions
let templateSearchTimeout: number;
const searchTemplates = async () => {
    if (templateSearchQuery.value.length < 2) {
        templateSearchResults.value = [];
        showTemplateSearchResults.value = false;
        return;
    }

    isTemplateSearching.value = true;
    
    try {
        const response = await fetch(`/api/asset-templates/search?q=${encodeURIComponent(templateSearchQuery.value)}`);
        const templates = await response.json();
        templateSearchResults.value = templates;
        showTemplateSearchResults.value = true;
    } catch (error) {
        console.error('Template search failed:', error);
        templateSearchResults.value = [];
    } finally {
        isTemplateSearching.value = false;
    }
};

const handleTemplateSearchInput = () => {
    clearTimeout(templateSearchTimeout);
    templateSearchTimeout = window.setTimeout(searchTemplates, 300);
};

const selectTemplate = (template: AssetTemplate) => {
    selectedTemplate.value = template;
    form.asset_template_id = template.id.toString();
    templateSearchQuery.value = template.display_name;
    showTemplateSearchResults.value = false;
};

const clearTemplateSelection = () => {
    selectedTemplate.value = null;
    form.asset_template_id = '';
    templateSearchQuery.value = '';
    templateSearchResults.value = [];
    showTemplateSearchResults.value = false;
};

const hideTemplateSearchResults = () => {
    setTimeout(() => {
        showTemplateSearchResults.value = false;
    }, 200);
};

// Company search functions
let companySearchTimeout: number;
const searchCompanies = async () => {
    if (companySearchQuery.value.length < 2) {
        companySearchResults.value = [];
        showCompanySearchResults.value = false;
        return;
    }

    isCompanySearching.value = true;
    
    try {
        const response = await fetch(`/api/companies/search?q=${encodeURIComponent(companySearchQuery.value)}`);
        const companies = await response.json();
        companySearchResults.value = companies;
        showCompanySearchResults.value = true;
    } catch (error) {
        console.error('Company search failed:', error);
        companySearchResults.value = [];
    } finally {
        isCompanySearching.value = false;
    }
};

const handleCompanySearchInput = () => {
    clearTimeout(companySearchTimeout);
    companySearchTimeout = window.setTimeout(searchCompanies, 300);
};

const selectCompany = (company: typeof companySearchResults.value[0]) => {
    selectedCompany.value = company;
    form.company_id = company.id.toString();
    companySearchQuery.value = company.display_name;
    showCompanySearchResults.value = false;
    
    // Clear dependent selections when company changes
    clearDepartmentSelection();
    clearEmployeeSelection();
};

const clearCompanySelection = () => {
    selectedCompany.value = null;
    form.company_id = props.currentCompany?.id || '';
    companySearchQuery.value = '';
    companySearchResults.value = [];
    showCompanySearchResults.value = false;
    
    // Clear dependent selections
    clearDepartmentSelection();
    clearEmployeeSelection();
};

const hideCompanySearchResults = () => {
    setTimeout(() => {
        showCompanySearchResults.value = false;
    }, 200);
};

// Department search functions
let departmentSearchTimeout: number;
const searchDepartments = async () => {
    if (departmentSearchQuery.value.length < 2) {
        departmentSearchResults.value = [];
        showDepartmentSearchResults.value = false;
        return;
    }

    isDepartmentSearching.value = true;
    
    try {
        const companyId = selectedCompany.value?.id || props.currentCompany?.id;
        const response = await fetch(`/api/departments/search?q=${encodeURIComponent(departmentSearchQuery.value)}&company_id=${companyId}`);
        const departments = await response.json();
        departmentSearchResults.value = departments;
        showDepartmentSearchResults.value = true;
    } catch (error) {
        console.error('Department search failed:', error);
        departmentSearchResults.value = [];
    } finally {
        isDepartmentSearching.value = false;
    }
};

const handleDepartmentSearchInput = () => {
    clearTimeout(departmentSearchTimeout);
    departmentSearchTimeout = window.setTimeout(searchDepartments, 300);
};

const selectDepartment = (department: typeof departmentSearchResults.value[0]) => {
    selectedDepartment.value = department;
    form.department_id = department.id;
    departmentSearchQuery.value = department.display_name;
    showDepartmentSearchResults.value = false;
    
    // Clear employee selection when department changes
    clearEmployeeSelection();
};

const clearDepartmentSelection = () => {
    selectedDepartment.value = null;
    form.department_id = '';
    departmentSearchQuery.value = '';
    departmentSearchResults.value = [];
    showDepartmentSearchResults.value = false;
    
    // Clear employee selection
    clearEmployeeSelection();
};

const hideDepartmentSearchResults = () => {
    setTimeout(() => {
        showDepartmentSearchResults.value = false;
    }, 200);
};

// Employee search functions
let employeeSearchTimeout: number;
const searchEmployees = async () => {
    if (employeeSearchQuery.value.length < 2) {
        employeeSearchResults.value = [];
        showEmployeeSearchResults.value = false;
        return;
    }

    isEmployeeSearching.value = true;
    
    try {
        const companyId = selectedCompany.value?.id || props.currentCompany?.id;
        const url = `/api/employees/search?q=${encodeURIComponent(employeeSearchQuery.value)}&company_id=${companyId}`;
        
        const response = await fetch(url);
        const employees = await response.json();
        employeeSearchResults.value = employees;
        showEmployeeSearchResults.value = true;
    } catch (error) {
        console.error('Employee search failed:', error);
        employeeSearchResults.value = [];
    } finally {
        isEmployeeSearching.value = false;
    }
};

const handleEmployeeSearchInput = () => {
    clearTimeout(employeeSearchTimeout);
    employeeSearchTimeout = window.setTimeout(searchEmployees, 300);
};

const selectEmployee = (employee: typeof employeeSearchResults.value[0]) => {
    selectedEmployee.value = employee;
    form.assigned_to = employee.id.toString();
    employeeSearchQuery.value = employee.display_name;
    showEmployeeSearchResults.value = false;
};

const clearEmployeeSelection = () => {
    selectedEmployee.value = null;
    form.assigned_to = '';
    employeeSearchQuery.value = '';
    employeeSearchResults.value = [];
    showEmployeeSearchResults.value = false;
};

const hideEmployeeSearchResults = () => {
    setTimeout(() => {
        showEmployeeSearchResults.value = false;
    }, 200);
};

// File upload functions
const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    
    if (!file) {
        selectedFile.value = null;
        filePreview.value = null;
        form.image = null;
        return;
    }

    // Validate file type
    if (!file.type.startsWith('image/')) {
        fileError.value = t('assets.invalid_file_type');
        return;
    }

    // Validate file size (5MB limit)
    if (file.size > 5 * 1024 * 1024) {
        fileError.value = t('assets.file_too_large');
        return;
    }

    fileError.value = null;
    selectedFile.value = file;
    form.image = file;

    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
        filePreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);
};

const removeFile = () => {
    selectedFile.value = null;
    filePreview.value = null;
    fileError.value = null;
    form.image = null;
    
    // Reset file input
    const fileInput = document.getElementById('asset_image') as HTMLInputElement;
    if (fileInput) {
        fileInput.value = '';
    }
};

const nextStep = () => {
    if (canGoNext.value && currentStep.value < totalSteps) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const createLocation = async () => {
    locationForm.processing = true;
    locationForm.clearErrors();

    try {
        const response = await fetch('/locations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify({
                company_id: locationForm.company_id,
                name: locationForm.name,
                building: locationForm.building,
                office_number: locationForm.office_number,
                address: locationForm.address,
                city: locationForm.city,
                state: locationForm.state,
                postal_code: locationForm.postal_code,
                country: locationForm.country,
                _from_modal: true, // Flag to indicate this is from modal
            }),
        });

        const data = await response.json();

        if (response.ok && data.success) {
            // Successfully created location
            isLocationDialogOpen.value = false;
            locationForm.reset();
            
            // Create the location object for selection
            const newLocation = {
                id: data.location.id,
                name: data.location.name,
                code: data.location.code,
                building: data.location.building,
                office_number: data.location.office_number,
                address: data.location.address,
                city: data.location.city,
                company_name: data.location.company.name_en,
                display_name: `${data.location.code}: ${data.location.name}` + 
                    (data.location.building ? ` - Building ${data.location.building}` : '') +
                    (data.location.office_number ? ` - Office ${data.location.office_number}` : ''),
                full_address: [data.location.address, data.location.city].filter(Boolean).join(', ')
            };
            
            // Auto-select the newly created location
            selectLocation(newLocation);
        } else {
            // Handle validation errors
            if (data.errors) {
                const validFields = ['company_id', 'name', 'building', 'office_number', 'address', 'city', 'state', 'postal_code', 'country'] as const;
                Object.keys(data.errors).forEach(key => {
                    if (validFields.includes(key as typeof validFields[number])) {
                        locationForm.setError(key as typeof validFields[number], data.errors[key][0]);
                    }
                });
            } else {
                console.error('Failed to create location:', data);
            }
        }
    } catch (error) {
        console.error('Error creating location:', error);
        locationForm.setError('name', 'An error occurred while creating the location.');
    } finally {
        locationForm.processing = false;
    }
};

const submit = () => {
    form.post('/assets', {
        onSuccess: () => {
            router.visit('/assets');
        },
        onError: () => {
            // Form errors will be handled by the form
        }
    });
};

const getStepClass = (step: number) => {
    if (step < currentStep.value) {
        return 'bg-primary text-primary-foreground';
    } else if (step === currentStep.value) {
        return 'bg-primary text-primary-foreground ring-2 ring-primary ring-offset-2';
    } else {
        return 'bg-muted text-muted-foreground';
    }
};

const getStepConnectorClass = (step: number) => {
    return step < currentStep.value ? 'bg-primary' : 'bg-muted';
};

const handleBarcodeScanned = (scannedValue: string) => {
    form.serial_number = scannedValue;
    showBarcodeScanner.value = false;
};
</script>

<template>
    <Head :title="t('assets.create_asset')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-4xl mx-auto">
            <div class="mb-8">
                <Heading :title="t('assets.create_asset')" />
                <p class="text-gray-600 dark:text-gray-400 mt-2">
                    {{ t('assets.create_asset_description') }}
                </p>
            </div>

            <!-- Step Indicator -->
            <div class="flex items-center justify-center mb-8">
                <div class="flex items-center">
                    <div
                        v-for="step in totalSteps"
                        :key="step"
                        class="flex items-center"
                    >
                        <div
                            :class="getStepClass(step)"
                            class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-medium transition-colors"
                        >
                            {{ step }}
                        </div>
                        <div
                            v-if="step < totalSteps"
                            :class="getStepConnectorClass(step)"
                            class="w-16 h-1 mx-4 transition-colors"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Step Content -->
            <Card>
                <CardHeader>
                    <CardTitle>
                        {{ t(`assets.step_${currentStep}_title`) }}
                    </CardTitle>
                    <CardDescription>
                        {{ t(`assets.step_${currentStep}_description`) }}
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <!-- Step 1: Location Selection -->
                    <div v-if="currentStep === 1" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="location_search">{{ t('assets.location') }} *</Label>
                            <div class="flex gap-2">
                                <div class="flex-1 relative">
                                    <div class="relative">
                                        <Input
                                            id="location_search"
                                            v-model="searchQuery"
                                            type="text"
                                            :placeholder="selectedLocation ? selectedLocation.display_name : t('assets.search_location_placeholder')"
                                            @input="handleSearchInput"
                                            @focus="searchQuery.length >= 2 && (showSearchResults = true)"
                                            @blur="hideSearchResults"
                                            :class="{ 'border-red-500': form.errors.location_id }"
                                            :disabled="selectedLocation !== null"
                                        />
                                        
                                        <!-- Clear button when location is selected -->
                                        <button
                                            v-if="selectedLocation"
                                            type="button"
                                            @click="clearLocationSelection"
                                            class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                        >
                                            <Icon name="X" class="h-4 w-4" />
                                        </button>
                                        
                                        <!-- Loading spinner -->
                                        <div
                                            v-if="isSearching"
                                            class="absolute right-2 top-1/2 transform -translate-y-1/2"
                                        >
                                            <Icon name="Loader2" class="h-4 w-4 animate-spin text-gray-400" />
                                        </div>
                                    </div>
                                    
                                    <!-- Search Results Dropdown -->
                                    <div
                                        v-if="showSearchResults && searchResults.length > 0"
                                        class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg max-h-60 overflow-y-auto"
                                    >
                                        <div
                                            v-for="location in searchResults"
                                            :key="location.id"
                                            @click="selectLocation(location)"
                                            class="px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                                        >
                                            <div class="font-medium text-sm">{{ location.display_name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ location.company_name }}
                                                <span v-if="location.full_address"> • {{ location.full_address }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- No results message -->
                                    <div
                                        v-if="showSearchResults && searchResults.length === 0 && !isSearching && searchQuery.length >= 2"
                                        class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg px-3 py-2"
                                    >
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ t('assets.no_locations_found') }}
                                        </div>
                                    </div>
                                </div>
                                <Dialog v-model:open="isLocationDialogOpen">
                                    <DialogTrigger asChild>
                                        <Button variant="outline" type="button">
                                            <Icon name="Plus" class="h-4 w-4 mr-2 rtl:mr-0 rtl:ml-2" />
                                            {{ t('locations.create_location') }}
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent class="sm:max-w-lg">
                                        <DialogHeader>
                                            <DialogTitle>{{ t('locations.create_location') }}</DialogTitle>
                                            <DialogDescription>
                                                {{ t('locations.create_location_description') }}
                                            </DialogDescription>
                                        </DialogHeader>
                                        <form @submit.prevent="createLocation" class="space-y-4">
                                            <!-- Company Selection -->
                                            <div class="space-y-2">
                                                <Label for="location_company_id">{{ t('locations.company') }} *</Label>
                                                <select
                                                    id="location_company_id"
                                                    v-model="locationForm.company_id"
                                                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                                    :class="{ 'border-red-500': locationForm.errors.company_id }"
                                                    :disabled="locationForm.processing"
                                                    required
                                                >
                                                    <option value="">{{ t('locations.select_company') }}</option>
                                                    <option v-for="company in companies" :key="company.id" :value="company.id">
                                                        {{ company.name_en }} {{ company.name_ar ? `(${company.name_ar})` : '' }}
                                                    </option>
                                                </select>
                                                <div v-if="locationForm.errors.company_id" class="text-sm text-red-600 dark:text-red-400">
                                                    {{ locationForm.errors.company_id }}
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 gap-4">
                                                <div class="space-y-2">
                                                    <Label for="location_name">{{ t('locations.name') }} *</Label>
                                                    <Input
                                                        id="location_name"
                                                        v-model="locationForm.name"
                                                        :error="locationForm.errors.name"
                                                        required
                                                    />
                                                </div>
                                                <div class="space-y-2">
                                                    <Label for="location_code">{{ t('locations.code') }}</Label>
                                                    <Input
                                                        id="location_code"
                                                        type="text"
                                                        :placeholder="t('locations.code_auto_generated')"
                                                        disabled
                                                        class="bg-muted"
                                                    />
                                                    <p class="text-sm text-muted-foreground">{{ t('locations.code_auto_generated') }}</p>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div class="space-y-2">
                                                    <Label for="location_building">{{ t('locations.building') }}</Label>
                                                    <Input
                                                        id="location_building"
                                                        v-model="locationForm.building"
                                                        :error="locationForm.errors.building"
                                                    />
                                                </div>
                                                <div class="space-y-2">
                                                    <Label for="location_office_number">{{ t('locations.office_number') }}</Label>
                                                    <Input
                                                        id="location_office_number"
                                                        v-model="locationForm.office_number"
                                                        :error="locationForm.errors.office_number"
                                                    />
                                                </div>
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="location_address">{{ t('locations.address') }}</Label>
                                                <Input
                                                    id="location_address"
                                                    v-model="locationForm.address"
                                                    :error="locationForm.errors.address"
                                                />
                                            </div>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div class="space-y-2">
                                                    <Label for="location_city">{{ t('locations.city') }}</Label>
                                                    <Input
                                                        id="location_city"
                                                        v-model="locationForm.city"
                                                        :error="locationForm.errors.city"
                                                    />
                                                </div>
                                                <div class="space-y-2">
                                                    <Label for="location_state">{{ t('locations.state') }}</Label>
                                                    <Input
                                                        id="location_state"
                                                        v-model="locationForm.state"
                                                        :error="locationForm.errors.state"
                                                    />
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div class="space-y-2">
                                                    <Label for="location_postal_code">{{ t('locations.postal_code') }}</Label>
                                                    <Input
                                                        id="location_postal_code"
                                                        v-model="locationForm.postal_code"
                                                        :error="locationForm.errors.postal_code"
                                                    />
                                                </div>
                                                <div class="space-y-2">
                                                    <Label for="location_country">{{ t('locations.country') }}</Label>
                                                    <Input
                                                        id="location_country"
                                                        v-model="locationForm.country"
                                                        :error="locationForm.errors.country"
                                                    />
                                                </div>
                                            </div>
                                        </form>
                                        <DialogFooter>
                                            <Button variant="outline" @click="isLocationDialogOpen = false" type="button">
                                                {{ t('common.cancel') }}
                                            </Button>
                                            <Button @click="createLocation" :disabled="locationForm.processing">
                                                <Icon v-if="locationForm.processing" name="Loader2" class="h-4 w-4 mr-2 rtl:mr-0 rtl:ml-2 animate-spin" />
                                                {{ t('common.create') }}
                                            </Button>
                                        </DialogFooter>
                                    </DialogContent>
                                </Dialog>
                            </div>
                            <div v-if="form.errors.location_id" class="text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.location_id }}
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Template Selection and Serial Number -->
                    <div v-else-if="currentStep === 2" class="space-y-6">
                        <!-- Template Selection -->
                        <div class="space-y-2">
                            <Label for="template_search">{{ t('assets.template') }} *</Label>
                            <div class="relative">
                                <div class="relative">
                                    <Input
                                        id="template_search"
                                        v-model="templateSearchQuery"
                                        type="text"
                                        :placeholder="selectedTemplate ? selectedTemplate.display_name : t('assets.search_template_placeholder')"
                                        @input="handleTemplateSearchInput"
                                        @focus="templateSearchQuery.length >= 2 && (showTemplateSearchResults = true)"
                                        @blur="hideTemplateSearchResults"
                                        :class="{ 'border-red-500': form.errors.asset_template_id }"
                                        :disabled="selectedTemplate !== null"
                                    />
                                    
                                    <!-- Clear button when template is selected -->
                                    <button
                                        v-if="selectedTemplate"
                                        type="button"
                                        @click="clearTemplateSelection"
                                        class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                    >
                                        <Icon name="X" class="h-4 w-4" />
                                    </button>
                                    
                                    <!-- Loading spinner -->
                                    <div
                                        v-if="isTemplateSearching"
                                        class="absolute right-2 top-1/2 transform -translate-y-1/2"
                                    >
                                        <Icon name="Loader2" class="h-4 w-4 animate-spin text-gray-400" />
                                    </div>
                                </div>
                                
                                <!-- Search Results Dropdown -->
                                <div
                                    v-if="showTemplateSearchResults && templateSearchResults.length > 0"
                                    class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg max-h-60 overflow-y-auto"
                                >
                                    <div
                                        v-for="template in templateSearchResults"
                                        :key="template.id"
                                        @click="selectTemplate(template)"
                                        class="px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                                    >
                                        <div class="font-medium text-sm">{{ template.display_name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            <span v-if="template.category_name">{{ template.category_name }}</span>
                                            <span v-if="template.category_name && (template.company?.name_en || template.is_global)"> • </span>
                                            <span v-if="template.is_global">{{ t('asset_templates.global') }}</span>
                                            <span v-else-if="template.company?.name_en">{{ template.company.name_en }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- No results message -->
                                <div
                                    v-if="showTemplateSearchResults && templateSearchResults.length === 0 && !isTemplateSearching && templateSearchQuery.length >= 2"
                                    class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg px-3 py-2"
                                >
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ t('assets.no_templates_found') }}
                                    </div>
                                </div>
                            </div>
                            <div v-if="form.errors.asset_template_id" class="text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.asset_template_id }}
                            </div>
                        </div>

                        <!-- Serial Number -->
                        <div class="space-y-2">
                            <Label for="serial_number">{{ t('assets.serial_number') }}</Label>
                            <div class="flex gap-2">
                                <Input
                                    id="serial_number"
                                    v-model="form.serial_number"
                                    type="text"
                                    :placeholder="t('assets.serial_number_placeholder')"
                                    :class="{ 'border-red-500': form.errors.serial_number }"
                                    class="flex-1"
                                />
                                <Button
                                    type="button"
                                    variant="outline"
                                    @click="showBarcodeScanner = true"
                                    class="shrink-0"
                                >
                                    <Icon name="ScanSearch" class="h-4 w-4 mr-2" />
                                    Scan
                                </Button>
                            </div>
                            <p class="text-sm text-muted-foreground">{{ t('assets.serial_number_optional') }}</p>
                            <div v-if="form.errors.serial_number" class="text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.serial_number }}
                            </div>
                        </div>

                        <!-- Condition -->
                        <div class="space-y-2">
                            <Label for="condition">{{ t('assets.condition') }} *</Label>
                            <select
                                id="condition"
                                v-model="form.condition"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                :class="{ 'border-red-500': form.errors.condition }"
                                required
                            >
                                <option value="good">{{ t('assets.condition_good') }}</option>
                                <option value="damaged">{{ t('assets.condition_damaged') }}</option>
                            </select>
                            <div v-if="form.errors.condition" class="text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.condition }}
                            </div>
                        </div>

                        <!-- Asset Assignment Details -->
                        <div class="space-y-6 border-t pt-6">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                {{ t('assets.assignment_details') }}
                            </h4>

                            <!-- Company Selection -->
                            <div class="space-y-2">
                                <Label for="company_search">{{ t('assets.company') }}</Label>
                                <div class="relative">
                                    <div class="relative">
                                        <Input
                                            id="company_search"
                                            v-model="companySearchQuery"
                                            type="text"
                                            :placeholder="selectedCompany ? selectedCompany.display_name : t('assets.search_company_placeholder')"
                                            @input="handleCompanySearchInput"
                                            @focus="companySearchQuery.length >= 2 && (showCompanySearchResults = true)"
                                            @blur="hideCompanySearchResults"
                                            :class="{ 'border-red-500': form.errors.company_id }"
                                            :disabled="selectedCompany !== null"
                                        />
                                        
                                        <!-- Clear button when company is selected -->
                                        <button
                                            v-if="selectedCompany"
                                            type="button"
                                            @click="clearCompanySelection"
                                            class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                        >
                                            <Icon name="X" class="h-4 w-4" />
                                        </button>
                                        
                                        <!-- Loading spinner -->
                                        <div
                                            v-if="isCompanySearching"
                                            class="absolute right-2 top-1/2 transform -translate-y-1/2"
                                        >
                                            <Icon name="Loader2" class="h-4 w-4 animate-spin text-gray-400" />
                                        </div>
                                    </div>
                                    
                                    <!-- Search Results Dropdown -->
                                    <div
                                        v-if="showCompanySearchResults && companySearchResults.length > 0"
                                        class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg max-h-60 overflow-y-auto"
                                    >
                                        <div
                                            v-for="company in companySearchResults"
                                            :key="company.id"
                                            @click="selectCompany(company)"
                                            class="px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                                        >
                                            <div class="font-medium text-sm">{{ company.display_name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ company.company_email }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-sm text-muted-foreground">{{ t('assets.company_optional') }}</p>
                                <div v-if="form.errors.company_id" class="text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.company_id }}
                                </div>
                            </div>

                            <!-- Department Selection -->
                            <div class="space-y-2">
                                <Label for="department_search">{{ t('assets.department') }}</Label>
                                <div class="relative">
                                    <div class="relative">
                                        <Input
                                            id="department_search"
                                            v-model="departmentSearchQuery"
                                            type="text"
                                            :placeholder="selectedDepartment ? selectedDepartment.display_name : t('assets.search_department_placeholder')"
                                            @input="handleDepartmentSearchInput"
                                            @focus="departmentSearchQuery.length >= 2 && (showDepartmentSearchResults = true)"
                                            @blur="hideDepartmentSearchResults"
                                            :class="{ 'border-red-500': form.errors.department_id }"
                                            :disabled="selectedDepartment !== null"
                                        />
                                        
                                        <!-- Clear button when department is selected -->
                                        <button
                                            v-if="selectedDepartment"
                                            type="button"
                                            @click="clearDepartmentSelection"
                                            class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                        >
                                            <Icon name="X" class="h-4 w-4" />
                                        </button>
                                        
                                        <!-- Loading spinner -->
                                        <div
                                            v-if="isDepartmentSearching"
                                            class="absolute right-2 top-1/2 transform -translate-y-1/2"
                                        >
                                            <Icon name="Loader2" class="h-4 w-4 animate-spin text-gray-400" />
                                        </div>
                                    </div>
                                    
                                    <!-- Search Results Dropdown -->
                                    <div
                                        v-if="showDepartmentSearchResults && departmentSearchResults.length > 0"
                                        class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg max-h-60 overflow-y-auto"
                                    >
                                        <div
                                            v-for="department in departmentSearchResults"
                                            :key="department.id"
                                            @click="selectDepartment(department)"
                                            class="px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                                        >
                                            <div class="font-medium text-sm">{{ department.display_name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ department.company_name }}
                                                <span v-if="department.description"> • {{ department.description }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-sm text-muted-foreground">{{ t('assets.department_optional') }}</p>
                                <div v-if="form.errors.department_id" class="text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.department_id }}
                                </div>
                            </div>

                            <!-- Employee Assignment -->
                            <div class="space-y-2">
                                <Label for="employee_search">{{ t('assets.assign_to_employee') }}</Label>
                                <div class="relative">
                                    <div class="relative">
                                        <Input
                                            id="employee_search"
                                            v-model="employeeSearchQuery"
                                            type="text"
                                            :placeholder="selectedEmployee ? selectedEmployee.display_name : t('assets.search_employee_placeholder')"
                                            @input="handleEmployeeSearchInput"
                                            @focus="employeeSearchQuery.length >= 2 && (showEmployeeSearchResults = true)"
                                            @blur="hideEmployeeSearchResults"
                                            :class="{ 'border-red-500': form.errors.assigned_to }"
                                            :disabled="selectedEmployee !== null"
                                        />
                                        
                                        <!-- Clear button when employee is selected -->
                                        <button
                                            v-if="selectedEmployee"
                                            type="button"
                                            @click="clearEmployeeSelection"
                                            class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                        >
                                            <Icon name="X" class="h-4 w-4" />
                                        </button>
                                        
                                        <!-- Loading spinner -->
                                        <div
                                            v-if="isEmployeeSearching"
                                            class="absolute right-2 top-1/2 transform -translate-y-1/2"
                                        >
                                            <Icon name="Loader2" class="h-4 w-4 animate-spin text-gray-400" />
                                        </div>
                                    </div>
                                    
                                    <!-- Search Results Dropdown -->
                                    <div
                                        v-if="showEmployeeSearchResults && employeeSearchResults.length > 0"
                                        class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg max-h-60 overflow-y-auto"
                                    >
                                        <div
                                            v-for="employee in employeeSearchResults"
                                            :key="employee.id"
                                            @click="selectEmployee(employee)"
                                            class="px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                                        >
                                            <div class="font-medium text-sm">{{ employee.display_name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ employee.email }}
                                                <span v-if="employee.department"> • {{ employee.department }}</span>
                                                <span v-if="employee.company_name"> • {{ employee.company_name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-sm text-muted-foreground">{{ t('assets.assign_to_employee_optional') }}</p>
                                <div v-if="form.errors.assigned_to" class="text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.assigned_to }}
                                </div>
                            </div>
                        </div>

                        <!-- Selected Template Preview -->
                        <div v-if="selectedTemplate" class="mt-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">
                                {{ t('assets.selected_template') }}
                            </h4>
                            <div class="space-y-1 text-sm">
                                <div><strong>{{ t('assets.name') }}:</strong> {{ selectedTemplate.name }}</div>
                                <div v-if="selectedTemplate.manufacturer">
                                    <strong>{{ t('assets.manufacturer') }}:</strong> {{ selectedTemplate.manufacturer }}
                                </div>
                                <div v-if="selectedTemplate.model_name">
                                    <strong>{{ t('assets.model') }}:</strong> {{ selectedTemplate.model_name }}
                                </div>
                                <div v-if="selectedTemplate.asset_category">
                                    <strong>{{ t('assets.category') }}:</strong> {{ selectedTemplate.asset_category.name }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Image Upload and Final Review -->
                    <div v-else-if="currentStep === 3" class="space-y-6">
                        <!-- Image Upload -->
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <Label for="asset_image">{{ t('assets.image') }}</Label>
                                <div class="flex items-center justify-center w-full">
                                    <label
                                        for="asset_image"
                                        class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-gray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500"
                                        :class="{ 'border-red-500': fileError }"
                                    >
                                        <div v-if="!filePreview" class="flex flex-col items-center justify-center pt-5 pb-6">
                                            <Icon name="Upload" class="w-10 h-10 mb-3 text-gray-400" />
                                            <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                                <span class="font-semibold">{{ t('assets.click_to_upload') }}</span>
                                                {{ t('assets.or_drag_and_drop') }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ t('assets.supported_formats') }}
                                            </p>
                                        </div>
                                        <div v-else class="relative w-full h-full">
                                            <img
                                                :src="filePreview"
                                                alt="Asset preview"
                                                class="w-full h-full object-contain rounded-lg"
                                            />
                                            <button
                                                type="button"
                                                @click.prevent="removeFile"
                                                class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors"
                                            >
                                                <Icon name="X" class="h-4 w-4" />
                                            </button>
                                        </div>
                                        <input
                                            id="asset_image"
                                            type="file"
                                            accept="image/*"
                                            @change="handleFileSelect"
                                            class="hidden"
                                        />
                                    </label>
                                </div>
                                <div v-if="fileError" class="text-sm text-red-600 dark:text-red-400">
                                    {{ fileError }}
                                </div>
                                <p class="text-sm text-muted-foreground">{{ t('assets.image_optional') }}</p>
                            </div>
                        </div>

                        <!-- Final Review -->
                        <div class="mt-8 p-6 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-4">
                                {{ t('assets.review_asset') }}
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <strong>{{ t('assets.location') }}:</strong>
                                    {{ selectedLocation?.display_name || '—' }}
                                </div>
                                <div>
                                    <strong>{{ t('assets.template') }}:</strong>
                                    {{ selectedTemplate?.display_name || '—' }}
                                </div>
                                <div>
                                    <strong>{{ t('assets.serial_number') }}:</strong>
                                    {{ form.serial_number || t('assets.not_provided') }}
                                </div>
                                <div>
                                    <strong>{{ t('assets.condition') }}:</strong>
                                    {{ form.condition === 'good' ? t('assets.condition_good') : t('assets.condition_damaged') }}
                                </div>
                                <div>
                                    <strong>{{ t('assets.image') }}:</strong>
                                    {{ selectedFile ? selectedFile.name : t('assets.not_provided') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Navigation Buttons -->
            <div class="flex justify-between mt-8">
                <div class="flex gap-2">
                    <Button variant="outline" asChild>
                        <Link :href="route('assets.index')">
                            <Icon name="ArrowLeft" class="h-4 w-4 mr-2 rtl:mr-0 rtl:ml-2" />
                            {{ t('common.cancel') }}
                        </Link>
                    </Button>
                    <Button
                        v-if="currentStep > 1"
                        variant="outline"
                        @click="prevStep"
                    >
                        <Icon name="ChevronLeft" class="h-4 w-4 mr-2 rtl:mr-0 rtl:ml-2" />
                        {{ t('common.previous') }}
                    </Button>
                </div>
                <div class="flex gap-2">
                    <Button
                        v-if="currentStep < totalSteps"
                        @click="nextStep"
                        :disabled="!canGoNext"
                    >
                        {{ t('common.next') }}
                        <Icon name="ChevronRight" class="h-4 w-4 ml-2 rtl:ml-0 rtl:mr-2" />
                    </Button>
                    <Button
                        v-else
                        @click="submit"
                        :disabled="!canGoNext || form.processing"
                    >
                        <Icon v-if="form.processing" name="Loader2" class="h-4 w-4 mr-2 rtl:mr-0 rtl:ml-2 animate-spin" />
                        {{ t('assets.create_asset') }}
                    </Button>
                </div>
            </div>
        </div>

        <!-- Barcode Scanner -->
        <BarcodeScanner
            v-model="showBarcodeScanner"
            title="Scan Serial Number"
            description="Position the barcode on the asset within the camera view to scan the serial number automatically."
            @scanned="handleBarcodeScanned"
            @cancel="showBarcodeScanner = false"
        />
    </AppLayout>
</template> 