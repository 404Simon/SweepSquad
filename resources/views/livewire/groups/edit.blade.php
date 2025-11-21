<?php

use App\Actions\Groups\UpdateGroupAction;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

use function Livewire\Volt\{layout};

layout('components.layouts.app');

new class extends Component {
    #[Locked]
    public Group $group;

    public string $name = '';
    public string $description = '';

    /**
     * Mount the component.
     */
    public function mount($id): void
    {
        $this->group = Group::query()->findOrFail($id);

        // Authorization check
        if ($this->group->owner_id !== Auth::id()) {
            abort(403, 'Only the group owner can edit group details.');
        }

        $this->name = $this->group->name;
        $this->description = $this->group->description ?? '';
    }

    /**
     * Update the group.
     */
    public function update(UpdateGroupAction $action): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $action->handle($this->group, $validated);

        session()->flash('success', 'Group updated successfully!');

        $this->redirect(route('groups.show', $this->group), navigate: true);
    }
}; ?>

<div>
    <div class="max-w-2xl mx-auto px-4 py-8">
        <flux:heading size="lg" class="mb-6">Edit Group</flux:heading>

        <form wire:submit="update" class="space-y-6">
            <flux:field>
                <flux:label for="name">Group Name</flux:label>
                <flux:input
                    id="name"
                    wire:model="name"
                    type="text"
                    placeholder="Enter group name"
                    required
                />
                @error('name')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </flux:field>

            <flux:field>
                <flux:label for="description">Description</flux:label>
                <flux:textarea
                    id="description"
                    wire:model="description"
                    placeholder="Enter group description (optional)"
                    rows="4"
                />
                @error('description')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </flux:field>

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">
                    Update Group
                </flux:button>
                <flux:button
                    type="button"
                    variant="ghost"
                    wire:navigate
                    href="{{ route('groups.show', $group) }}"
                >
                    Cancel
                </flux:button>
            </div>
        </form>
    </div>
</div>
