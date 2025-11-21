# Task 9: Item Detail Page

## Goal
Create a detailed view for individual cleaning items showing stats, history, and actions.

## Description
Build a comprehensive item detail page that provides all information about a specific cleaning item and its cleaning history.

## Components to Build
1. **Item Show Component** (`cleaning-items.show`)
   - Breadcrumb navigation: Group > Room > Item
   - Item header:
     - Item name
     - Edit button (for admins)
   - Status card:
     - Large circular progress indicator (dirtiness %)
     - Time until 100% dirty
     - Base coin reward
     - Potential coins if cleaned now
   - Action buttons:
     - "Mark as Cleaned" (primary, large)
     - "Edit Item" (secondary, admins only)
   - Cleaning history section:
     - Timeline of recent cleanings
     - Who cleaned, when, coins earned
     - Optional notes
   - Statistics card:
     - Total times cleaned
     - Average dirtiness when cleaned
     - Most frequent cleaner
     - Average time between cleans

2. **Supporting Components**
   - `cleaning-items.circular-progress` - Circular dirtiness indicator
   - `cleaning-items.history-timeline` - Cleaning history timeline
   - `cleaning-items.stats-grid` - Statistics grid

## Acceptance Criteria
- [ ] Item show component displays all information
- [ ] Breadcrumb navigation works
- [ ] Circular progress indicator accurate
- [ ] Calculations correct (time until dirty, coins available)
- [ ] Cleaning history loaded and displayed
- [ ] Statistics calculated correctly
- [ ] "Mark as Cleaned" button triggers modal
- [ ] Edit button visible only to admins
- [ ] Responsive design
- [ ] Feature tests for item detail page
- [ ] Tests pass

## Related Files
- `resources/views/livewire/cleaning-items/show.blade.php`
- `resources/views/livewire/cleaning-items/circular-progress.blade.php`
- `resources/views/livewire/cleaning-items/history-timeline.blade.php`
- `resources/views/livewire/cleaning-items/stats-grid.blade.php`
- `routes/web.php`
- `tests/Feature/CleaningItems/ShowTest.php`

## Next Steps
After completion, proceed to Task 10: User Statistics & Achievements
