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

export interface Location {
    id: number;
    name: string;
    code: string;
    building?: string;
    office_number?: string;
    company_id: number;
    is_active: boolean;
    settings?: Record<string, any>;
    created_at: string;
    updated_at: string;
    company?: Company;
    assets?: Asset[];
    assets_count?: number;
    full_name?: string;
    display_name?: string;
}

export interface Employee {
    id: number;
    employee_id?: string;
    first_name: string;
    last_name: string;
    email: string;
    phone?: string;
    mobile?: string;
    department?: string;
    job_title?: string;
    manager?: string;
    hire_date?: string;
    termination_date?: string;
    employment_status: 'active' | 'inactive' | 'terminated';
    company_id: number;
    notes?: string;
    settings?: Record<string, any>;
    created_at: string;
    updated_at: string;
    company?: Company;
    assets?: Asset[];
    asset_assignments?: AssetAssignment[];
    reported_tickets?: Ticket[];
    assets_count?: number;
    reported_tickets_count?: number;
    full_name?: string;
    display_name?: string;
}

export interface AssetCategory {
    id: number;
    name: string;
    code?: string;
    description?: string;
    icon?: string;
    color?: string;
    company_id: number;
    parent_id?: number;
    _lft?: number;
    _rgt?: number;
    depth?: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    company?: Company;
    parent?: AssetCategory;
    children?: AssetCategory[];
    ancestors?: AssetCategory[];
    assets?: Asset[];
    assets_count?: number;
}

export interface Asset {
    id: number;
    asset_tag: string;
    serial_number?: string;
    service_tag_number?: string;
    finance_tag_number?: string;
    asset_category_id: number;
    location_id: number;
    company_id: number;
    model_name?: string;
    model_number?: string;
    status: 'available' | 'assigned' | 'maintenance' | 'retired';
    notes?: string;
    created_at: string;
    updated_at: string;
    category?: AssetCategory;
    location?: Location;
    company?: Company;
    assignments?: AssetAssignment[];
    tickets?: Ticket[];
}

export interface AssetTemplate {
    id: number;
    name: string;
    manufacturer?: string;
    model_name?: string;
    model_number?: string;
    asset_category_id: number;
    company_id?: number;
    specifications?: Record<string, any>;
    default_notes?: string;
    image_path?: string;
    is_global: boolean;
    usage_count: number;
    created_by_user_id: number;
    created_at: string;
    updated_at: string;
    asset_category?: AssetCategory;
    company?: Company;
    created_by_user?: User;
    display_name?: string;
    formatted_specifications?: string;
}

export interface TicketCategory {
    id: number;
    name: string;
    code: string;
    description?: string;
    icon?: string;
    color?: string;
    company_id: number;
    is_active: boolean;
    default_priority: number;
    sla_hours?: number;
    settings?: Record<string, any>;
    created_at: string;
    updated_at: string;
    company?: Company;
    tickets?: Ticket[];
    priority_name?: string;
}

export interface Ticket {
    id: number;
    ticket_number: string;
    subject: string;
    description: string;
    ticket_category_id: number;
    company_id: number;
    reporter_id: number;
    assigned_to?: number;
    location_id?: number;
    asset_id?: number;
    status: 'open' | 'in_progress' | 'pending' | 'resolved' | 'closed';
    priority: 'low' | 'medium' | 'high' | 'critical';
    due_date?: string;
    resolved_at?: string;
    closed_at?: string;
    time_spent: number;
    resolution?: string;
    resolved_by?: number;
    custom_data?: Record<string, any>;
    created_at: string;
    updated_at: string;
    ticket_category?: TicketCategory;
    company?: Company;
    reporter?: Employee;
    assigned_user?: User;
    resolver?: User;
    location?: Location;
    asset?: Asset;
    comments?: TicketComment[];
    priority_color?: string;
    status_color?: string;
    time_spent_hours?: number;
}

export interface AssetAssignment {
    id: number;
    asset_id: number;
    employee_id: number;
    assigned_by: number;
    assigned_date: string;
    returned_date?: string;
    returned_by?: number;
    status: 'active' | 'returned' | 'lost' | 'damaged';
    assignment_notes?: string;
    return_notes?: string;
    condition_notes?: string;
    checklist_data?: Record<string, any>;
    created_at: string;
    updated_at: string;
    asset?: Asset;
    employee?: Employee;
    assigned_by_user?: User;
    returned_by_user?: User;
    duration_days?: number;
}

export interface TicketComment {
    id: number;
    ticket_id: number;
    user_id: number;
    comment: string;
    type: 'comment' | 'status_change' | 'assignment' | 'resolution' | 'internal';
    is_internal: boolean;
    time_spent: number;
    metadata?: Record<string, any>;
    created_at: string;
    updated_at: string;
    ticket?: Ticket;
    user?: User;
    time_spent_hours?: number;
    type_color?: string;
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
