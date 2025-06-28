import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: any;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    locale: string;
    translations: Record<string, any>;
};

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    language?: string;
    team_id?: number;
    joined_team_at?: string;
    team?: Team;
    roles?: Role[];
    permissions?: Permission[];
    created_at: string;
    updated_at: string;
}

export interface Team {
    id: number;
    name: string;
    slug: string;
    description?: string;
    logo?: string;
    website?: string;
    settings?: Record<string, any>;
    is_active: boolean;
    owner_id: number;
    trial_ends_at?: string;
    subscription_status: 'trial' | 'active' | 'past_due' | 'canceled';
    created_at: string;
    updated_at: string;
    owner?: User;
    users?: User[];
    users_count?: number;
}

export interface Company {
    id: number;
    name_en: string;
    name_ar: string;
    slug: string;
    company_email: string;
    description_en?: string;
    description_ar?: string;
    logo?: string;
    website?: string;
    settings?: Record<string, any>;
    is_active: boolean;
    owner_id: number;
    created_at: string;
    updated_at: string;
    owner?: User;
}

export interface Role {
    id: number;
    name: string;
    guard_name: string;
    team_id?: number;
    created_at: string;
    updated_at: string;
    permissions?: Permission[];
}

export interface Permission {
    id: number;
    name: string;
    guard_name: string;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;
