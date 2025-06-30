<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import Heading from '@/components/Heading.vue';
import { useI18n } from 'vue-i18n';
import { computed } from 'vue';
import type { Company, BreadcrumbItem } from '@/types';

const { t } = useI18n();

interface Props {
    company: Company;
}

defineProps<Props>();

const form = useForm({
    name: '',
    building: '',
    office_number: '',
});

const breadcrumbs = computed((): BreadcrumbItem[] => [
    {
        title: t('nav.dashboard'),
        href: '/dashboard',
    },
    {
        title: t('locations.title'),
        href: '/locations',
    },
    {
        title: t('locations.create_location'),
        href: '/locations/create',
    },
]);

const submit = () => {
    form.post(route('locations.store'));
};
</script>

<template>
    <Head :title="t('locations.create_location')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <Heading :title="t('locations.create_location')" :description="t('locations.create_location_description')" />

            <Card>
                <CardHeader>
                    <CardTitle>{{ t('locations.location_details') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <Label for="name">{{ t('locations.name') }}</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    :placeholder="t('locations.name_placeholder')"
                                    required
                                    :disabled="form.processing"
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div>
                                <Label for="code">{{ t('locations.code') }}</Label>
                                <Input
                                    id="code"
                                    type="text"
                                    :placeholder="t('locations.code_auto_generated')"
                                    disabled
                                    class="bg-muted"
                                />
                                <p class="text-sm text-muted-foreground mt-1">{{ t('locations.code_auto_generated') }}</p>
                            </div>

                            <div>
                                <Label for="building">{{ t('locations.building') }}</Label>
                                <Input
                                    id="building"
                                    v-model="form.building"
                                    type="text"
                                    :placeholder="t('locations.building_placeholder')"
                                    :disabled="form.processing"
                                />
                                <InputError :message="form.errors.building" />
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div>
                            <Label for="office_number">{{ t('locations.office_number') }}</Label>
                            <Input
                                id="office_number"
                                v-model="form.office_number"
                                type="text"
                                :placeholder="t('locations.office_number_placeholder')"
                                :disabled="form.processing"
                            />
                            <InputError :message="form.errors.office_number" />
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t">
                            <Button variant="outline" type="button" asChild>
                                <Link :href="route('locations.index')">
                                    {{ t('common.cancel') }}
                                </Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? t('common.saving') : t('common.save') }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template> 