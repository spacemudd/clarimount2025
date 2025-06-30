<template>
    <AppLayout>
        <div class="container mx-auto py-8">
            <div class="space-y-6">
                <div class="space-y-2">
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <Heading :title="location.name" />
                            <div class="flex items-center gap-2">
                                <Badge :class="location.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                    {{ location.is_active ? t('locations.active') : t('locations.inactive') }}
                                </Badge>
                                <span class="text-sm font-mono text-muted-foreground">{{ location.code }}</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <Button variant="outline" asChild>
                                <Link :href="route('locations.edit', location.id)">
                                    <Icon name="SquarePen" class="mr-2 h-4 w-4" />
                                    {{ t('locations.edit') }}
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Location Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>{{ t('locations.location_details') }}</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">{{ t('locations.name') }}</Label>
                                    <p class="text-sm">{{ location.name }}</p>
                                </div>
                                
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">{{ t('locations.code') }}</Label>
                                    <p class="text-sm font-mono">{{ location.code }}</p>
                                </div>
                                
                                <div v-if="location.building" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <Label class="text-sm font-medium text-muted-foreground">{{ t('locations.building') }}</Label>
                                        <p class="text-sm">{{ location.building }}</p>
                                    </div>
                                </div>
                                
                                <div v-if="location.office_number">
                                    <Label class="text-sm font-medium text-muted-foreground">{{ t('locations.office_number') }}</Label>
                                    <p class="text-sm">{{ location.office_number }}</p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Quick Stats -->
                    <div class="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>{{ t('common.statistics') }}</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <Icon name="Package" class="h-4 w-4 text-muted-foreground" />
                                        <span class="text-sm">{{ t('locations.assets_count') }}</span>
                                    </div>
                                    <span class="font-medium">{{ location.assets_count || 0 }}</span>
                                </div>
                            </CardContent>
                        </Card>
                        
                        <Card>
                            <CardHeader>
                                <CardTitle>{{ t('common.created_at') }}</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p class="text-sm text-muted-foreground">
                                    {{ new Date(location.created_at).toLocaleDateString() }}
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/layouts/AppLayout.vue';
import { 
    Card, 
    CardContent, 
    CardHeader, 
    CardTitle 
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import Heading from '@/components/Heading.vue';
import Icon from '@/components/Icon.vue';
import type { Location } from '@/types';
import type { BreadcrumbItem } from '@/types';

interface Props {
    location: Location;
}

const props = defineProps<Props>();
const { t } = useI18n();

const breadcrumbs = computed((): BreadcrumbItem[] => [
    {
        title: t('nav.dashboard'),
        href: '/dashboard',
    },
    {
        title: t('locations.title'),
        href: '/locations',
    },
    {
        title: props.location.name,
        href: `/locations/${props.location.id}`,
    },
]);
</script> 