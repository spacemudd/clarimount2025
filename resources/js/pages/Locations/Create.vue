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
    companies: Company[];
    currentCompany?: Company;
}

const props = defineProps<Props>();

const form = useForm({
    company_id: props.currentCompany?.id || (props.companies.length === 1 ? props.companies[0].id : ''),
    name: '',
    building: '',
    office_number: '',
    address: '',
    city: '',
    state: '',
    postal_code: '',
    country: '',
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
                        <!-- Company Selection -->
                        <div>
                            <Label for="company_id">{{ t('locations.company') }} *</Label>
                            <select
                                id="company_id"
                                v-model="form.company_id"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                :class="{ 'border-red-500': form.errors.company_id }"
                                :disabled="form.processing"
                                required
                            >
                                <option value="">{{ t('locations.select_company') }}</option>
                                <option v-for="company in companies" :key="company.id" :value="company.id">
                                    {{ company.name_en }} {{ company.name_ar ? `(${company.name_ar})` : '' }}
                                </option>
                            </select>
                            <InputError :message="form.errors.company_id" />
                        </div>

                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <Label for="name">{{ t('locations.name') }} *</Label>
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

                        <!-- Address Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium">{{ t('locations.address_information') }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <Label for="address">{{ t('locations.address') }}</Label>
                                    <Input
                                        id="address"
                                        v-model="form.address"
                                        type="text"
                                        :placeholder="t('locations.address_placeholder')"
                                        :disabled="form.processing"
                                    />
                                    <InputError :message="form.errors.address" />
                                </div>

                                <div>
                                    <Label for="city">{{ t('locations.city') }}</Label>
                                    <Input
                                        id="city"
                                        v-model="form.city"
                                        type="text"
                                        :placeholder="t('locations.city_placeholder')"
                                        :disabled="form.processing"
                                    />
                                    <InputError :message="form.errors.city" />
                                </div>

                                <div>
                                    <Label for="state">{{ t('locations.state') }}</Label>
                                    <Input
                                        id="state"
                                        v-model="form.state"
                                        type="text"
                                        :placeholder="t('locations.state_placeholder')"
                                        :disabled="form.processing"
                                    />
                                    <InputError :message="form.errors.state" />
                                </div>

                                <div>
                                    <Label for="postal_code">{{ t('locations.postal_code') }}</Label>
                                    <Input
                                        id="postal_code"
                                        v-model="form.postal_code"
                                        type="text"
                                        :placeholder="t('locations.postal_code_placeholder')"
                                        :disabled="form.processing"
                                    />
                                    <InputError :message="form.errors.postal_code" />
                                </div>

                                <div>
                                    <Label for="country">{{ t('locations.country') }}</Label>
                                    <Input
                                        id="country"
                                        v-model="form.country"
                                        type="text"
                                        :placeholder="t('locations.country_placeholder')"
                                        :disabled="form.processing"
                                    />
                                    <InputError :message="form.errors.country" />
                                </div>
                            </div>
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