<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { computed } from 'vue';
import type { BreadcrumbItem, Employee } from '@/types';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';

const { t } = useI18n();

interface ExpiringEmployeeRow {
    employee_id: number;
    display_name: string;
    full_name: string;
    expiry_field: string;
    expiry_label_key: string;
    expiry_date: string;
    days_remaining: number;
}

interface Paginated<T> {
    data: T[];
    links: any[];
    meta: any;
}

interface Props {
    expiringEmployees: Paginated<ExpiringEmployeeRow>;
    days: number;
}

const props = defineProps<Props>();

const breadcrumbs = computed((): BreadcrumbItem[] => [
    {
        title: t('nav.dashboard'),
        href: '/dashboard',
    },
    {
        title: t('nav.employees'),
        href: '/employees',
    },
    {
        title: t('employees.expiry.view_all_title'),
        href: route('employees.expiring-documents.index'),
    },
]);

const getStatusVariant = (daysRemaining: number) => {
    if (daysRemaining <= 0) {
        return 'destructive';
    }
    if (daysRemaining <= 30) {
        return 'warning';
    }
    return 'secondary';
};

const formatRemainingText = (daysRemaining: number) => {
    if (daysRemaining < 0) {
        const days = Math.abs(daysRemaining);
        return t('employees.expiry.expired_days_ago', { days });
    }

    if (daysRemaining === 0) {
        return t('employees.expiry.expires_today');
    }

    return t('employees.expiry.days_remaining', { days: daysRemaining });
};
</script>

<template>
    <Head :title="t('employees.expiry.view_all_title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto py-6 space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">
                    {{ t('employees.expiry.view_all_title') }}
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{ t('employees.expiry.view_all_subtitle', { days }) }}
                </p>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle class="text-base">
                        {{ t('employees.expiry.table_title') }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="!props.expiringEmployees.data.length" class="py-6 text-center text-sm text-muted-foreground">
                        {{ t('employees.expiry.no_upcoming', { days }) }}
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b text-xs text-muted-foreground">
                                    <th class="py-2 pr-4 text-right">
                                        {{ t('employees.full_name') }}
                                    </th>
                                    <th class="py-2 px-4 text-right">
                                        {{ t('employees.expiry.document_type') }}
                                    </th>
                                    <th class="py-2 px-4 text-right">
                                        {{ t('employees.expiry.expiry_date') }}
                                    </th>
                                    <th class="py-2 px-4 text-right">
                                        {{ t('employees.expiry.remaining') }}
                                    </th>
                                    <th class="py-2 px-4"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="row in props.expiringEmployees.data"
                                    :key="row.employee_id + '-' + row.expiry_field"
                                    class="border-b last:border-0 hover:bg-muted/40"
                                >
                                    <td class="py-2 pr-4">
                                        <span class="font-medium">
                                            {{ row.display_name || row.full_name }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-4">
                                        {{ t(row.expiry_label_key) }}
                                    </td>
                                    <td class="py-2 px-4">
                                        {{ new Date(row.expiry_date).toLocaleDateString() }}
                                    </td>
                                    <td class="py-2 px-4">
                                        <Badge :variant="getStatusVariant(row.days_remaining)">
                                            {{ formatRemainingText(row.days_remaining) }}
                                        </Badge>
                                    </td>
                                    <td class="py-2 px-4 text-left">
                                        <Button variant="outline" size="xs" asChild>
                                            <Link :href="route('employees.show', row.employee_id)">
                                                {{ t('employees.view') }}
                                            </Link>
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div
                        v-if="props.expiringEmployees.links && props.expiringEmployees.links.length"
                        class="mt-4 flex flex-wrap justify-center gap-2"
                    >
                        <Button
                            v-for="link in props.expiringEmployees.links"
                            :key="link.label"
                            v-html="link.label"
                            :variant="link.active ? 'default' : 'outline'"
                            :disabled="!link.url"
                            size="sm"
                            class="min-w-[2.5rem]"
                            @click="link.url && $inertia.visit(link.url)"
                        />
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
></template>


