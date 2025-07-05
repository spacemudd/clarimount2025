<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import Icon from '@/components/Icon.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { useI18n } from 'vue-i18n';
import { computed, ref } from 'vue';
import type { Asset, AssetCategory, Location, Company, BreadcrumbItem } from '@/types';
import BarcodeScanner from '@/components/BarcodeScanner.vue';

const { t } = useI18n();

interface Props {
    asset: Asset;
    categories: AssetCategory[];
    locations: Location[];
    companies: Company[];
}

const props = defineProps<Props>();

// Form state
const form = useForm({
    serial_number: props.asset.serial_number || '',
    service_tag_number: props.asset.service_tag_number || '',
    finance_tag_number: props.asset.finance_tag_number || '',
    asset_category_id: props.asset.asset_category_id,
    location_id: props.asset.location_id,
    company_id: props.asset.company_id,
    model_name: props.asset.model_name || '',
    model_number: props.asset.model_number || '',
    condition: props.asset.condition || 'good',
    notes: props.asset.notes || '',
    image: null as File | null,
    remove_image: false as boolean,
});

// Image handling
const imagePreview = ref<string | null>(null);
const imageDeleted = ref(false);
const showBarcodeScanner = ref(false);

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
        title: props.asset.asset_tag,
        href: `/assets/${props.asset.id}`,
    },
    {
        title: t('assets.edit_asset'),
        href: `/assets/${props.asset.id}/edit`,
    },
]);

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'available':
            return 'default';
        case 'assigned':
            return 'secondary';
        case 'maintenance':
            return 'destructive';
        case 'retired':
            return 'outline';
        default:
            return 'secondary';
    }
};

const handleImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    
    if (!file) {
        form.image = null;
        imagePreview.value = null;
        return;
    }

    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
    if (!allowedTypes.includes(file.type)) {
        alert(t('assets.invalid_file_type'));
        target.value = '';
        return;
    }

    // Validate file size (5MB)
    if (file.size > 5 * 1024 * 1024) {
        alert(t('assets.file_too_large'));
        target.value = '';
        return;
    }

    form.image = file;
    
    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
        imagePreview.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);
};

const removeImage = () => {
    form.image = null;
    imagePreview.value = null;
    const fileInput = document.getElementById('image') as HTMLInputElement;
    if (fileInput) {
        fileInput.value = '';
    }
};

const markImageForDeletion = () => {
    imageDeleted.value = true;
    form.remove_image = true;
};

const restoreImage = () => {
    imageDeleted.value = false;
    form.remove_image = false;
};

const handleImageError = () => {
    console.warn('Failed to load asset image');
};

const handleBarcodeScanned = (scannedValue: string) => {
    form.serial_number = scannedValue;
    showBarcodeScanner.value = false;
};

const submit = () => {
    form.put(route('assets.update', props.asset.id), {
        onSuccess: () => {
            // Reset image state
            imagePreview.value = null;
            imageDeleted.value = false;
            form.image = null;
            form.remove_image = false;
        },
    });
};
</script>

<template>
    <Head :title="`${t('assets.edit_asset')} - ${asset.asset_tag}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <Heading :title="`${t('assets.edit_asset')} - ${asset.asset_tag}`" />
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ t('assets.edit_asset_description') }}
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <Button variant="outline" asChild>
                        <Link :href="route('assets.show', asset.id)">
                            <Icon name="Eye" class="mr-2 rtl:mr-0 rtl:ml-2 h-4 w-4" />
                            {{ t('common.view') }}
                        </Link>
                    </Button>
                    <Button variant="outline" asChild>
                        <Link :href="route('assets.index')">
                            <Icon name="ArrowLeft" class="mr-2 rtl:mr-0 rtl:ml-2 h-4 w-4" />
                            {{ t('common.back') }}
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Form -->
                <div class="lg:col-span-2">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Basic Information -->
                        <Card>
                            <CardHeader>
                                <CardTitle>{{ t('assets.basic_information') }}</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-6">
                                <!-- Asset Tag (Read-only) -->
                                <div>
                                    <Label for="asset_tag">{{ t('assets.asset_tag') }}</Label>
                                    <Input
                                        id="asset_tag"
                                        :value="asset.asset_tag"
                                        disabled
                                        class="mt-1 bg-gray-50 dark:bg-gray-800"
                                    />
                                    <p class="text-xs text-gray-500 mt-1">
                                        Asset tag cannot be changed
                                    </p>
                                </div>

                                <!-- Serial Numbers -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <Label for="serial_number">{{ t('assets.serial_number') }}</Label>
                                        <div class="flex gap-2 mt-1">
                                            <Input
                                                id="serial_number"
                                                v-model="form.serial_number"
                                                placeholder="Enter serial number"
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
                                        <InputError v-if="form.errors.serial_number" :message="form.errors.serial_number" class="mt-1" />
                                    </div>

                                    <div>
                                        <Label for="service_tag_number">{{ t('assets.service_tag_number') }}</Label>
                                        <Input
                                            id="service_tag_number"
                                            v-model="form.service_tag_number"
                                            placeholder="Service tag number"
                                            class="mt-1"
                                        />
                                        <InputError v-if="form.errors.service_tag_number" :message="form.errors.service_tag_number" class="mt-1" />
                                    </div>

                                    <div>
                                        <Label for="finance_tag_number">{{ t('assets.finance_tag_number') }}</Label>
                                        <Input
                                            id="finance_tag_number"
                                            v-model="form.finance_tag_number"
                                            placeholder="Finance tag number"
                                            class="mt-1"
                                        />
                                        <InputError v-if="form.errors.finance_tag_number" :message="form.errors.finance_tag_number" class="mt-1" />
                                    </div>
                                </div>

                                <!-- Model Information -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <Label for="model_name">{{ t('assets.model_name') }}</Label>
                                        <Input
                                            id="model_name"
                                            v-model="form.model_name"
                                            placeholder="Model name"
                                            class="mt-1"
                                        />
                                        <InputError v-if="form.errors.model_name" :message="form.errors.model_name" class="mt-1" />
                                    </div>

                                    <div>
                                        <Label for="model_number">{{ t('assets.model_number') }}</Label>
                                        <Input
                                            id="model_number"
                                            v-model="form.model_number"
                                            placeholder="Model number"
                                            class="mt-1"
                                        />
                                        <InputError v-if="form.errors.model_number" :message="form.errors.model_number" class="mt-1" />
                                    </div>
                                </div>

                                <!-- Category -->
                                <div>
                                    <Label for="asset_category_id">{{ t('assets.category') }} *</Label>
                                    <select
                                        id="asset_category_id"
                                        v-model="form.asset_category_id"
                                        required
                                        class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <option value="">Select a category</option>
                                        <option 
                                            v-for="category in categories" 
                                            :key="category.id" 
                                            :value="category.id"
                                        >
                                            {{ '—'.repeat(Math.max(0, category.depth || 0)) }} {{ category.name }}
                                        </option>
                                    </select>
                                    <InputError v-if="form.errors.asset_category_id" :message="form.errors.asset_category_id" class="mt-1" />
                                </div>

                                <!-- Company -->
                                <div>
                                    <Label for="company_id">{{ t('assets.company') }} *</Label>
                                    <select
                                        id="company_id"
                                        v-model="form.company_id"
                                        required
                                        class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <option value="">{{ t('assets.select_company') }}</option>
                                        <option 
                                            v-for="company in companies" 
                                            :key="company.id" 
                                            :value="company.id"
                                        >
                                            {{ company.name_en }}
                                        </option>
                                    </select>
                                    <InputError v-if="form.errors.company_id" :message="form.errors.company_id" class="mt-1" />
                                    <p class="text-xs text-gray-500 mt-1">
                                        Changing the company will affect asset ownership and access permissions.
                                    </p>
                                </div>

                                <!-- Location -->
                                <div>
                                    <Label for="location_id">{{ t('assets.location') }} *</Label>
                                    <select
                                        id="location_id"
                                        v-model="form.location_id"
                                        required
                                        class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <option value="">Select a location</option>
                                        <option 
                                            v-for="location in locations" 
                                            :key="location.id" 
                                            :value="location.id"
                                        >
                                            {{ location.code ? `${location.code}: ${location.name}` : location.name }}
                                        </option>
                                    </select>
                                    <InputError v-if="form.errors.location_id" :message="form.errors.location_id" class="mt-1" />
                                </div>

                                <!-- Condition -->
                                <div>
                                    <Label for="condition">{{ t('assets.condition') }} *</Label>
                                    <select
                                        id="condition"
                                        v-model="form.condition"
                                        required
                                        class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <option value="good">{{ t('assets.condition_good') }}</option>
                                        <option value="damaged">{{ t('assets.condition_damaged') }}</option>
                                    </select>
                                    <InputError v-if="form.errors.condition" :message="form.errors.condition" class="mt-1" />
                                </div>

                                <!-- Notes -->
                                <div>
                                    <Label for="notes">{{ t('assets.notes') }}</Label>
                                    <textarea
                                        id="notes"
                                        v-model="form.notes"
                                        rows="4"
                                        placeholder="Additional notes about this asset"
                                        class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    />
                                    <InputError v-if="form.errors.notes" :message="form.errors.notes" class="mt-1" />
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Image Management -->
                        <Card>
                            <CardHeader>
                                <CardTitle>{{ t('assets.asset_image') }}</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-6">
                                <!-- Current Image -->
                                <div v-if="asset.image_path && !imageDeleted" class="space-y-3">
                                    <Label class="text-sm font-medium">Current Image</Label>
                                    <div class="relative inline-block">
                                        <img 
                                            :src="`/storage/${asset.image_path}`" 
                                            :alt="asset.asset_tag" 
                                            class="max-w-sm max-h-64 rounded-lg border-2 border-gray-200 dark:border-gray-600 shadow-sm"
                                            @error="handleImageError"
                                        />
                                        <button 
                                            type="button" 
                                            @click="markImageForDeletion"
                                            class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-2 shadow-lg transition-colors"
                                            title="Remove current image"
                                        >
                                            <Icon name="X" class="h-4 w-4" />
                                        </button>
                                    </div>
                                    <p class="text-xs text-gray-500">Click the X to remove this image</p>
                                </div>

                                <!-- Image Deletion Notice -->
                                <div v-if="imageDeleted" class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                    <div class="flex items-center gap-2">
                                        <Icon name="AlertTriangle" class="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                                        <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                            Current image will be removed when you save the asset.
                                        </p>
                                        <button 
                                            type="button" 
                                            @click="restoreImage"
                                            class="ml-auto text-sm text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 underline"
                                        >
                                            Undo
                                        </button>
                                    </div>
                                </div>

                                <!-- Image Upload -->
                                <div class="space-y-3">
                                    <Label for="image" class="text-sm font-medium">
                                        {{ asset.image_path ? 'Replace Image' : 'Upload Image' }}
                                    </Label>
                                    
                                    <!-- File Input -->
                                    <div class="relative">
                                        <input
                                            id="image"
                                            type="file"
                                            accept="image/jpeg,image/png,image/jpg,image/gif"
                                            @change="handleImageChange"
                                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-300 border border-gray-300 rounded-lg dark:border-gray-600"
                                        />
                                        <div class="mt-2 flex items-center gap-2 text-xs text-gray-500">
                                            <Icon name="Info" class="h-4 w-4" />
                                            <span>Supported: PNG, JPG, GIF up to 5MB</span>
                                        </div>
                                    </div>
                                    
                                    <InputError v-if="form.errors.image" :message="form.errors.image" class="mt-1" />
                                    
                                    <!-- New Image Preview -->
                                    <div v-if="imagePreview" class="mt-4 space-y-3">
                                        <Label class="text-sm font-medium text-green-700 dark:text-green-400">New Image Preview</Label>
                                        <div class="relative inline-block">
                                            <img 
                                                :src="imagePreview" 
                                                alt="New image preview" 
                                                class="max-w-sm max-h-64 rounded-lg border-2 border-green-200 dark:border-green-600 shadow-sm"
                                            />
                                            <button 
                                                type="button" 
                                                @click="removeImage"
                                                class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-2 shadow-lg transition-colors"
                                                title="Remove new image"
                                            >
                                                <Icon name="X" class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-between">
                            <Button variant="outline" type="button" asChild>
                                <Link :href="route('assets.show', asset.id)">
                                    <Icon name="ArrowLeft" class="mr-2 rtl:mr-0 rtl:ml-2 h-4 w-4" />
                                    {{ t('common.cancel') }}
                                </Link>
                            </Button>
                            
                            <Button 
                                type="submit" 
                                :disabled="form.processing"
                                class="min-w-32"
                            >
                                <Icon v-if="form.processing" name="Loader2" class="mr-2 rtl:mr-0 rtl:ml-2 h-4 w-4 animate-spin" />
                                <Icon v-else name="Save" class="mr-2 rtl:mr-0 rtl:ml-2 h-4 w-4" />
                                {{ form.processing ? t('common.saving') : t('common.save_changes') }}
                            </Button>
                        </div>
                    </form>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Asset Overview -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Asset Overview</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ t('assets.asset_tag') }}
                                </Label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-mono">
                                    {{ asset.asset_tag }}
                                </p>
                            </div>

                            <div>
                                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ t('assets.status') }}
                                </Label>
                                <Badge 
                                    :variant="getStatusVariant(asset.status)"
                                    class="mt-1"
                                >
                                    {{ t(`assets.status_${asset.status}`) }}
                                </Badge>
                            </div>

                            <div v-if="asset.company">
                                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ t('assets.company') }}
                                </Label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ asset.company.name_en }}
                                </p>
                            </div>

                            <div>
                                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ t('assets.created_at') }}
                                </Label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ new Date(asset.created_at).toLocaleDateString() }}
                                </p>
                            </div>

                            <div>
                                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ t('assets.updated_at') }}
                                </Label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ new Date(asset.updated_at).toLocaleDateString() }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Template Information -->
                    <Card v-if="asset.assetTemplate">
                        <CardHeader>
                            <CardTitle>Template Information</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div>
                                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Template Name
                                </Label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ asset.assetTemplate.name }}
                                </p>
                            </div>
                            
                            <div>
                                <Label class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Template Type
                                </Label>
                                <Badge 
                                    :variant="asset.assetTemplate.is_global ? 'secondary' : 'default'"
                                    class="mt-1"
                                >
                                    {{ asset.assetTemplate.is_global ? 'Global' : asset.assetTemplate.company?.name_en }}
                                </Badge>
                            </div>
                            
                            <div class="flex justify-end pt-2">
                                <Link 
                                    :href="route('asset-templates.show', asset.assetTemplate.id)"
                                    class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                >
                                    View Template
                                    <Icon name="ExternalLink" class="ml-1 rtl:ml-0 rtl:mr-1 h-3 w-3" />
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <Button variant="outline" size="sm" asChild class="w-full justify-start">
                                <Link :href="route('assets.show', asset.id)">
                                    <Icon name="Eye" class="mr-2 rtl:mr-0 rtl:ml-2 h-4 w-4" />
                                    View Details
                                </Link>
                            </Button>
                            
                            <Button variant="outline" size="sm" asChild class="w-full justify-start">
                                <Link :href="route('assets.index')">
                                    <Icon name="List" class="mr-2 rtl:mr-0 rtl:ml-2 h-4 w-4" />
                                    All Assets
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>
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