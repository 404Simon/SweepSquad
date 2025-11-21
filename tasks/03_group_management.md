# Task 3: Group Management System

## Goal
Implement group creation, management, and membership functionality.

## Description
Build the core group system allowing users to create groups, manage members, and handle ownership.

## Components to Build
1. **Group Model & Relationships**
   - `owner()` relationship
   - `members()` relationship through GroupMember
   - `cleaningItems()` relationship
   - `invites()` relationship

2. **GroupMember Model**
   - Enum for roles: Owner, Admin, Member
   - Relationships to User and Group

3. **Actions**
   - `CreateGroupAction` - Create new group with owner
   - `UpdateGroupAction` - Update group details
   - `DeleteGroupAction` - Delete group (owner only)
   - `AddGroupMemberAction` - Add user to group
   - `RemoveGroupMemberAction` - Remove user from group
   - `TransferOwnershipAction` - Transfer ownership to another member
   - `LeaveGroupAction` - User leaves group

4. **Volt Components**
   - `groups.create` - Create group form
   - `groups.edit` - Edit group form
   - `groups.index` - List user's groups
   - `groups.show` - Group detail page

## Acceptance Criteria
- [ ] Models created with all relationships
- [ ] All actions implemented using Actions pattern
- [ ] Actions wrapped in DB transactions
- [ ] Volt components created for all CRUD operations
- [ ] Authorization checks (owner/admin/member permissions)
- [ ] Feature tests for all actions created
- [ ] Tests pass

## Related Files
- `app/Models/Group.php`
- `app/Models/GroupMember.php`
- `app/Actions/Groups/`
- `resources/views/livewire/groups/`
- `tests/Feature/Groups/`

## Next Steps
After completion, proceed to Task 4: Group Invite System
