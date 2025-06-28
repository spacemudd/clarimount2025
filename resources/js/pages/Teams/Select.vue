<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Plus, Users, Building } from 'lucide-vue-next';

import AuthLayout from '@/layouts/AuthLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { type Team } from '@/types';

interface Props {
    ownedTeams: Team[];
    memberTeams: Team[];
}

const props = defineProps<Props>();
const { t } = useI18n();

const hasTeams = computed(() => {
    return props.ownedTeams.length > 0 || props.memberTeams.length > 0;
});
</script>

<template>
    <Head title="Select Team" />

    <AuthLayout>
        <div class="container mx-auto max-w-4xl px-4 py-8">
            <div class="text-center mb-8">
                <Building class="mx-auto h-12 w-12 text-primary mb-4" />
                <h1 class="text-3xl font-bold tracking-tight">{{ t('teams.select_team') }}</h1>
                <p class="text-muted-foreground mt-2">
                    {{ t('teams.select_team_description') }}
                </p>
            </div>

            <div class="space-y-6">
                <!-- Owned Teams -->
                <div v-if="props.ownedTeams.length > 0">
                    <h2 class="text-xl font-semibold mb-4">{{ t('teams.owned_teams') }}</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        <Card v-for="team in props.ownedTeams" :key="team.id" class="cursor-pointer hover:bg-accent/50 transition-colors">
                            <CardHeader>
                                <div class="flex items-center justify-between">
                                    <CardTitle class="flex items-center gap-2">
                                        <Building class="h-5 w-5" />
                                        {{ team.name }}
                                    </CardTitle>
                                    <Badge variant="secondary">{{ t('teams.owner') }}</Badge>
                                </div>
                                <CardDescription v-if="team.description">
                                    {{ team.description }}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                        <Users class="h-4 w-4" />
                                        {{ team.users_count || 0 }} {{ t('teams.members') }}
                                    </div>
                                    <form :action="route('teams.switch', team.id)" method="POST" class="inline">
                                        <Button type="submit" size="sm">
                                            {{ t('teams.select') }}
                                        </Button>
                                    </form>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Member Teams -->
                <div v-if="props.memberTeams.length > 0">
                    <h2 class="text-xl font-semibold mb-4">{{ t('teams.member_teams') }}</h2>
                    <div class="grid gap-4 md:grid-cols-2">
                        <Card v-for="team in props.memberTeams" :key="team.id" class="cursor-pointer hover:bg-accent/50 transition-colors">
                            <CardHeader>
                                <div class="flex items-center justify-between">
                                    <CardTitle class="flex items-center gap-2">
                                        <Building class="h-5 w-5" />
                                        {{ team.name }}
                                    </CardTitle>
                                    <Badge variant="outline">{{ t('teams.member') }}</Badge>
                                </div>
                                <CardDescription v-if="team.description">
                                    {{ team.description }}
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                        <Users class="h-4 w-4" />
                                        {{ team.users_count || 0 }} {{ t('teams.members') }}
                                    </div>
                                    <form :action="route('teams.switch', team.id)" method="POST" class="inline">
                                        <Button type="submit" size="sm">
                                            {{ t('teams.select') }}
                                        </Button>
                                    </form>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- No Teams / Create Team -->
                <div v-if="!hasTeams || true" class="text-center py-8">
                    <div class="border-2 border-dashed border-muted-foreground/25 rounded-lg p-8">
                        <Plus class="mx-auto h-12 w-12 text-muted-foreground mb-4" />
                        <h3 class="text-lg font-semibold mb-2">{{ t('teams.no_teams') }}</h3>
                        <p class="text-muted-foreground mb-4">
                            {{ t('teams.create_first_team') }}
                        </p>
                        <Button as-child size="lg">
                            <Link :href="route('teams.create')">
                                <Plus class="mr-2 h-4 w-4" />
                                {{ t('teams.create_team') }}
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AuthLayout>
</template> 