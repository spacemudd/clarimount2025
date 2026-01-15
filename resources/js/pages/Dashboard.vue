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

const breadcrumbs = computed((): BreadcrumbItem[] => []);

const getUrgencyTone = (daysRemaining: number) => {
    if (daysRemaining <= 7) return 'critical';
    if (daysRemaining <= 30) return 'warning';
    return 'info';
};

const getCardAccentClasses = () => {
    // Fixed blue border for all cards
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

const getToneIcon = () => {
    // Use consistent icon for all cards
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
            <Card>
                <CardHeader class="flex flex-row items-center justify-end space-y-0 pb-2" v-if="expiringEmployeesCount > 0">
                    <Button
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
                            class="cursor-pointer transition-all hover:shadow-md border-blue-200"
                            :class="getCardAccentClasses()"
                            @click="$inertia.visit(route('employees.show', row.employee_id))"
                        >
                            <CardContent class="py-4">
                                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                    <div class="space-y-1.5 flex-1">
                                        <div class="flex items-center gap-2">
                                            <component :is="getToneIcon()" class="h-4 w-4 text-blue-600" />
                                            <div class="text-sm font-semibold text-foreground">
                                                {{ row.display_name || row.full_name }}
                                            </div>
                                        </div>
                                        <div class="text-xs text-muted-foreground pl-6">
                                            {{ t(row.expiry_label_key) }}
                                            <span class="mx-2">•</span>
                                            {{ t('employees.expiry.expiry_date') }}:
                                            <span class="font-medium">{{ new Date(row.expiry_date).toLocaleDateString() }}</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 md:justify-end">
                                        <Badge variant="outline" class="border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-800 dark:bg-blue-950/30 dark:text-blue-300">
                                            {{ formatRemainingText(row.days_remaining) }}
                                        </Badge>
                                        <Button variant="outline" size="sm" asChild @click.stop class="border-blue-200 hover:bg-blue-50">
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
