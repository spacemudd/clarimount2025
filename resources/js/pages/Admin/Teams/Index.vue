<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Search, Plus, Filter, Eye, Pause, Play, Trash2, MoreHorizontal } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
    DropdownMenu, 
    DropdownMenuContent, 
    DropdownMenuItem, 
    DropdownMenuLabel, 
    DropdownMenuSeparator, 
    DropdownMenuTrigger 
} from '@/components/ui/dropdown-menu';

import { type BreadcrumbItem, type Team } from '@/types';

interface Props {
    teams: {
        data: Team[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    stats: {
        total_teams: number;
        active_teams: number;
        suspended_teams: number;
        trial_teams: number;
        active_subscriptions: number;
    };
    filters: {
        search?: string;
        status?: string;
        subscription?: string;
    };
}

const props = defineProps<Props>();
const { t } = useI18n();

const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const subscriptionFilter = ref(props.filters.subscription || '');

const breadcrumbs = computed((): BreadcrumbItem[] => [
    {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
    },
    {
        title: 'Teams',
        href: '/admin/teams',
    },
]);

const subscriptionStatusColor = (status: string) => {
    switch (status) {
        case 'active': return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'trial': return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
        case 'past_due': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
        case 'canceled': return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default: return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};

const teamStatusColor = (isActive: boolean) => {
    return isActive 
        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
};

const search = () => {
    router.get('/admin/teams', {
        search: searchQuery.value,
        status: statusFilter.value,
        subscription: subscriptionFilter.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    statusFilter.value = '';
    subscriptionFilter.value = '';
    router.get('/admin/teams', {}, {
        preserveState: true,
        replace: true,
    });
};

const suspendTeam = (teamId: number) => {
    if (confirm('Are you sure you want to suspend this team?')) {
        router.post(`/admin/teams/${teamId}/suspend`, {}, {
            preserveScroll: true,
        });
    }
};

const activateTeam = (teamId: number) => {
    router.post(`/admin/teams/${teamId}/activate`, {}, {
        preserveScroll: true,
    });
};

const deleteTeam = (teamId: number) => {
    if (confirm('Are you sure you want to delete this team? This action cannot be undone.')) {
        router.delete(`/admin/teams/${teamId}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Admin - Teams" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Teams Management</h1>
                    <p class="text-muted-foreground">
                        Manage all teams across the platform
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button @click="clearFilters" variant="outline">
                        Clear Filters
                    </Button>
                    <Button as-child>
                        <Link href="/admin/teams/create">
                            <Plus class="mr-2 h-4 w-4" />
                            Create Team
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-5">
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Total Teams</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.stats.total_teams }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Active</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-green-600">{{ props.stats.active_teams }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Suspended</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-red-600">{{ props.stats.suspended_teams }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">On Trial</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-blue-600">{{ props.stats.trial_teams }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Paid</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-purple-600">{{ props.stats.active_subscriptions }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg">Filters</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-4">
                        <div class="relative">
                            <Search class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                            <Input
                                v-model="searchQuery"
                                placeholder="Search teams..."
                                class="pl-10"
                                @keyup.enter="search"
                            />
                        </div>
                        <select
                            v-model="statusFilter"
                            class="w-full px-3 py-2 text-sm bg-background border border-input rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                        >
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                        </select>
                        <select
                            v-model="subscriptionFilter"
                            class="w-full px-3 py-2 text-sm bg-background border border-input rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                        >
                            <option value="">All Subscriptions</option>
                            <option value="trial">Trial</option>
                            <option value="active">Active</option>
                            <option value="past_due">Past Due</option>
                            <option value="canceled">Canceled</option>
                        </select>
                        <Button @click="search" class="w-full">
                            <Filter class="mr-2 h-4 w-4" />
                            Apply Filters
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Teams Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Teams ({{ props.teams.total }})</CardTitle>
                    <CardDescription>
                        Showing {{ props.teams.data.length }} of {{ props.teams.total }} teams
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left p-4 font-medium">Team</th>
                                    <th class="text-left p-4 font-medium">Owner</th>
                                    <th class="text-left p-4 font-medium">Users</th>
                                    <th class="text-left p-4 font-medium">Status</th>
                                    <th class="text-left p-4 font-medium">Subscription</th>
                                    <th class="text-left p-4 font-medium">Created</th>
                                    <th class="text-right p-4 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="team in props.teams.data" :key="team.id" class="border-b hover:bg-muted/50">
                                    <td class="p-4">
                                        <div>
                                            <div class="font-medium">{{ team.name }}</div>
                                            <div class="text-sm text-muted-foreground">{{ team.slug }}</div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div>
                                            <div class="font-medium">{{ team.owner?.name }}</div>
                                            <div class="text-sm text-muted-foreground">{{ team.owner?.email }}</div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <Badge variant="secondary">{{ team.users_count || 0 }}</Badge>
                                    </td>
                                    <td class="p-4">
                                        <Badge :class="teamStatusColor(team.is_active)">
                                            {{ team.is_active ? 'Active' : 'Suspended' }}
                                        </Badge>
                                    </td>
                                    <td class="p-4">
                                        <Badge :class="subscriptionStatusColor(team.subscription_status)">
                                            {{ team.subscription_status }}
                                        </Badge>
                                    </td>
                                    <td class="p-4 text-sm text-muted-foreground">
                                        {{ new Date(team.created_at).toLocaleDateString() }}
                                    </td>
                                    <td class="p-4 text-right">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="sm">
                                                    <MoreHorizontal class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                                <DropdownMenuItem as-child>
                                                    <Link :href="`/admin/teams/${team.id}`">
                                                        <Eye class="mr-2 h-4 w-4" />
                                                        View Details
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem 
                                                    v-if="team.is_active"
                                                    @click="suspendTeam(team.id)"
                                                    class="text-orange-600"
                                                >
                                                    <Pause class="mr-2 h-4 w-4" />
                                                    Suspend Team
                                                </DropdownMenuItem>
                                                <DropdownMenuItem 
                                                    v-else
                                                    @click="activateTeam(team.id)"
                                                    class="text-green-600"
                                                >
                                                    <Play class="mr-2 h-4 w-4" />
                                                    Activate Team
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem 
                                                    @click="deleteTeam(team.id)"
                                                    class="text-red-600"
                                                >
                                                    <Trash2 class="mr-2 h-4 w-4" />
                                                    Delete Team
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination would go here -->
                    <div v-if="props.teams.total === 0" class="text-center py-8 text-muted-foreground">
                        No teams found matching your criteria.
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template> 