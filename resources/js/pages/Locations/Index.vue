<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import Icon from '@/components/Icon.vue';
import Heading from '@/components/Heading.vue';
import { useI18n } from 'vue-i18n';
import { computed } from 'vue';
import type { Location, Company, BreadcrumbItem } from '@/types';

const { t } = useI18n();

interface Props {
    locations: {
        data: Location[];
        links: any[];
        meta: any;
    };
    companies: Company[];
    currentCompany?: Company;
}

defineProps<Props>();

const breadcrumbs = computed((): BreadcrumbItem[] => [
    {
        title: t('nav.dashboard'),
        href: '/dashboard',
    },
    {
        title: t('locations.title'),
        href: '/locations',
    },
]);
</script>

<template>
    <Head :title="t('locations.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <Heading :title="t('locations.my_locations')" />
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ t('locations.all_companies_locations') }}
                    </p>
                </div>
                <Button asChild class="bg-blue-600 hover:bg-blue-700 text-white font-semibold">
                    <Link :href="route('locations.create')">
                        <Icon name="Plus" class="mr-2 h-4 w-4" />
                        {{ t('locations.create_location') }}
                    </Link>
                </Button>
            </div>

            <!-- Summary Statistics -->
            <div v-if="locations.data.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                                <Icon name="MapPin" class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ locations.data.length }}</p>
                                <p class="text-sm text-gray-500">{{ t('locations.total_locations') }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                
                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                                <Icon name="Building2" class="h-5 w-5 text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ companies.length }}</p>
                                <p class="text-sm text-gray-500">{{ t('locations.total_companies') }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                
                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                                <Icon name="Package" class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ locations.data.reduce((sum, loc) => sum + (loc.assets_count || 0), 0) }}
                                </p>
                                <p class="text-sm text-gray-500">{{ t('locations.total_assets') }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-if="locations.data.length === 0" class="text-center py-12">
                <Icon name="MapPin" class="mx-auto h-12 w-12 text-gray-400 mb-4" />
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                    {{ t('locations.no_locations') }}
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    {{ t('locations.create_first_location') }}
                </p>
                <Button asChild class="bg-blue-600 hover:bg-blue-700 text-white font-semibold">
                    <Link :href="route('locations.create')">
                        <Icon name="Plus" class="mr-2 h-4 w-4" />
                        {{ t('locations.create_location') }}
                    </Link>
                </Button>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Card v-for="location in locations.data" :key="location.id" class="hover:shadow-lg transition-shadow">
                    <CardHeader class="pb-3">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-lg">{{ location.name }}</CardTitle>
                            <Badge :variant="location.is_active ? 'default' : 'secondary'">
                                {{ location.is_active ? t('locations.active') : t('locations.inactive') }}
                            </Badge>
                        </div>
                        <CardDescription class="space-y-1">
                            <div class="font-mono text-sm">{{ location.code }}</div>
                            <div v-if="location.company" class="flex items-center gap-2 text-xs">
                                <Icon name="Building2" class="w-3 h-3" />
                                <span class="text-blue-600 dark:text-blue-400 font-medium">{{ location.company.name_en }}</span>
                            </div>
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div class="flex items-center gap-2">
                                <Badge :class="location.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                    {{ location.is_active ? t('locations.active') : t('locations.inactive') }}
                                </Badge>
                                <span class="text-sm font-mono text-muted-foreground">{{ location.code }}</span>
                            </div>
                            
                            <div v-if="location.building || location.office_number" class="text-sm text-muted-foreground space-y-1">
                                <div v-if="location.building" class="flex items-center gap-2">
                                    <Icon name="Building" class="w-4 h-4" />
                                    {{ location.building }}
                                </div>
                                <div v-if="location.office_number" class="flex items-center gap-2">
                                    <Icon name="Hash" class="w-4 h-4" />
                                    {{ t('locations.office_number') }}: {{ location.office_number }}
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-3 border-t">
                                <div class="flex space-x-4 text-sm text-muted-foreground">
                                    <div class="flex items-center gap-1">
                                        <Icon name="Package" class="h-4 w-4" />
                                        <span>{{ location.assets_count || 0 }} {{ t('locations.assets_count') }}</span>
                                    </div>
                                </div>
                                
                                <div class="flex space-x-2">
                                    <Button variant="ghost" size="sm" asChild>
                                        <Link :href="route('locations.show', location.id)">
                                            {{ t('locations.view') }}
                                        </Link>
                                    </Button>
                                    <Button variant="ghost" size="sm" asChild>
                                        <Link :href="route('locations.edit', location.id)">
                                            {{ t('locations.edit') }}
                                        </Link>
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Pagination would go here if needed -->
        </div>
    </AppLayout>
</template> 