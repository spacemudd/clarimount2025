<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Building, MapPin, Users, Package } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const { t } = useI18n();

const mainNavItems = computed((): NavItem[] => [
    {
        title: t('nav.dashboard'),
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: t('nav.companies'),
        href: '/companies',
        icon: Building,
    },
    {
        title: t('nav.locations'),
        href: '/locations',
        icon: MapPin,
    },
    {
        title: t('nav.employees'),
        href: '/employees',
        icon: Users,
    },
    {
        title: t('nav.asset_categories'),
        href: '/asset-categories',
        icon: Package,
    },
]);

const footerNavItems = computed((): NavItem[] => [
    {
        title: t('nav.githubRepo'),
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: t('nav.documentation'),
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
]);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" asChild>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
