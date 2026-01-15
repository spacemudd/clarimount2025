<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { CalendarClock, ArrowUpRight, AlertTriangle, ShieldAlert } from 'lucide-vue-next';

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

interface Props {
    expiringEmployeesPreview: ExpiringEmployeeRow[];
    expiringEmployeesCount: number;
    expiryDaysThreshold: number;
}

const props = defineProps<Props>();

const breadcrumbs = computed((): BreadcrumbItem[] => [
    {
        title: t('nav.dashboard'),
        href: '/dashboard',
    },
]);

const getUrgencyTone = (daysRemaining: number) => {
    if (daysRemaining <= 7) return 'critical';
    if (daysRemaining <= 30) return 'warning';
    return 'info';
};

const getCardAccentClasses = (daysRemaining: number) => {
    const tone = getUrgencyTone(daysRemaining);
    if (tone === 'critical') return 'border-l-4 border-red-500';
    if (tone === 'warning') return 'border-l-4 border-amber-500';
    return 'border-l-4 border-blue-500';
};

const getBadgeClasses = (daysRemaining: number) => {
    const tone = getUrgencyTone(daysRemaining);
    if (tone === 'critical') {
        return 'border-red-200 bg-red-50 text-red-700 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-200';
    }
    if (tone === 'warning') {
        return 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-200';
    }
    return 'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-900/60 dark:bg-blue-950/30 dark:text-blue-200';
};

const getToneIcon = (daysRemaining: number) => {
    const tone = getUrgencyTone(daysRemaining);
    if (tone === 'critical') return ShieldAlert;
    if (tone === 'warning') return AlertTriangle;
    return CalendarClock;
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
    <Head :title="t('nav.dashboard')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
            <div class="relative overflow-hidden rounded-xl border bg-card">
                <div class="absolute inset-0 bg-gradient-to-l from-primary/10 via-transparent to-blue-500/10 dark:from-primary/20 dark:to-blue-500/20" />
                <div class="relative p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="space-y-1">
                            <h1 class="text-2xl font-semibold tracking-tight">
                                {{ t('dashboard.work-portal') }}
                            </h1>
                            <p class="text-sm text-muted-foreground">
                                {{ t('employees.expiry.widget_subtitle') }}
                            </p>
                        </div>
                        <div class="hidden md:flex items-center gap-2">
                            <Badge variant="outline" class="border-primary/30 bg-primary/5 text-primary dark:bg-primary/10">
                                {{ t('employees.expiry.widget_title', { days: expiryDaysThreshold }) }}
                            </Badge>
                        </div>
                    </div>
                </div>
            </div>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <div>
                        <CardTitle class="text-base font-semibold">
                            {{ t('employees.expiry.widget_title', { days: expiryDaysThreshold }) }}
                        </CardTitle>
                        <p class="text-xs text-muted-foreground mt-1">
                            {{ t('employees.expiry.widget_subtitle') }}
                        </p>
                    </div>
                    <Button
                        v-if="expiringEmployeesCount > 0"
                        asChild
                        variant="outline"
                        size="sm"
                    >
                        <Link :href="route('employees.expiring-documents.index')">
                            {{ t('employees.expiry.view_all') }} ({{ expiringEmployeesCount }})
                            <ArrowUpRight class="ml-2 h-4 w-4" />
                        </Link>
                    </Button>
                </CardHeader>

                <CardContent>
                    <div v-if="!expiringEmployeesPreview || expiringEmployeesPreview.length === 0" class="py-6 text-center text-sm text-muted-foreground">
                        {{ t('employees.expiry.no_upcoming', { days: expiryDaysThreshold }) }}
                    </div>

                    <div v-else class="space-y-3">
                        <Card
                            v-for="row in expiringEmployeesPreview"
                            :key="row.employee_id + '-' + row.expiry_field"
                            class="cursor-pointer transition-all hover:-translate-y-[1px] hover:shadow-md"
                            :class="getCardAccentClasses(row.days_remaining)"
                            @click="$inertia.visit(route('employees.show', row.employee_id))"
                        >
                            <CardContent class="py-4">
                                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <component :is="getToneIcon(row.days_remaining)" class="h-4 w-4 text-muted-foreground" />
                                            <div class="text-sm font-semibold">
                                                {{ row.display_name || row.full_name }}
                                            </div>
                                        </div>
                                        <div class="text-xs text-muted-foreground">
                                            {{ t(row.expiry_label_key) }}
                                            <span class="mx-2">•</span>
                                            {{ t('employees.expiry.expiry_date') }}:
                                            {{ new Date(row.expiry_date).toLocaleDateString() }}
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between gap-3 md:justify-end">
                                        <Badge variant="outline" :class="getBadgeClasses(row.days_remaining)">
                                            {{ formatRemainingText(row.days_remaining) }}
                                        </Badge>
                                        <Button variant="outline" size="sm" asChild @click.stop>
                                            <Link :href="route('employees.show', row.employee_id)">
                                                {{ t('employees.view') }}
                                            </Link>
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
