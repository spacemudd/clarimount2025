<template>
    <AppLayout>
        <div class="container max-w-2xl mx-auto py-8">
            <div class="space-y-6">
                <div class="space-y-2">
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                    <Heading :title="t('locations.edit_location')" />
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('locations.location_details') }}</CardTitle>
                        <CardDescription>
                            {{ t('locations.location_details_description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
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
                                        :value="location.code"
                                        disabled
                                        class="bg-muted"
                                    />
                                    <p class="text-sm text-muted-foreground mt-1">{{ t('locations.code_auto_generated') }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                            </div>

                            <div class="flex items-center justify-between">
                                <Button variant="outline" asChild>
                                    <Link :href="route('locations.index')">
                                        {{ t('common.cancel') }}
                                    </Link>
                                </Button>
                                <Button type="submit" :disabled="form.processing">
                                    <Icon v-if="form.processing" name="LoaderCircle" class="mr-2 h-4 w-4 animate-spin" />
                                    {{ form.processing ? t('common.saving') : t('common.save') }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLayout from '@/layouts/AppLayout.vue';
import { 
    Card, 
    CardContent, 
    CardDescription, 
    CardHeader, 
    CardTitle 
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import Heading from '@/components/Heading.vue';
import Icon from '@/components/Icon.vue';
import type { Location } from '@/types';
import type { BreadcrumbItem } from '@/types';

interface Props {
    location: Location;
}

const props = defineProps<Props>();
const { t } = useI18n();

const form = useForm({
    name: props.location.name,
    building: props.location.building || '',
    office_number: props.location.office_number || '',
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
        title: props.location.name,
        href: `/locations/${props.location.id}`,
    },
    {
        title: t('locations.edit_location'),
        href: `/locations/${props.location.id}/edit`,
    },
]);

const submit = () => {
    form.put(route('locations.update', props.location.id));
};
</script> 