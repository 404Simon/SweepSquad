<?php

use App\Actions\Groups\CreateGroupAction;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

use function Livewire\Volt\{layout};

layout('components.layouts.app');

new class extends Component {
    public string $name = '';
    public string $description = '';

    /**
     * Create a new group.
     */
    public function create(CreateGroupAction $action): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $group = $action->handle(
            Auth::user(),
            $validated['name'],
            $validated['description'] ?? null
        );

        session()->flash('success', 'Group created successfully!');

        $this->redirect(route('groups.show', $group), navigate: true);
    }
}; ?>

<div>
    <div class="max-w-2xl mx-auto px-4 py-8">
        <flux:heading size="lg" class="mb-6">Create New Group</flux:heading>

        <form wire:submit="create" class="space-y-6">
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
                    Create Group
                </flux:button>
                <flux:button
                    type="button"
                    variant="ghost"
                    wire:navigate
                    href="{{ route('groups.index') }}"
                >
                    Cancel
                </flux:button>
            </div>
        </form>
    </div>
</div>
