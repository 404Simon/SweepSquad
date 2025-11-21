# Task 5: Cleaning Items Hierarchy

## Goal
Implement the hierarchical structure for cleaning items (rooms, areas, sub-items).

## Description
Build the flexible tree structure that allows users to organize their cleaning spaces in a nested hierarchy.

## Components to Build
1. **CleaningItem Model**
   - Self-referencing relationship: `parent()`, `children()`
   - Relationship to Group and User (last cleaner)
   - Virtual attributes (computed properties):
     - `dirtiness_percentage` - Calculate based on time since last clean
     - `is_overdue` - Boolean (dirtiness >= 100%)
     - `needs_attention` - Boolean (dirtiness >= 80%)
     - `is_clean` - Boolean (dirtiness < 20%)
     - `coins_available` - Coins if cleaned now
   - Scopes: `roots()`, `overdue()`, `needsAttention()`, `clean()`
   - Methods: `calculateDirtiness()`, `getCoinsAvailable()`

2. **Actions**
   - `CreateCleaningItemAction` - Create new item
   - `UpdateCleaningItemAction` - Update item details
   - `DeleteCleaningItemAction` - Delete item and handle children
   - `ReorderCleaningItemsAction` - Change item order
   - `MoveCleaningItemAction` - Move item to different parent

3. **Volt Components**
   - `cleaning-items.create` - Create item form
   - `cleaning-items.edit` - Edit item form
   - `cleaning-items.tree` - Display hierarchical tree view
   - `cleaning-items.show` - Item detail page

## Acceptance Criteria
- [ ] CleaningItem model with all virtual attributes
- [ ] Dirtiness calculation formula implemented correctly
- [ ] All relationships and scopes defined
- [ ] Actions for CRUD operations
- [ ] Tree view component with visual indicators
- [ ] Ability to nest items up to 5 levels deep
- [ ] Order management within same parent
- [ ] Unit tests for dirtiness calculation
- [ ] Feature tests for all CRUD operations
- [ ] Tests pass

## Related Files
- `app/Models/CleaningItem.php`
- `app/Actions/CleaningItems/`
- `resources/views/livewire/cleaning-items/`
- `tests/Unit/CleaningItemTest.php`
- `tests/Feature/CleaningItems/`

## Next Steps
After completion, proceed to Task 6: Mark as Cleaned Functionality
