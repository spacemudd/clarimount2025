<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

import InputError from '@/components/InputError.vue';
import { type BreadcrumbItem, type User } from '@/types';

interface Props {
    users: User[];
}

const props = defineProps<Props>();
const { t } = useI18n();

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
        title: 'Create Team',
        href: '/admin/teams/create',
    },
]);

const form = useForm({
    name: '',
    description: '',
    owner_id: '',
    subscription_status: 'trial',
    trial_days: 14,
    slug: '',
});

const submit = () => {
    form.post('/admin/teams');
};
</script>

<template>
    <Head title="Admin - Create Team" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Create New Team</h1>
                    <p class="text-muted-foreground">
                        Create a new team and assign an owner
                    </p>
                </div>
            </div>

            <!-- Form -->
            <div class="max-w-2xl">
                <Card>
                    <CardHeader>
                        <CardTitle>Team Information</CardTitle>
                        <CardDescription>
                            Fill in the team details and select an owner
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Team Name -->
                            <div class="space-y-2">
                                <Label for="name">Team Name *</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Enter team name"
                                    required
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <!-- Description -->
                            <div class="space-y-2">
                                <Label for="description">Description</Label>
                                <Input
                                    id="description"
                                    v-model="form.description"
                                    type="text"
                                    placeholder="Optional team description"
                                />
                                <InputError :message="form.errors.description" />
                            </div>

                            <!-- Slug -->
                            <div class="space-y-2">
                                <Label for="slug">Slug (Optional)</Label>
                                <Input
                                    id="slug"
                                    v-model="form.slug"
                                    type="text"
                                    placeholder="team-slug (auto-generated if empty)"
                                />
                                <InputError :message="form.errors.slug" />
                                <p class="text-sm text-muted-foreground">
                                    Leave empty to auto-generate from team name
                                </p>
                            </div>

                            <!-- Team Owner -->
                            <div class="space-y-2">
                                <Label for="owner_id">Team Owner *</Label>
                                <select
                                    id="owner_id"
                                    v-model="form.owner_id"
                                    class="w-full px-3 py-2 text-sm bg-background border border-input rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                                    required
                                >
                                    <option value="">Select team owner</option>
                                    <option
                                        v-for="user in props.users"
                                        :key="user.id"
                                        :value="user.id.toString()"
                                    >
                                        {{ user.name }} ({{ user.email }})
                                    </option>
                                </select>
                                <InputError :message="form.errors.owner_id" />
                                <p class="text-sm text-muted-foreground">
                                    Only users without existing teams are shown
                                </p>
                            </div>

                            <!-- Subscription Status -->
                            <div class="space-y-2">
                                <Label for="subscription_status">Subscription Status *</Label>
                                <select
                                    id="subscription_status"
                                    v-model="form.subscription_status"
                                    class="w-full px-3 py-2 text-sm bg-background border border-input rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                                    required
                                >
                                    <option value="trial">Trial</option>
                                    <option value="active">Active (Paid)</option>
                                    <option value="past_due">Past Due</option>
                                    <option value="canceled">Canceled</option>
                                </select>
                                <InputError :message="form.errors.subscription_status" />
                            </div>

                            <!-- Trial Days (only show if trial) -->
                            <div v-if="form.subscription_status === 'trial'" class="space-y-2">
                                <Label for="trial_days">Trial Days</Label>
                                <Input
                                    id="trial_days"
                                    v-model.number="form.trial_days"
                                    type="number"
                                    min="0"
                                    max="365"
                                    placeholder="14"
                                />
                                <InputError :message="form.errors.trial_days" />
                                <p class="text-sm text-muted-foreground">
                                    Number of trial days (default: 14)
                                </p>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="flex justify-end gap-4 pt-4">
                                <Button
                                    type="button"
                                    variant="outline"
                                    as-child
                                >
                                    <a href="/admin/teams">Cancel</a>
                                </Button>
                                <Button
                                    type="submit"
                                    :disabled="form.processing"
                                >
                                    {{ form.processing ? 'Creating...' : 'Create Team' }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template> 