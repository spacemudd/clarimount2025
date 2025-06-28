<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Building, ArrowLeft } from 'lucide-vue-next';

import AuthLayout from '@/layouts/AuthLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import InputError from '@/components/InputError.vue';

const { t } = useI18n();

const form = useForm({
    name: '',
    description: '',
    slug: '',
});

const submit = () => {
    form.post(route('teams.store'));
};
</script>

<template>
    <Head title="Create Team" />

    <AuthLayout>
        <div class="container mx-auto max-w-2xl px-4 py-8">
            <div class="flex items-center gap-4 mb-8">
                <Button 
                    variant="ghost" 
                    size="sm" 
                    @click="$inertia.visit(route('teams.select'))"
                >
                    <ArrowLeft class="h-4 w-4 mr-2" />
                    {{ t('common.back') }}
                </Button>
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ t('teams.create_team') }}</h1>
                    <p class="text-muted-foreground">
                        {{ t('teams.create_team_description') }}
                    </p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Building class="h-5 w-5" />
                        {{ t('teams.team_details') }}
                    </CardTitle>
                    <CardDescription>
                        {{ t('teams.team_details_description') }}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <Label for="name">{{ t('teams.team_name') }}</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                :placeholder="t('teams.team_name_placeholder')"
                                required
                                class="w-full"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="space-y-2">
                            <Label for="slug">{{ t('teams.team_slug') }}</Label>
                            <Input
                                id="slug"
                                v-model="form.slug"
                                :placeholder="t('teams.team_slug_placeholder')"
                                class="w-full"
                            />
                            <p class="text-sm text-muted-foreground">
                                {{ t('teams.team_slug_description') }}
                            </p>
                            <InputError :message="form.errors.slug" />
                        </div>

                        <div class="space-y-2">
                            <Label for="description">{{ t('teams.team_description') }}</Label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                :placeholder="t('teams.team_description_placeholder')"
                                rows="3"
                                class="w-full min-h-[80px] px-3 py-2 text-sm bg-background border border-input rounded-md shadow-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent disabled:cursor-not-allowed disabled:opacity-50"
                            />
                            <InputError :message="form.errors.description" />
                        </div>

                        <div class="flex items-center justify-between pt-6">
                            <Button 
                                type="button" 
                                variant="outline" 
                                @click="$inertia.visit(route('teams.select'))"
                            >
                                {{ t('common.cancel') }}
                            </Button>
                            <Button 
                                type="submit" 
                                :disabled="form.processing"
                            >
                                {{ form.processing ? t('common.creating') : t('teams.create_team') }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AuthLayout>
</template> 