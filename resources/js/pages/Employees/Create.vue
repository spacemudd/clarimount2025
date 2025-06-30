<template>
    <AppLayout>
        <div class="container max-w-4xl mx-auto py-8">
            <div class="space-y-6">
                <div class="space-y-2">
                    <Breadcrumbs :items="breadcrumbs" />
                    <Heading>{{ t('employees.create_employee') }}</Heading>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('employees.employee_details') }}</CardTitle>
                        <CardDescription>
                            {{ t('employees.employee_details_description') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Basic Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium">{{ t('employees.basic_information') }}</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <Label for="employee_id" class="mb-2">{{ t('employees.employee_id') }}</Label>
                                        <Input
                                            id="employee_id"
                                            v-model="form.employee_id"
                                            type="text"
                                            :placeholder="t('employees.employee_id_placeholder')"
                                            :disabled="form.processing"
                                        />
                                        <InputError :message="form.errors.employee_id" />
                                    </div>

                                    <div>
                                        <Label for="first_name" class="mb-2">{{ t('employees.first_name') }}</Label>
                                        <Input
                                            id="first_name"
                                            v-model="form.first_name"
                                            type="text"
                                            :placeholder="t('employees.first_name_placeholder')"
                                            required
                                            :disabled="form.processing"
                                        />
                                        <InputError :message="form.errors.first_name" />
                                    </div>

                                    <div>
                                        <Label for="last_name" class="mb-2">{{ t('employees.last_name') }}</Label>
                                        <Input
                                            id="last_name"
                                            v-model="form.last_name"
                                            type="text"
                                            :placeholder="t('employees.last_name_placeholder')"
                                            required
                                            :disabled="form.processing"
                                        />
                                        <InputError :message="form.errors.last_name" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <Label for="email" class="mb-2">{{ t('employees.email') }}</Label>
                                        <Input
                                            id="email"
                                            v-model="form.email"
                                            type="email"
                                            :placeholder="t('employees.email_placeholder')"
                                            required
                                            :disabled="form.processing"
                                        />
                                        <InputError :message="form.errors.email" />
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium">{{ t('employees.contact_information') }}</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <Label for="phone" class="mb-2">{{ t('employees.phone') }}</Label>
                                        <Input
                                            id="phone"
                                            v-model="form.phone"
                                            type="text"
                                            :placeholder="t('employees.phone_placeholder')"
                                            :disabled="form.processing"
                                        />
                                        <InputError :message="form.errors.phone" />
                                    </div>

                                    <div>
                                        <Label for="mobile" class="mb-2">{{ t('employees.mobile') }}</Label>
                                        <Input
                                            id="mobile"
                                            v-model="form.mobile"
                                            type="text"
                                            :placeholder="t('employees.mobile_placeholder')"
                                            :disabled="form.processing"
                                        />
                                        <InputError :message="form.errors.mobile" />
                                    </div>
                                </div>
                            </div>

                            <!-- Job Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium">{{ t('employees.job_information') }}</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <Label for="department" class="mb-2">{{ t('employees.department') }}</Label>
                                        <Input
                                            id="department"
                                            v-model="form.department"
                                            type="text"
                                            :placeholder="t('employees.department_placeholder')"
                                            :disabled="form.processing"
                                        />
                                        <InputError :message="form.errors.department" />
                                    </div>

                                    <div>
                                        <Label for="job_title" class="mb-2">{{ t('employees.job_title') }}</Label>
                                        <Input
                                            id="job_title"
                                            v-model="form.job_title"
                                            type="text"
                                            :placeholder="t('employees.job_title_placeholder')"
                                            :disabled="form.processing"
                                        />
                                        <InputError :message="form.errors.job_title" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <Label for="manager" class="mb-2">{{ t('employees.manager') }}</Label>
                                        <Input
                                            id="manager"
                                            v-model="form.manager"
                                            type="text"
                                            :placeholder="t('employees.manager_placeholder')"
                                            :disabled="form.processing"
                                        />
                                        <InputError :message="form.errors.manager" />
                                    </div>

                                    <div>
                                        <Label for="hire_date" class="mb-2">{{ t('employees.hire_date') }}</Label>
                                        <Input
                                            id="hire_date"
                                            v-model="form.hire_date"
                                            type="date"
                                            :disabled="form.processing"
                                        />
                                        <InputError :message="form.errors.hire_date" />
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-medium">{{ t('employees.additional_information') }}</h3>
                                
                                <div>
                                    <Label for="notes" class="mb-2">{{ t('employees.notes') }}</Label>
                                    <textarea
                                        id="notes"
                                        v-model="form.notes"
                                        :placeholder="t('employees.notes_placeholder')"
                                        :disabled="form.processing"
                                        rows="4"
                                        class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    ></textarea>
                                    <InputError :message="form.errors.notes" />
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-6 border-t">
                                <Button variant="outline" asChild>
                                    <Link :href="route('employees.index')">
                                        {{ t('common.cancel') }}
                                    </Link>
                                </Button>
                                <Button type="submit" :disabled="form.processing">
                                    <Icon v-if="form.processing" name="LoaderCircle" class="mr-2 h-4 w-4 animate-spin" />
                                    {{ form.processing ? t('common.saving') : t('employees.create_employee') }}
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
import type { Company } from '@/types';
import type { BreadcrumbItem } from '@/types';

interface Props {
    company: Company;
}

const props = defineProps<Props>();
const { t } = useI18n();

const form = useForm({
    employee_id: '',
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    mobile: '',
    department: '',
    job_title: '',
    manager: '',
    hire_date: '',
    notes: '',
});

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
        title: t('employees.create_employee'),
        href: '/employees/create',
    },
]);

const submit = () => {
    form.post(route('employees.store'));
};
</script> 