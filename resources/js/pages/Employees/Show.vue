<template>
    <AppLayout>
        <div class="container mx-auto py-8">
            <div class="space-y-6">
                <div class="space-y-2">
                    <Breadcrumbs :items="breadcrumbs" />
                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <Heading>{{ employee.full_name }}</Heading>
                            <div class="flex items-center gap-2">
                                <Badge :class="getStatusBadgeClass(employee.employment_status)">
                                    {{ t(`employees.status_${employee.employment_status}`) }}
                                </Badge>
                                <span class="text-sm font-mono text-muted-foreground">{{ employee.employee_id }}</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <Button variant="outline" asChild>
                                <Link :href="route('employees.edit', employee.id)">
                                    <Icon name="SquarePen" class="mr-2 h-4 w-4" />
                                    {{ t('employees.edit') }}
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Employee Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>{{ t('employees.basic_information') }}</CardTitle>
                            </CardHeader>
                            <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">{{ t('employees.employee_id') }}</Label>
                                    <p class="text-sm font-mono">{{ employee.employee_id }}</p>
                                </div>
                                
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">{{ t('employees.full_name') }}</Label>
                                    <p class="text-sm">{{ employee.full_name }}</p>
                                </div>
                                
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">{{ t('employees.email') }}</Label>
                                    <p class="text-sm">{{ employee.email }}</p>
                                </div>
                                
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">{{ t('employees.employment_status') }}</Label>
                                    <Badge :class="getStatusBadgeClass(employee.employment_status)" class="text-xs">
                                        {{ t(`employees.status_${employee.employment_status}`) }}
                                    </Badge>
                                </div>
                                
                                <div v-if="employee.phone">
                                    <Label class="text-sm font-medium text-muted-foreground">{{ t('employees.phone') }}</Label>
                                    <p class="text-sm">{{ employee.phone }}</p>
                                </div>
                                
                                <div v-if="employee.mobile">
                                    <Label class="text-sm font-medium text-muted-foreground">{{ t('employees.mobile') }}</Label>
                                    <p class="text-sm">{{ employee.mobile }}</p>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>{{ t('employees.job_information') }}</CardTitle>
                            </CardHeader>
                            <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div v-if="employee.job_title">
                                    <Label class="text-sm font-medium text-muted-foreground">{{ t('employees.job_title') }}</Label>
                                    <p class="text-sm">{{ employee.job_title }}</p>
                                </div>
                                
                                <div v-if="employee.department">
                                    <Label class="text-sm font-medium text-muted-foreground">{{ t('employees.department') }}</Label>
                                    <p class="text-sm">{{ employee.department }}</p>
                                </div>
                                
                                <div v-if="employee.manager">
                                    <Label class="text-sm font-medium text-muted-foreground">{{ t('employees.manager') }}</Label>
                                    <p class="text-sm">{{ employee.manager }}</p>
                                </div>
                                
                                <div v-if="employee.location">
                                    <Label class="text-sm font-medium text-muted-foreground">{{ t('employees.location') }}</Label>
                                    <p class="text-sm">{{ employee.location.display_name || `${employee.location.code}: ${employee.location.name}` }}</p>
                                </div>
                                
                                <div v-if="employee.hire_date">
                                    <Label class="text-sm font-medium text-muted-foreground">{{ t('employees.hire_date') }}</Label>
                                    <p class="text-sm">{{ new Date(employee.hire_date).toLocaleDateString() }}</p>
                                </div>
                                
                                <div v-if="employee.termination_date">
                                    <Label class="text-sm font-medium text-muted-foreground">{{ t('employees.termination_date') }}</Label>
                                    <p class="text-sm">{{ new Date(employee.termination_date).toLocaleDateString() }}</p>
                                </div>
                            </CardContent>
                        </Card>

                        <Card v-if="employee.notes">
                            <CardHeader>
                                <CardTitle>{{ t('employees.additional_information') }}</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div>
                                    <Label class="text-sm font-medium text-muted-foreground">{{ t('employees.notes') }}</Label>
                                    <p class="text-sm mt-1 whitespace-pre-wrap">{{ employee.notes }}</p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Assets -->
                        <Card v-if="employee.assets && employee.assets.length > 0">
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <Icon name="Package" class="h-5 w-5" />
                                    {{ t('employees.assets_count') }} ({{ employee.assets.length }})
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-3">
                                    <div 
                                        v-for="asset in employee.assets" 
                                        :key="asset.id"
                                        class="flex items-center justify-between p-3 border rounded-lg"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center">
                                                <Icon name="Package" class="h-4 w-4" />
                                            </div>
                                            <div>
                                                <p class="font-medium">{{ asset.name }}</p>
                                                <p class="text-sm text-muted-foreground">{{ asset.asset_tag }}</p>
                                            </div>
                                        </div>
                                        <Badge :class="asset.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                                            {{ asset.status }}
                                        </Badge>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Tickets -->
                        <Card v-if="employee.reported_tickets && employee.reported_tickets.length > 0">
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <Icon name="Activity" class="h-5 w-5" />
                                    {{ t('employees.tickets_count') }} ({{ employee.reported_tickets.length }})
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-3">
                                    <div 
                                        v-for="ticket in employee.reported_tickets" 
                                        :key="ticket.id"
                                        class="flex items-center justify-between p-3 border rounded-lg"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center">
                                                <Icon name="Activity" class="h-4 w-4" />
                                            </div>
                                            <div>
                                                <p class="font-medium">{{ ticket.title }}</p>
                                                <p class="text-sm text-muted-foreground">{{ ticket.ticket_number }}</p>
                                            </div>
                                        </div>
                                        <Badge :class="getTicketStatusBadgeClass(ticket.status)">
                                            {{ ticket.status }}
                                        </Badge>
                                    </div>
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
                                        <span class="text-sm">{{ t('employees.assets_count') }}</span>
                                    </div>
                                    <span class="font-medium">{{ employee.assets_count || 0 }}</span>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <Icon name="Activity" class="h-4 w-4 text-muted-foreground" />
                                        <span class="text-sm">{{ t('employees.tickets_count') }}</span>
                                    </div>
                                    <span class="font-medium">{{ employee.reported_tickets_count || 0 }}</span>
                                </div>
                            </CardContent>
                        </Card>
                        
                        <Card>
                            <CardHeader>
                                <CardTitle>{{ t('common.created_at') }}</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p class="text-sm text-muted-foreground">
                                    {{ new Date(employee.created_at).toLocaleDateString() }}
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
import type { Employee } from '@/types';
import type { BreadcrumbItem } from '@/types';

interface Props {
    employee: Employee;
}

const props = defineProps<Props>();
const { t } = useI18n();

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
        title: props.employee.full_name || `${props.employee.first_name} ${props.employee.last_name}`,
        href: `/employees/${props.employee.id}`,
    },
]);

const getStatusBadgeClass = (status: string): string => {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-800';
        case 'inactive':
            return 'bg-yellow-100 text-yellow-800';
        case 'terminated':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const getTicketStatusBadgeClass = (status: string): string => {
    switch (status) {
        case 'open':
            return 'bg-blue-100 text-blue-800';
        case 'in_progress':
            return 'bg-yellow-100 text-yellow-800';
        case 'resolved':
            return 'bg-green-100 text-green-800';
        case 'closed':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};
</script> 