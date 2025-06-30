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
    company: Company;
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
                <Heading :title="t('locations.my_locations')" />
                <Button asChild>
                    <Link :href="route('locations.create')">
                        <Icon name="Plus" class="mr-2 h-4 w-4" />
                        {{ t('locations.create_location') }}
                    </Link>
                </Button>
            </div>

            <div v-if="locations.data.length === 0" class="text-center py-12">
                <Icon name="MapPin" class="mx-auto h-12 w-12 text-gray-400 mb-4" />
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                    {{ t('locations.no_locations') }}
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    {{ t('locations.create_first_location') }}
                </p>
                <Button asChild>
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
                        <CardDescription class="font-mono text-sm">
                            {{ location.code }}
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