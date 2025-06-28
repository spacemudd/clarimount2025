<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { computed } from 'vue';
import { Users, Building2, TrendingUp, AlertTriangle } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';

const { t } = useI18n();

const breadcrumbs = computed((): BreadcrumbItem[] => [
    {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
    },
]);

const quickActions = [
    {
        title: 'Manage Teams',
        description: 'View, create, and manage all teams',
        href: '/admin/teams',
        icon: Building2,
        color: 'bg-blue-500',
    },
    {
        title: 'System Analytics',
        description: 'View platform-wide statistics',
        href: '#',
        icon: TrendingUp,
        color: 'bg-green-500',
    },
    {
        title: 'User Management',
        description: 'Manage all platform users',
        href: '#',
        icon: Users,
        color: 'bg-purple-500',
    },
    {
        title: 'System Health',
        description: 'Monitor system status and alerts',
        href: '#',
        icon: AlertTriangle,
        color: 'bg-orange-500',
    },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Super Admin Dashboard</h1>
                    <p class="text-muted-foreground">
                        Manage teams, users, and platform-wide settings
                    </p>
                </div>
            </div>

            <!-- Quick Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Teams</CardTitle>
                        <Building2 class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">-</div>
                        <p class="text-xs text-muted-foreground">
                            +- from last month
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active Teams</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">-</div>
                        <p class="text-xs text-muted-foreground">
                            +- from last month
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Users</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">-</div>
                        <p class="text-xs text-muted-foreground">
                            +- from last month
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Active Subscriptions</CardTitle>
                        <AlertTriangle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">-</div>
                        <p class="text-xs text-muted-foreground">
                            +- from last month
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Actions -->
            <div>
                <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card v-for="action in quickActions" :key="action.title" class="hover:shadow-md transition-shadow cursor-pointer">
                        <CardHeader class="pb-3">
                            <div class="flex items-center gap-3">
                                <div :class="[action.color, 'p-2 rounded-lg text-white']">
                                    <component :is="action.icon" class="h-5 w-5" />
                                </div>
                                <div>
                                    <CardTitle class="text-base">{{ action.title }}</CardTitle>
                                    <CardDescription class="text-sm">{{ action.description }}</CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent class="pt-0">
                            <Button 
                                :as-child="!!action.href" 
                                variant="outline" 
                                size="sm"
                                :disabled="!action.href || action.href === '#'"
                                class="w-full"
                            >
                                <a v-if="action.href && action.href !== '#'" :href="action.href">
                                    Go to {{ action.title }}
                                </a>
                                <span v-else>
                                    Coming Soon
                                </span>
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Recent Activity -->
            <Card>
                <CardHeader>
                    <CardTitle>Recent Activity</CardTitle>
                    <CardDescription>
                        Latest actions across the platform
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="text-center py-8 text-muted-foreground">
                        Activity feed coming soon...
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template> 