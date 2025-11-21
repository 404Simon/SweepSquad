# 🧹 SweepSquad - Cleaning Game Concept

## Overview

**SweepSquad** is a gamified cleaning management application that transforms household chores into an engaging, collaborative experience. Users create cleaning groups, manage cleaning tasks in a hierarchical structure, and earn rewards for maintaining cleanliness.

## Core Concepts

### 🎯 Mission Statement

Make cleaning fun, trackable, and rewarding by gamifying household maintenance through collaborative groups, visual progress tracking, and a coin-based reward system.

### 👥 Target Audience

- Roommates sharing living spaces
- Families managing household chores
- Couples dividing cleaning responsibilities
- Small offices maintaining shared spaces

## Feature Breakdown

### 1. User Management & Authentication

#### User Registration & Login

- Standard email/password authentication (via Laravel Fortify)
- Email verification for new accounts
- Password reset functionality
- Two-factor authentication (optional)
- Profile management with avatar support

#### User Profiles

- Display name and email
- Total coins earned (lifetime)
- Current streak (consecutive days cleaned)
- Cleaning statistics and leaderboards within groups
- Achievement badges

### 2. Groups (Cleaning Squads)

#### Group Creation

- Any authenticated user can create a group
- Group attributes:
  - **Name**: "Apartment 42", "Johnson Family Home", etc.
  - **Description**: Optional details about the space
  - **UUID**: Unique identifier for invite links
  - **Created by**: Owner/creator
  - **Settings**: Configurable options (coin multipliers, notification preferences)

#### Group Membership

- **Roles**:
  - **Owner**: Full control (can delete group, manage all settings)
  - **Admin**: Can invite users, manage rooms/items, but cannot delete group
  - **Member**: Can mark items as cleaned, view everything
  - **Guest**: View-only access (optional future feature)

#### Invite System

- Generate shareable invite links with UUID
- Example: `https://sweepsquad.app/invite/550e8400-e29b-41d4-a716-446655440000`
- Invite links can be:
  - **Permanent**: Always active
  - **Single-use**: Expires after one person joins
  - **Time-limited**: Expires after X days/hours
- User clicks link → Redirected to login/register if not authenticated → Automatically joins group
- Display pending invitations in group settings

#### Leaving Groups

- Members can leave groups anytime
- Last member leaving deletes the group (with confirmation)
- Owners can transfer ownership before leaving

### 3. Hierarchical Cleaning Structure

The core of the application is a flexible tree structure for organizing spaces and tasks.

#### Structure Levels

```
Group (Household/Office)
└── Room (Kitchen, Bathroom, Living Room)
    └── Area/Item (Sink, Floor, Table, Toilet)
        └── Sub-item (Kitchen Floor - Under Table, Bathroom Sink - Faucet)
```

#### Flexibility

- Unlimited nesting depth (practical limit: 4-5 levels)
- Users define their own structure based on their space
- Templates available for common room types

#### Item/Area Attributes

Every cleanable item in the hierarchy has:

##### Basic Information

- **Name**: "Kitchen Floor", "Bathroom Toilet", "Coffee Table"
- **Description**: Optional notes or special instructions
- **Parent**: Reference to parent item (Room → Area → Sub-item)
- **Order**: For sorting items within the same parent

##### Cleaning Configuration

- **Cleaning Frequency**: How often it should be cleaned
  - Measured in hours: 1 hour, 6 hours, 12 hours, 24 hours, 48 hours, 168 hours (weekly), etc.
  - Custom frequency in hours
  - Examples:
    - Toilet: Every 48 hours (2 days)
    - Kitchen floor: Every 168 hours (1 week)
    - Bathroom sink: Every 24 hours (daily)
    - Coffee table: Every 72 hours (3 days)
- **Base Coin Reward**: Coins awarded when cleaned at 100% dirtiness
  - Higher frequency items = more coins (e.g., daily toilet cleaning = 20 coins)
  - Lower frequency items = fewer coins (e.g., weekly deep clean = 50 coins)
  - Calculated based on effort and frequency

##### Dynamic State

- **Last Cleaned**: Timestamp of last cleaning
- **Last Cleaned By**: User who cleaned it
- **Dirtiness Percentage**: 0-100%
  - Calculated based on time elapsed since last cleaning
  - Formula: `(time_since_last_clean / cleaning_frequency) × 100`
  - Capped at 100%
  - Examples:
    - Item cleaned 12 hours ago with 24-hour frequency = 50% dirty
    - Item cleaned 30 hours ago with 24-hour frequency = 100% dirty
    - Item cleaned 1 hour ago with 24-hour frequency = 4.17% dirty
- **Status Flags**:
  - `overdue`: Boolean (true if dirtiness >= 100%)
  - `needs_attention`: Boolean (true if dirtiness >= 80%)
  - `clean`: Boolean (true if dirtiness < 20%)

### 4. Cleaning Mechanics

#### Marking Items as Cleaned

**User Flow**:
1. User navigates to an item/area
2. Views current dirtiness percentage
3. Clicks "Mark as Cleaned" button
4. Confirmation modal shows:
   - Current dirtiness: "85%"
   - Coins to be earned: "17 coins" (85% of 20 base coins)
   - Optional: Add photo proof (future feature)
   - Optional: Add note
5. User confirms
6. System updates:
   - Sets `last_cleaned` to current timestamp
   - Sets `dirtiness` to 0%
   - Awards coins to user
   - Records cleaning log entry
   - Updates user's streak

#### Coin Calculation

```
coins_earned = base_coin_reward × (dirtiness_percentage / 100)
```
**Examples**:
- Toilet (20 base coins, 100% dirty) = 20 coins
- Toilet (20 base coins, 50% dirty) = 10 coins
- Kitchen floor (50 base coins, 85% dirty) = 42.5 coins (rounded to 43)
**Bonuses**:
- **Streak Bonus**: +10% coins for 7+ day streak, +20% for 14+ day streak
- **Speed Bonus**: +5% coins if cleaned before 80% dirtiness
- **Perfect Clean**: +25% coins if cleaned exactly at 100% (maximum dirtiness)

#### Cleaning History

- Track every cleaning action
- Attributes:
  - User who cleaned
  - Item cleaned
  - Timestamp
  - Dirtiness at time of cleaning
  - Coins earned
  - Optional notes/photos

### 5. Gamification & Rewards

#### Coin System

- Virtual currency earned by cleaning
- **Uses for Coins** (future features):
  - Unlock custom avatars/themes
  - Group challenges and competitions
  - Trade coins for "skip a chore" vouchers within group
  - Charity donations (partner with real cleaning charities)

#### User Statistics Dashboard

- **Personal Stats**:
  - Total coins earned (all-time)
  - Coins this week/month
  - Current streak
  - Total items cleaned
  - Favorite room (most cleaned)
  - Cleaning consistency score
- **Group Leaderboards**:
  - Top cleaners this week/month
  - Most valuable cleaner (highest coin earnings)
  - Most consistent member (longest streak)
  - Room champions (who cleans which room most)

#### Achievement System

Unlockable badges and achievements:
**Beginner Achievements**:
- "First Clean": Clean your first item
- "Squad Member": Join a group
- "Squad Creator": Create a group
**Progress Achievements**:
- "Coin Collector": Earn 100/500/1000/5000 coins
- "Streak Master": Maintain 7/14/30/90 day streak
- "Clean Sweep": Clean all items in a room in one day
- "Speed Demon": Clean 10 items before they reach 50% dirtiness
**Social Achievements**:
- "Team Player": Clean with 3+ different group members in a week
- "Room Owner": Clean the same room 50 times
- "Jack of All Trades": Clean every item type in a group
**Challenge Achievements**:
- "Perfectionist": Clean 10 items at exactly 100% dirtiness
- "Early Bird": Clean 20 items before 7am
- "Night Owl": Clean 20 items after 10pm

### 6. User Interface & Experience

#### Dashboard (Home Page)

After login, users see:
**Overview Section**:
- Welcome message with user's current streak
- Today's stats: coins earned, items cleaned
- Quick actions: "Join Group", "Create Group"
**My Groups Section**:
- Cards for each group user belongs to
- Per group:
  - Group name and member count
  - Items needing attention (80%+ dirty)
  - Items overdue (100%+ dirty)
  - Quick "Jump to Group" button
**Recent Activity Feed**:
- Recent cleanings by all group members
- Example: "Alice cleaned Kitchen Sink • 2 hours ago • +15 coins"
#### Group View

Hierarchical navigation with visual indicators:
**Tree/List View**:
```
🏠 Johnson Family Home (Group)
  📊 Group Stats: 23 items • 12 need attention • 5 overdue
  🚪 Kitchen
    [████████░░] 80% - Floor ⚠️ • 40 coins • Last: 5 days ago
    [██░░░░░░░░] 20% - Sink ✓ • Last: 4 hours ago by Alice
    [██████████] 100% - Trash 🔴 • 15 coins • Last: 8 days ago
    
  🚪 Bathroom
    [██████████] 100% - Toilet 🔴 • 20 coins • Last: 3 days ago
    [████░░░░░░] 40% - Sink • 10 coins • Last: 1 day ago
    
  🚪 Living Room
    [░░░░░░░░░░] 0% - Coffee Table ✨ • Just cleaned by Bob!
```
**Visual Indicators**:
- Progress bars showing dirtiness
- Color coding:
  - Green (0-20%): Clean ✨
  - Yellow (20-50%): Getting dirty ⚡
  - Orange (50-80%): Needs cleaning soon ⚠️
  - Red (80-100%): Urgent! 🔴
  - Dark red (100%+): Overdue! 🚨
**Filters & Sorting**:
- Show only: Overdue, Needs attention, All
- Sort by: Dirtiness, Coins, Last cleaned, Name
- Group by: Room, Assigned person (future)

#### Item Detail Page

When clicking an item:
**Header**:
- Breadcrumb: Group > Room > Item
- Item name with edit button (for admins)
**Status Card**:
- Large circular progress indicator showing dirtiness
- Calculated time until 100% dirty
- Base coin reward
- Potential coins if cleaned now
**Action Buttons**:
- Primary: "Mark as Cleaned" (large, prominent)
- Secondary: "Edit Item", "View History"
**Cleaning History**:
- Timeline of recent cleanings
- Who cleaned it, when, and coins earned
- Trends: "Usually cleaned every 2.3 days"
**Statistics**:
- Total times cleaned
- Average dirtiness when cleaned
- Most frequent cleaner
- Best streak

#### Mobile-First Design

- Responsive design with mobile as primary target
- Touch-friendly buttons and interactions
- Push notifications for overdue items (optional)
- Quick "Mark Clean" from notification
- Offline support: Queue cleanings when offline

### 7. Data Models

#### User

```
- id: bigint (primary key)
- name: string
- email: string (unique)
- email_verified_at: timestamp (nullable)
- password: string (hashed)
- profile_photo: string (nullable)
- total_coins: integer (default: 0)
- current_streak: integer (default: 0)
- last_cleaned_at: timestamp (nullable)
- created_at: timestamp
- updated_at: timestamp
```
#### Group

```
- id: bigint (primary key)
- uuid: uuid (unique, for invites)
- name: string
- description: text (nullable)
- owner_id: bigint (foreign key → users)
- settings: json (coin multipliers, preferences)
- created_at: timestamp
- updated_at: timestamp
```
#### GroupMember

```
- id: bigint (primary key)
- group_id: bigint (foreign key → groups)
- user_id: bigint (foreign key → users)
- role: enum (owner, admin, member)
- joined_at: timestamp
- created_at: timestamp
- updated_at: timestamp
- unique: (group_id, user_id)
```
#### GroupInvite

```
- id: bigint (primary key)
- uuid: uuid (unique, for invite link)
- group_id: bigint (foreign key → groups)
- created_by: bigint (foreign key → users)
- type: enum (permanent, single_use, time_limited)
- expires_at: timestamp (nullable)
- used_by: bigint (foreign key → users, nullable)
- used_at: timestamp (nullable)
- created_at: timestamp
- updated_at: timestamp
```
#### CleaningItem

```
- id: bigint (primary key)
- group_id: bigint (foreign key → groups)
- parent_id: bigint (foreign key → cleaning_items, nullable)
- name: string
- description: text (nullable)
- cleaning_frequency_hours: integer (how often to clean)
- base_coin_reward: integer
- last_cleaned_at: timestamp (nullable)
- last_cleaned_by: bigint (foreign key → users, nullable)
- order: integer (for sorting)
- created_at: timestamp
- updated_at: timestamp
- Virtual attributes (computed):
  - dirtiness_percentage: float (0-100)
  - is_overdue: boolean
  - needs_attention: boolean
  - coins_available: integer
```
#### CleaningLog

```
- id: bigint (primary key)
- cleaning_item_id: bigint (foreign key → cleaning_items)
- user_id: bigint (foreign key → users)
- group_id: bigint (foreign key → groups)
- dirtiness_at_clean: float (0-100)
- coins_earned: integer
- notes: text (nullable)
- photo: string (nullable, future)
- cleaned_at: timestamp
- created_at: timestamp
```
#### UserAchievement

```
- id: bigint (primary key)
- user_id: bigint (foreign key → users)
- achievement_code: string (e.g., 'first_clean', 'streak_7')
- earned_at: timestamp
- created_at: timestamp
- unique: (user_id, achievement_code)
```

### 8. Technical Implementation Notes

#### Dirtiness Calculation

Computed dynamically when accessed:
```php
public function getDirtinessPercentageAttribute(): float
{
    if (!$this->last_cleaned_at) {
        // Never cleaned - consider 100% dirty
        return 100.0;
    }
    
    $hoursSinceClean = $this->last_cleaned_at->diffInHours(now());
    $percentage = ($hoursSinceClean / $this->cleaning_frequency_hours) * 100;
    
    return min($percentage, 100.0); // Cap at 100%
}
```
#### Efficient Queries

- Use eager loading for nested items
- Cache dirtiness calculations for list views (using database cache)
- Index frequently queried columns (group_id, parent_id, last_cleaned_at)

#### Background Jobs

- **Daily Streak Checker**: Scheduled job to reset streaks if user hasn't cleaned in 24 hours (runs via Laravel Scheduler)
- **Cleanup Tasks**: Remove expired invites (runs via Laravel Scheduler)
- **Queue Worker**: Run `php artisan queue:work` to process background jobs (database queue driver)

### 9. User Workflows

#### New User Journey

1. **Landing Page** → "Join SweepSquad" CTA
2. **Registration** → Email verification
3. **Welcome Screen** → "Create a group or join one"
4. **Option A: Create Group**
   - Enter group name
   - Use quick setup wizard with room templates
   - Invite roommates via link
5. **Option B: Join via Invite Link**
   - Click link from friend
   - Auto-join after login/register
6. **First Clean**
   - Tutorial overlay highlights dirty items
   - Click "Mark as Cleaned" on first item
   - Celebrate with animation: "+20 coins! 🎉"
7. **Dashboard** → See progress and start cleaning habit
#### Daily User Workflow

1. Login → Dashboard shows overdue items
2. Navigate to group → See what needs attention
3. Clean physical space
4. Mark items as cleaned → Earn coins
5. Check leaderboard → Friendly competition
6. Repeat tomorrow → Build streak
#### Group Admin Workflow

1. Create/manage rooms and items
2. Set appropriate cleaning frequencies
3. Adjust coin rewards for balance
4. Review group statistics
5. Invite new members
6. Celebrate group achievements

### 10. Future Feature Ideas

#### Phase 2 Features

- **Assigned Tasks**: Assign specific items to specific members
- **Recurring Schedules**: "Alice cleans toilet on Mondays and Thursdays"
- **Photo Verification**: Require/optional photos when marking clean
- **Comments**: Discussion threads on items
- **Custom Icons**: Choose icons for rooms/items
#### Phase 3 Features

- **Group Challenges**: 
  - "Clean all rooms this weekend - 2x coins!"
  - Team competitions between groups
- **Coin Marketplace**:
  - Trade coins for profile customization
  - "Skip a chore" vouchers
  - Donate to real charities
- **Calendar Integration**: 
  - Export cleaning schedule to Google Calendar
  - Reminders via calendar events
- **Smart Suggestions**:
  - ML-based predictions: "Kitchen floor usually needs cleaning on Saturdays"
  - Optimize cleaning schedules
#### Phase 4 Features

- **Public Groups**: 
  - Office spaces, community centers
  - View-only public leaderboards
- **Integrations**:
  - Smart home integration (notify when robot vacuum runs)
  - Todoist/Trello integration
- **Gamification++**:
  - Seasonal events with special rewards
  - Customizable avatars and profiles
  - Group vs. group tournaments

### 11. Success Metrics

**User Engagement**:
- Daily active users (DAU)
- Average cleanings per user per week
- Streak retention rate (% users maintaining 7+ day streaks)
**Group Health**:
- Average group size
- Invite conversion rate
- Active groups (at least 1 cleaning per week)
**Feature Adoption**:
- % users earning achievements
- Leaderboard view rate
- Mobile vs. desktop usage
**Business Metrics** (if applicable):
- User retention (30-day, 90-day)
- Viral coefficient (invites sent → new users)
- Premium conversion rate (future monetization)

## Visual Design Direction

### Color Palette

- **Primary**: Fresh green (#10B981) - Represents cleanliness and growth
- **Secondary**: Bright blue (#3B82F6) - Energy and motivation
- **Success**: Mint green (#34D399) - Clean items
- **Warning**: Amber (#F59E0B) - Needs attention
- **Danger**: Red (#EF4444) - Overdue items
- **Neutral**: Gray scale for backgrounds and text

### Typography

- **Headings**: Bold, modern sans-serif (Inter, Poppins)
- **Body**: Readable sans-serif (Inter, System UI)
- **Coins/Stats**: Tabular numbers for alignment

### Illustrations & Icons

- Playful cleaning-themed illustrations
- Lucide icons for UI elements (already available in Flux UI)
- Custom icons for achievements
- Animated celebrations for milestones

### Tone & Voice

- **Friendly & Encouraging**: "Great job! You're on a roll! 🔥"
- **Playful**: "That toilet won't clean itself... or will it? 🤔"
- **Motivational**: "3 more items and you'll hit 100 coins today!"
- **Casual**: Avoid corporate speak, feel like a helpful friend

## Technical Stack

### Backend

- **Framework**: Laravel 12 (PHP 8.4)
- **Authentication**: Laravel Fortify
- **Database**: SQLite (simple, file-based, no external services)
- **Queue**: Database driver (no Redis needed)
- **Cache**: Database driver (no Redis needed)

### Frontend

- **Framework**: Livewire 3 + Volt (reactive components)
- **UI Library**: Flux UI Free (Tailwind CSS-based)
- **CSS**: Tailwind CSS 4
- **JavaScript**: Alpine.js (bundled with Livewire)
- **Build**: Vite

### Testing

- **Framework**: Pest 4
- **Browser Testing**: Pest Browser Testing
- **Coverage**: Feature tests and browser tests for all workflows

### DevOps

- **Code Quality**: Laravel Pint (formatting) + Rector (refactoring)
- **CI/CD**: GitHub Actions (lint, tests)
- **Deployment**: Simple deployment - no additional services required

## Implementation Phases

### Phase 1: MVP (Weeks 1-4)

**Goal**: Core functionality - groups, items, cleaning
- ✅ User authentication (already exists)
- 🔨 Group CRUD (create, invite, join, leave)
- 🔨 Hierarchical cleaning items (CRUD)
- 🔨 Dirtiness calculation system
- 🔨 Mark as cleaned functionality
- 🔨 Coin earning system
- 🔨 Basic dashboard and navigation
- 🔨 Mobile-responsive UI

### Phase 2: Gamification (Weeks 5-6)

**Goal**: Make it fun and engaging
- 🔨 User statistics dashboard
- 🔨 Group leaderboards
- 🔨 Streak tracking
- 🔨 Achievement system
- 🔨 Cleaning history/logs
- 🔨 Visual progress indicators

### Phase 3: Polish & UX (Weeks 7-8)

**Goal**: Smooth experience and optimization
- 🔨 Advanced filtering and sorting
- 🔨 Onboarding tutorial
- 🔨 Push notifications
- 🔨 Performance optimization
- 🔨 Comprehensive testing
- 🔨 Bug fixes and refinement

### Phase 4: Launch Prep (Week 9+)

**Goal**: Production-ready
- 🔨 Security audit
- 🔨 Load testing
- 🔨 Documentation
- 🔨 Marketing site
- 🔨 Beta testing
- 🚀 Launch!

## Conclusion

**SweepSquad** transforms the mundane task of cleaning into an engaging, social, and rewarding experience. By combining gamification mechanics with practical cleaning management, it motivates users to maintain cleaner spaces while building positive habits.
The hierarchical structure provides flexibility for any living situation, while the coin system and achievements create a compelling progression system. The collaborative nature of groups turns cleaning into a team effort, fostering accountability and friendly competition.
This concept document serves as the foundation for building a unique product that solves a real problem: motivating people to keep their spaces clean consistently.

**Let's make cleaning fun! 🧹✨**
