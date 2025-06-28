<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Edit, Users, Calendar, Activity, Pause, Play, Trash2 } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

import InputError from '@/components/InputError.vue';
import { type BreadcrumbItem, type Team } from '@/types';

interface Props {
    team: Team & {
        owner: {
            id: number;
            name: string;
            email: string;
        };
        users: Array<{
            id: number;
            name: string;
            email: string;
            roles: Array<{
                id: number;
                name: string;
            }>;
        }>;
    };
    teamStats: {
        total_users: number;
        active_users: number;
        created_ago: string;
        trial_status: string;
        days_since_created: number;
    };
}

const props = defineProps<Props>();
const { t } = useI18n();

const isEditing = ref(false);

const breadcrumbs = computed((): BreadcrumbItem[] => [
    {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
    },
    {
        title: 'Teams',
        href: '/admin/teams',
    },
    {
        title: props.team.name,
        href: `/admin/teams/${props.team.id}`,
    },
]);

const form = useForm({
    name: props.team.name,
    description: props.team.description || '',
    subscription_status: props.team.subscription_status,
    trial_days: 14,
    is_active: props.team.is_active,
});

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

const updateTeam = () => {
    form.put(`/admin/teams/${props.team.id}`, {
        onSuccess: () => {
            isEditing.value = false;
        },
    });
};

const suspendTeam = () => {
    if (confirm('Are you sure you want to suspend this team?')) {
        router.post(`/admin/teams/${props.team.id}/suspend`);
    }
};

const activateTeam = () => {
    router.post(`/admin/teams/${props.team.id}/activate`);
};

const deleteTeam = () => {
    if (confirm('Are you sure you want to delete this team? This action cannot be undone.')) {
        router.delete(`/admin/teams/${props.team.id}`);
    }
};
</script>

<template>
    <Head :title="`Admin - ${team.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ team.name }}</h1>
                    <p class="text-muted-foreground">
                        {{ team.description || 'No description provided' }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button @click="isEditing = !isEditing" variant="outline">
                        <Edit class="mr-2 h-4 w-4" />
                        {{ isEditing ? 'Cancel' : 'Edit' }}
                    </Button>
                    <Button
                        v-if="team.is_active"
                        @click="suspendTeam"
                        variant="destructive"
                    >
                        <Pause class="mr-2 h-4 w-4" />
                        Suspend
                    </Button>
                    <Button
                        v-else
                        @click="activateTeam"
                        variant="default"
                    >
                        <Play class="mr-2 h-4 w-4" />
                        Activate
                    </Button>
                    <Button @click="deleteTeam" variant="destructive">
                        <Trash2 class="mr-2 h-4 w-4" />
                        Delete
                    </Button>
                </div>
            </div>

            <!-- Team Stats -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium flex items-center gap-2">
                            <Users class="h-4 w-4" />
                            Total Users
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ teamStats.total_users }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium flex items-center gap-2">
                            <Activity class="h-4 w-4" />
                            Active Users
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ teamStats.active_users }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium flex items-center gap-2">
                            <Calendar class="h-4 w-4" />
                            Created
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-sm font-medium">{{ teamStats.created_ago }}</div>
                        <div class="text-xs text-muted-foreground">{{ teamStats.days_since_created }} days ago</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Trial Status</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-sm font-medium">{{ teamStats.trial_status }}</div>
                        <div v-if="team.trial_ends_at" class="text-xs text-muted-foreground">
                            Ends: {{ new Date(team.trial_ends_at).toLocaleDateString() }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Team Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Team Information</CardTitle>
                        <CardDescription>Basic team details and settings</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form v-if="isEditing" @submit.prevent="updateTeam" class="space-y-4">
                            <div class="space-y-2">
                                <Label for="name">Team Name</Label>
                                <Input id="name" v-model="form.name" />
                                <InputError :message="form.errors.name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="description">Description</Label>
                                <Input id="description" v-model="form.description" />
                                <InputError :message="form.errors.description" />
                            </div>
                            <div class="space-y-2">
                                <Label for="subscription_status">Subscription Status</Label>
                                <select
                                    v-model="form.subscription_status"
                                    class="w-full px-3 py-2 text-sm bg-background border border-input rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                                >
                                    <option value="trial">Trial</option>
                                    <option value="active">Active</option>
                                    <option value="past_due">Past Due</option>
                                    <option value="canceled">Canceled</option>
                                </select>
                                <InputError :message="form.errors.subscription_status" />
                            </div>
                            <div v-if="form.subscription_status === 'trial'" class="space-y-2">
                                <Label for="trial_days">Extend Trial (Days)</Label>
                                <Input
                                    id="trial_days"
                                    v-model.number="form.trial_days"
                                    type="number"
                                    min="0"
                                    max="365"
                                />
                                <InputError :message="form.errors.trial_days" />
                            </div>
                            <div class="flex gap-2">
                                <Button type="submit" :disabled="form.processing">
                                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                                </Button>
                                <Button type="button" variant="outline" @click="isEditing = false">
                                    Cancel
                                </Button>
                            </div>
                        </form>
                        <div v-else class="space-y-4">
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Name</label>
                                <p class="font-medium">{{ team.name }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Slug</label>
                                <p class="font-mono text-sm">{{ team.slug }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Description</label>
                                <p>{{ team.description || 'No description provided' }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Status</label>
                                <div>
                                    <Badge :class="teamStatusColor(team.is_active)">
                                        {{ team.is_active ? 'Active' : 'Suspended' }}
                                    </Badge>
                                </div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Subscription</label>
                                <div>
                                    <Badge :class="subscriptionStatusColor(team.subscription_status)">
                                        {{ team.subscription_status }}
                                    </Badge>
                                </div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Owner</label>
                                <div>
                                    <p class="font-medium">{{ team.owner.name }}</p>
                                    <p class="text-sm text-muted-foreground">{{ team.owner.email }}</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Team Members -->
                <Card>
                    <CardHeader>
                        <CardTitle>Team Members ({{ team.users.length }})</CardTitle>
                        <CardDescription>Users belonging to this team</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="user in team.users" :key="user.id" class="flex items-start justify-between p-3 border rounded">
                                <div>
                                    <p class="font-medium">{{ user.name }}</p>
                                    <p class="text-sm text-muted-foreground">{{ user.email }}</p>
                                    <div class="flex gap-1 mt-1">
                                        <Badge 
                                            v-for="role in user.roles" 
                                            :key="role.id" 
                                            variant="secondary" 
                                            class="text-xs"
                                        >
                                            {{ role.name.replace(`-${team.id}`, '').replace('team-', '') }}
                                        </Badge>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1">
                                    <Badge v-if="user.id === team.owner_id" variant="default" class="text-xs">
                                        Owner
                                    </Badge>
                                </div>
                            </div>
                            <div v-if="team.users.length === 0" class="text-center py-4 text-muted-foreground">
                                No team members found
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template> 