# Team & Permissions System Setup Guide

## Overview

This Laravel application now includes a comprehensive multi-tenant team system with role-based permissions using Spatie Laravel Permission. Each team operates as an isolated workspace with its own users, roles, and permissions.

## Architecture

### Core Components

1. **Teams**: Isolated workspaces with subscription management
2. **Users**: Can belong to one team at a time, can switch between teams
3. **Roles**: Team-specific roles (owner, admin, member, viewer)
4. **Permissions**: Granular permissions within each team context
5. **Super Admin**: Global admin role for platform management

### Database Structure

- `teams`: Team information, subscription status
- `users`: Users with team_id for current team context
- `roles`: Global and team-specific roles
- `permissions`: Available permissions
- `model_has_roles`: User-role assignments with team context
- `model_has_permissions`: Direct user permissions with team context

## System Roles

### Global Roles
- **super-admin**: Platform-wide admin access

### Team-Specific Roles
- **team-owner-{team_id}**: Full team control, billing, user management
- **team-admin-{team_id}**: Team management, user management (no billing)
- **team-member-{team_id}**: Content creation, basic team access
- **team-viewer-{team_id}**: Read-only access to team content

## Available Permissions

### Team Management
- `view team` - View team information
- `edit team` - Edit team settings
- `delete team` - Delete team
- `manage team settings` - Modify team configurations

### User Management
- `invite users` - Invite new users to team
- `view users` - View team members
- `edit users` - Edit user information
- `remove users` - Remove users from team
- `assign roles` - Assign roles to team members

### Content Management
- `create content` - Create new content
- `view content` - View content
- `edit content` - Edit existing content
- `delete content` - Delete content
- `publish content` - Publish content

### Administrative
- `view admin panel` - Access team admin features
- `manage billing` - Manage team subscription
- `view analytics` - View team analytics
- `export data` - Export team data

## Setup Instructions

### 1. Install Dependencies
```bash
composer require spatie/laravel-permission
```

### 2. Run Migrations
```bash
php artisan migrate
```

### 3. Seed Roles and Permissions
```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

### 4. Create Super Admin User
```bash
php artisan admin:create-super-admin "Your Name" "admin@yoursite.com" "secure-password"
```

### 5. Create a Team
```bash
php artisan team:create "My Company" "owner@company.com" --description="Our awesome team"
```

## Usage Guide

### For Super Admins

1. **Access Admin Panel**: Visit `/admin/dashboard`
2. **Manage Teams**: Create, view, suspend, or activate teams
3. **Monitor System**: View team statistics and user activity

### For Team Owners

1. **Team Management**: Edit team details, manage settings
2. **User Management**: Invite users, assign roles, remove members
3. **Billing**: Manage subscription and payment methods

### For Regular Users

1. **Team Selection**: Choose which team to work with
2. **Team Creation**: Create new teams (becomes owner)
3. **Team Switching**: Switch between teams you're member of

## Middleware Protection

### Routes are protected by:

- `auth`: User must be authenticated
- `verified`: Email must be verified
- `super-admin`: User must have super-admin role
- `team.access`: User must have active team membership
- `role:role-name`: User must have specific role
- `permission:permission-name`: User must have specific permission

## API Usage Examples

### Check User Permissions
```php
// Check if user has permission in current team context
if (auth()->user()->hasTeamPermission('edit content')) {
    // User can edit content in their current team
}

// Check if user has role in current team
if (auth()->user()->hasTeamRole('team-admin')) {
    // User is admin of their current team
}
```

### Switch Teams
```php
$team = Team::find(1);
auth()->user()->switchToTeam($team);
```

### Create Team with Roles
```php
$team = Team::create([
    'name' => 'New Team',
    'owner_id' => $user->id,
    // ... other fields
]);

// This automatically creates team-specific roles
$this->createTeamRoles($team);

// Assign owner role
$user->assignRole('team-owner-' . $team->id);
```

## Frontend Integration

### Vue Components
- `Teams/Select.vue`: Team selection interface
- `Teams/Create.vue`: Team creation form
- `Teams/Show.vue`: Team management interface
- `Admin/Teams/Index.vue`: Super admin team management

### Available Routes
- `/teams/select`: Choose team to work with
- `/teams/create`: Create new team
- `/teams/{team}`: Team management
- `/admin/teams`: Super admin team management

## Subscription Management

Teams support multiple subscription statuses:
- `trial`: 14-day trial period
- `active`: Paid subscription
- `past_due`: Payment failed
- `canceled`: Subscription canceled

## Security Features

1. **Team Isolation**: Users can only access their current team's data
2. **Permission Checks**: Every action requires proper permissions
3. **Role Validation**: Team-specific roles prevent cross-team access
4. **Middleware Protection**: Routes protected at multiple levels

## Troubleshooting

### Common Issues

1. **Permission Cache**: Clear permission cache after role changes
```bash
php artisan permission:cache-reset
```

2. **Missing Roles**: Recreate team roles if missing
```bash
php artisan team:create-roles {team-id}
```

3. **User Stuck**: Remove user from team if issues
```bash
// In tinker
$user = User::find(1);
$user->update(['team_id' => null]);
```

## Development Tips

1. **Testing**: Use factories to create teams with proper roles
2. **Policies**: Implement team-aware authorization policies
3. **Scoping**: Always scope queries by team context
4. **Notifications**: Send team-aware notifications

## Next Steps

1. **Billing Integration**: Add Stripe/Paddle integration
2. **Invitations**: Implement email-based team invitations
3. **Audit Logs**: Track team activities
4. **API**: Create team-aware API endpoints
5. **Multi-tenancy**: Consider database-per-tenant for larger scale 