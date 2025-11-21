# Task 4: Group Invite System

## Goal
Implement the invite system for users to join groups via shareable links.

## Description
Create the functionality for generating, sharing, and using invite links to join groups.

## Components to Build
1. **GroupInvite Model**
   - Enum for invite types: Permanent, SingleUse, TimeLimited
   - Relationships to Group and Users
   - Scopes: `valid()`, `expired()`, `unused()`
   - Methods: `isValid()`, `markAsUsed()`

2. **Actions**
   - `CreateInviteAction` - Generate new invite with UUID
   - `AcceptInviteAction` - User accepts invite and joins group
   - `RevokeInviteAction` - Admin revokes invite
   - `CleanupExpiredInvitesAction` - Queue job to remove expired invites

3. **Routes**
   - `GET /invite/{uuid}` - Accept invite page
   - `POST /invite/{uuid}/accept` - Process invite acceptance

4. **Volt Components**
   - `invites.create` - Create invite form
   - `invites.list` - List group invites (for admins)
   - `invites.accept` - Accept invite page

## Acceptance Criteria
- [ ] GroupInvite model created with all features
- [ ] All actions implemented with proper validation
- [ ] Redirect to login/register if unauthenticated
- [ ] Auto-join group after authentication
- [ ] Handle expired/used/invalid invites gracefully
- [ ] Display error messages for invalid invites
- [ ] Feature tests for all invite scenarios
- [ ] Tests pass

## Related Files
- `app/Models/GroupInvite.php`
- `app/Actions/Invites/`
- `resources/views/livewire/invites/`
- `routes/web.php`
- `tests/Feature/Invites/`

## Next Steps
After completion, proceed to Task 5: Cleaning Items Hierarchy
