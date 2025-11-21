# Task 8: Group Detail View

## Goal
Create the detailed group view showing hierarchical cleaning items with visual indicators.

## Description
Build an engaging group view that displays all cleaning items in a tree structure with color-coded progress bars and sorting/filtering options.

## Components to Build
1. **Group Show Component** (`groups.show`)
   - Group header:
     - Group name and description
     - Member count
     - Group stats (total items, need attention, overdue)
     - Admin actions (edit, invite, settings)
   - Filters:
     - Show: All / Overdue / Needs Attention / Clean
     - Sort by: Dirtiness / Coins / Last Cleaned / Name
   - Hierarchical item tree:
     - Nested items with indentation
     - Progress bars showing dirtiness
     - Color coding (green/yellow/orange/red)
     - Icons for status
     - Coins available
     - Last cleaned info

2. **Supporting Components**
   - `groups.item-row` - Single item row in tree
   - `groups.progress-bar` - Visual dirtiness indicator
   - `groups.filters` - Filter and sort controls

3. **Styling**
   - Progress bars with Tailwind
   - Color scheme:
     - Green (0-20%): Clean
     - Yellow (20-50%): Getting dirty
     - Orange (50-80%): Needs cleaning soon
     - Red (80-100%): Urgent
     - Dark red (100%+): Overdue

## Acceptance Criteria
- [ ] Group show component displays all info
- [ ] Items loaded efficiently with relationships
- [ ] Hierarchical structure displayed correctly
- [ ] Progress bars show accurate dirtiness
- [ ] Color coding matches specification
- [ ] Filters work (overdue, needs attention, all)
- [ ] Sorting works (dirtiness, coins, etc.)
- [ ] Responsive design
- [ ] Click item to view details
- [ ] Click "Mark as Cleaned" inline
- [ ] Feature tests for group view
- [ ] Tests pass

## Related Files
- `resources/views/livewire/groups/show.blade.php`
- `resources/views/livewire/groups/item-row.blade.php`
- `resources/views/livewire/groups/progress-bar.blade.php`
- `resources/views/livewire/groups/filters.blade.php`
- `routes/web.php`
- `tests/Feature/Groups/ShowTest.php`

## Next Steps
After completion, proceed to Task 9: Item Detail Page
