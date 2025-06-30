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
import type { Asset, Company, AssetCategory, Location, BreadcrumbItem } from '@/types';

const { t } = useI18n();

interface Props {
    assets: {
        data: Asset[];
        links: any[];
        meta: any;
    };
    categories: AssetCategory[];
    locations: Location[];
    filters?: {
        search?: string;
        category_id?: string;
        location_id?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters?.search || '');
const categoryFilter = ref(props.filters?.category_id || '');
const locationFilter = ref(props.filters?.location_id || '');

const breadcrumbs = computed((): BreadcrumbItem[] => [
    {
        title: t('nav.dashboard'),
        href: '/dashboard',
    },
    {
        title: t('nav.assets'),
        href: '/assets',
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

// Debounced search
let searchTimeout: number;
watch([search, categoryFilter, locationFilter], () => {
    clearTimeout(searchTimeout);
    searchTimeout = window.setTimeout(() => {
        router.get('/assets', {
            search: search.value || undefined,
            category_id: categoryFilter.value || undefined,
            location_id: locationFilter.value || undefined,
        }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

const clearFilters = () => {
    search.value = '';
    categoryFilter.value = '';
    locationFilter.value = '';
};

const getImageSrc = (asset: Asset) => {
    const imagePath = asset.image_path || asset.assetTemplate?.image_path;
    if (imagePath) {
        return `/storage/${imagePath}`;
    }
    return null;
};

const handleImageError = (event: Event) => {
    const target = event.target as HTMLImageElement;
    // Hide the broken image so the fallback icon shows
    target.style.display = 'none';
};
</script>

<template>
    <Head :title="t('assets.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <div class="flex items-center justify-between my-2">
                <Heading :title="t('assets.title')" />
                <Button asChild>
                    <Link :href="route('assets.create')">
                        <Icon name="Plus" class="mr-2 rtl:mr-0 rtl:ml-2 h-4 w-4" />
                        {{ t('assets.create_asset') }}
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
                        :placeholder="t('assets.search_placeholder')"
                        class="w-full pl-10 rtl:pl-3 rtl:pr-10"
                    />
                </div>
                
                <select 
                    v-model="categoryFilter"
                    class="flex h-10 w-full sm:w-48 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <option value="">{{ t('assets.filter_by_category') }}</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                    </option>
                </select>

                <select 
                    v-model="locationFilter"
                    class="flex h-10 w-full sm:w-48 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <option value="">{{ t('assets.filter_by_location') }}</option>
                    <option v-for="location in locations" :key="location.id" :value="location.id">
                        {{ `${location.code}: ${location.name}` }}
                    </option>
                </select>

                <Button variant="ghost" @click="clearFilters" v-if="search || categoryFilter || locationFilter">
                    <Icon name="X" class="mr-2 rtl:mr-0 rtl:ml-2 h-4 w-4" />
                    {{ t('common.clear') }}
                </Button>
            </div>

            <div v-if="assets.data.length === 0" class="text-center py-12">
                <Icon name="HardDrive" class="mx-auto h-12 w-12 text-gray-400 mb-4" />
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                    {{ t('assets.no_assets') }}
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    {{ search || categoryFilter || locationFilter ? t('assets.no_assets_found') : t('assets.create_first_asset') }}
                </p>
                <Button asChild v-if="!search && !categoryFilter && !locationFilter">
                    <Link :href="route('assets.create')">
                        <Icon name="Plus" class="mr-2 rtl:mr-0 rtl:ml-2 h-4 w-4" />
                        {{ t('assets.create_asset') }}
                    </Link>
                </Button>
            </div>

            <!-- Assets Table -->
            <div v-else class="bg-white dark:bg-gray-800 rounded-lg border overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr class="text-left rtl:text-right">
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('assets.asset') }}
                                </th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('assets.company') }}
                                </th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('assets.category') }}
                                </th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('assets.location') }}
                                </th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('assets.condition') }}
                                </th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('assets.status') }}
                                </th>
                                <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ t('common.actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="asset in assets.data" :key="asset.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <!-- Asset Image or Template Image -->
                                            <div v-if="getImageSrc(asset)" class="h-10 w-10 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-600">
                                                <img 
                                                    :src="getImageSrc(asset)!" 
                                                    :alt="asset.asset_tag"
                                                    class="h-full w-full object-cover"
                                                    @error="handleImageError"
                                                />
                                            </div>
                                            <!-- Fallback Icon -->
                                            <div v-else class="h-10 w-10 rounded-lg bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                                <Icon name="HardDrive" class="h-5 w-5 text-gray-700 dark:text-gray-300" />
                                            </div>
                                        </div>
                                        <div class="ml-4 rtl:ml-0 rtl:mr-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ asset.asset_tag }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ asset.model_name || asset.serial_number || '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ asset.company?.name_en || '-' }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ asset.company?.slug || '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ asset.category?.name || '-' }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ asset.category?.code || '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ asset.location?.name || '-' }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ asset.location?.code || '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <Badge :variant="asset.condition === 'good' ? 'default' : 'destructive'">
                                        {{ t(`assets.condition_${asset.condition}`) }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <Badge :variant="getStatusVariant(asset.status)">
                                        {{ t(`assets.status_${asset.status}`) }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right rtl:text-left text-sm font-medium">
                                    <div class="flex justify-end rtl:justify-start">
                                        <Button variant="ghost" size="sm" asChild class="mr-2 rtl:mr-0 rtl:ml-2">
                                            <Link :href="route('assets.show', asset.id)">
                                                <Icon name="Eye" class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="sm" asChild>
                                            <Link :href="route('assets.edit', asset.id)">
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