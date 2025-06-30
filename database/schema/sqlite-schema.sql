CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "password_reset_tokens"(
  "email" varchar not null,
  "token" varchar not null,
  "created_at" datetime,
  primary key("email")
);
CREATE TABLE IF NOT EXISTS "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE TABLE IF NOT EXISTS "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "jobs"(
  "id" integer primary key autoincrement not null,
  "queue" varchar not null,
  "payload" text not null,
  "attempts" integer not null,
  "reserved_at" integer,
  "available_at" integer not null,
  "created_at" integer not null
);
CREATE INDEX "jobs_queue_index" on "jobs"("queue");
CREATE TABLE IF NOT EXISTS "job_batches"(
  "id" varchar not null,
  "name" varchar not null,
  "total_jobs" integer not null,
  "pending_jobs" integer not null,
  "failed_jobs" integer not null,
  "failed_job_ids" text not null,
  "options" text,
  "cancelled_at" integer,
  "created_at" integer not null,
  "finished_at" integer,
  primary key("id")
);
CREATE TABLE IF NOT EXISTS "failed_jobs"(
  "id" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "connection" text not null,
  "queue" text not null,
  "payload" text not null,
  "exception" text not null,
  "failed_at" datetime not null default CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs"("uuid");
CREATE TABLE IF NOT EXISTS "permissions"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "guard_name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "permissions_name_guard_name_unique" on "permissions"(
  "name",
  "guard_name"
);
CREATE TABLE IF NOT EXISTS "roles"(
  "id" integer primary key autoincrement not null,
  "team_id" integer,
  "name" varchar not null,
  "guard_name" varchar not null,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "roles_team_foreign_key_index" on "roles"("team_id");
CREATE UNIQUE INDEX "roles_team_id_name_guard_name_unique" on "roles"(
  "team_id",
  "name",
  "guard_name"
);
CREATE TABLE IF NOT EXISTS "role_has_permissions"(
  "permission_id" integer not null,
  "role_id" integer not null,
  foreign key("permission_id") references "permissions"("id") on delete cascade,
  foreign key("role_id") references "roles"("id") on delete cascade,
  primary key("permission_id", "role_id")
);
CREATE TABLE IF NOT EXISTS "teams"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "slug" varchar not null,
  "description" text,
  "logo" varchar,
  "website" varchar,
  "settings" text,
  "is_active" tinyint(1) not null default '1',
  "owner_id" integer not null,
  "trial_ends_at" datetime,
  "subscription_status" varchar not null default 'trial',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("owner_id") references "users"("id") on delete cascade
);
CREATE INDEX "teams_is_active_subscription_status_index" on "teams"(
  "is_active",
  "subscription_status"
);
CREATE UNIQUE INDEX "teams_slug_unique" on "teams"("slug");
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "email_verified_at" datetime,
  "password" varchar not null,
  "remember_token" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "language" varchar not null default('en'),
  "team_id" integer,
  "joined_team_at" datetime,
  foreign key("team_id") references "teams"("id") on delete set null
);
CREATE UNIQUE INDEX "users_email_unique" on "users"("email");
CREATE INDEX "users_team_id_index" on "users"("team_id");
CREATE TABLE IF NOT EXISTS "model_has_roles"(
  "role_id" integer not null,
  "model_type" varchar not null,
  "model_id" integer not null,
  "team_id" integer,
  foreign key("role_id") references roles("id") on delete cascade on update no action,
  primary key("team_id", "role_id", "model_id", "model_type")
);
CREATE INDEX "model_has_roles_model_id_model_type_index" on "model_has_roles"(
  "model_id",
  "model_type"
);
CREATE INDEX "model_has_roles_team_foreign_key_index" on "model_has_roles"(
  "team_id"
);
CREATE TABLE IF NOT EXISTS "model_has_permissions"(
  "permission_id" integer not null,
  "model_type" varchar not null,
  "model_id" integer not null,
  "team_id" integer,
  foreign key("permission_id") references permissions("id") on delete cascade on update no action,
  primary key("team_id", "permission_id", "model_id", "model_type")
);
CREATE INDEX "model_has_permissions_model_id_model_type_index" on "model_has_permissions"(
  "model_id",
  "model_type"
);
CREATE INDEX "model_has_permissions_team_foreign_key_index" on "model_has_permissions"(
  "team_id"
);
CREATE TABLE IF NOT EXISTS "companies"(
  "id" integer primary key autoincrement not null,
  "name_en" varchar not null,
  "name_ar" varchar not null,
  "slug" varchar not null,
  "company_email" varchar not null,
  "description_en" text,
  "description_ar" text,
  "logo" varchar,
  "website" varchar,
  "owner_id" integer not null,
  "is_active" tinyint(1) not null default '1',
  "settings" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("owner_id") references "users"("id") on delete cascade
);
CREATE UNIQUE INDEX "companies_slug_unique" on "companies"("slug");
CREATE UNIQUE INDEX "companies_company_email_unique" on "companies"(
  "company_email"
);
CREATE TABLE IF NOT EXISTS "locations"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "code" varchar not null,
  "company_id" integer not null,
  "is_active" tinyint(1) not null default '1',
  "settings" text,
  "created_at" datetime,
  "updated_at" datetime,
  "building" varchar,
  "office_number" varchar,
  foreign key("company_id") references "companies"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "asset_assignments"(
  "id" integer primary key autoincrement not null,
  "asset_id" integer not null,
  "employee_id" integer not null,
  "assigned_by" integer not null,
  "assigned_date" date not null,
  "returned_date" date,
  "returned_by" integer,
  "status" varchar check("status" in('active', 'returned', 'lost', 'damaged')) not null default 'active',
  "assignment_notes" text,
  "return_notes" text,
  "condition_notes" text,
  "checklist_data" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("asset_id") references "assets"("id") on delete cascade,
  foreign key("employee_id") references "employees"("id") on delete cascade,
  foreign key("assigned_by") references "users"("id") on delete cascade,
  foreign key("returned_by") references "users"("id") on delete set null
);
CREATE TABLE IF NOT EXISTS "asset_categories"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "code" varchar not null,
  "description" text,
  "icon" varchar,
  "color" varchar,
  "company_id" integer not null,
  "is_active" tinyint(1) not null default '1',
  "custom_fields" text,
  "created_at" datetime,
  "updated_at" datetime,
  "_lft" integer not null default '0',
  "_rgt" integer not null default '0',
  "parent_id" integer,
  foreign key("company_id") references "companies"("id") on delete cascade
);
CREATE UNIQUE INDEX "asset_categories_code_unique" on "asset_categories"(
  "code"
);
CREATE TABLE IF NOT EXISTS "ticket_categories"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "code" varchar not null,
  "description" text,
  "icon" varchar,
  "color" varchar,
  "company_id" integer not null,
  "is_active" tinyint(1) not null default '1',
  "default_priority" integer not null default '2',
  "sla_hours" integer,
  "settings" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("company_id") references "companies"("id") on delete cascade
);
CREATE UNIQUE INDEX "ticket_categories_code_unique" on "ticket_categories"(
  "code"
);
CREATE TABLE IF NOT EXISTS "ticket_comments"(
  "id" integer primary key autoincrement not null,
  "ticket_id" integer not null,
  "user_id" integer not null,
  "comment" text not null,
  "type" varchar check("type" in('comment', 'status_change', 'assignment', 'resolution', 'internal')) not null default 'comment',
  "is_internal" tinyint(1) not null default '0',
  "time_spent" integer not null default '0',
  "metadata" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("ticket_id") references "tickets"("id") on delete cascade,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "tickets"(
  "id" integer primary key autoincrement not null,
  "ticket_number" varchar not null,
  "subject" varchar not null,
  "description" text not null,
  "ticket_category_id" integer not null,
  "company_id" integer not null,
  "reporter_id" integer not null,
  "assigned_to" integer,
  "location_id" integer,
  "asset_id" integer,
  "status" varchar check("status" in('open', 'in_progress', 'pending', 'resolved', 'closed')) not null default 'open',
  "priority" varchar check("priority" in('low', 'medium', 'high', 'critical')) not null default 'medium',
  "due_date" datetime,
  "resolved_at" datetime,
  "closed_at" datetime,
  "time_spent" integer not null default '0',
  "resolution" text,
  "resolved_by" integer,
  "custom_data" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("ticket_category_id") references "ticket_categories"("id") on delete cascade,
  foreign key("company_id") references "companies"("id") on delete cascade,
  foreign key("reporter_id") references "employees"("id") on delete cascade,
  foreign key("assigned_to") references "users"("id") on delete set null,
  foreign key("location_id") references "locations"("id") on delete set null,
  foreign key("asset_id") references "assets"("id") on delete set null,
  foreign key("resolved_by") references "users"("id") on delete set null
);
CREATE UNIQUE INDEX "tickets_ticket_number_unique" on "tickets"(
  "ticket_number"
);
CREATE UNIQUE INDEX "locations_company_id_code_unique" on "locations"(
  "company_id",
  "code"
);
CREATE TABLE IF NOT EXISTS "employees"(
  "id" integer primary key autoincrement not null,
  "employee_id" varchar,
  "first_name" varchar not null,
  "last_name" varchar not null,
  "email" varchar not null,
  "phone" varchar,
  "mobile" varchar,
  "department" varchar,
  "job_title" varchar,
  "manager" varchar,
  "hire_date" date,
  "termination_date" date,
  "employment_status" varchar not null default('active'),
  "company_id" integer not null,
  "notes" text,
  "settings" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("company_id") references companies("id") on delete cascade on update no action
);
CREATE UNIQUE INDEX "employees_email_unique" on "employees"("email");
CREATE UNIQUE INDEX "employees_employee_id_unique" on "employees"(
  "employee_id"
);
CREATE INDEX "asset_categories__lft__rgt_parent_id_index" on "asset_categories"(
  "_lft",
  "_rgt",
  "parent_id"
);
CREATE TABLE IF NOT EXISTS "assets"(
  "id" integer primary key autoincrement not null,
  "asset_tag" varchar not null,
  "asset_category_id" integer not null,
  "location_id" integer not null,
  "company_id" integer not null,
  "serial_number" varchar,
  "notes" text,
  "created_at" datetime,
  "updated_at" datetime,
  "service_tag_number" varchar,
  "finance_tag_number" varchar,
  "model_name" varchar,
  "model_number" varchar,
  "status" varchar check("status" in('available', 'assigned', 'maintenance', 'retired')) not null default 'available',
  foreign key("company_id") references companies("id") on delete cascade on update no action,
  foreign key("location_id") references locations("id") on delete cascade on update no action,
  foreign key("asset_category_id") references asset_categories("id") on delete cascade on update no action
);
CREATE UNIQUE INDEX "assets_asset_tag_unique" on "assets"("asset_tag");

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2025_06_28_182641_add_language_to_users_table',2);
INSERT INTO migrations VALUES(5,'2025_06_28_191640_create_permission_tables',3);
INSERT INTO migrations VALUES(6,'2025_06_28_191642_create_teams_table',3);
INSERT INTO migrations VALUES(7,'2025_06_28_191643_add_team_id_to_users_table',3);
INSERT INTO migrations VALUES(8,'2025_06_28_191645_add_team_id_to_permissions_tables',3);
INSERT INTO migrations VALUES(9,'2025_01_28_100000_allow_null_team_id_in_model_has_roles',4);
INSERT INTO migrations VALUES(10,'2025_06_28_210638_create_companies_table',5);
INSERT INTO migrations VALUES(11,'2025_06_28_211825_create_locations_table',6);
INSERT INTO migrations VALUES(12,'2025_06_28_211826_create_asset_assignments_table',6);
INSERT INTO migrations VALUES(13,'2025_06_28_211826_create_asset_categories_table',6);
INSERT INTO migrations VALUES(14,'2025_06_28_211826_create_assets_table',6);
INSERT INTO migrations VALUES(15,'2025_06_28_211826_create_employees_table',6);
INSERT INTO migrations VALUES(16,'2025_06_28_211826_create_ticket_categories_table',6);
INSERT INTO migrations VALUES(17,'2025_06_28_211826_create_ticket_comments_table',6);
INSERT INTO migrations VALUES(18,'2025_06_28_211826_create_tickets_table',6);
INSERT INTO migrations VALUES(19,'2025_06_28_214648_simplify_locations_table',7);
INSERT INTO migrations VALUES(20,'2025_06_29_042633_make_employee_id_nullable',8);
INSERT INTO migrations VALUES(21,'2025_06_29_043745_remove_location_id_from_employees',9);
INSERT INTO migrations VALUES(22,'2025_06_29_043826_add_nested_set_to_asset_categories',9);
INSERT INTO migrations VALUES(23,'2025_06_29_043909_update_assets_table_structure',10);
INSERT INTO migrations VALUES(24,'2025_06_29_074952_add_status_to_assets_table',11);
