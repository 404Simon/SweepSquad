<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

use function Livewire\Volt\{layout};

layout('components.layouts.app');

new class extends Component {
    public function with(): array
    {
        /** @var User $user */
        $user = Auth::user();

        return [
            'groups' => $user->groups()->with('owner')->get(),
            'ownedGroups' => $user->ownedGroups()->withCount('members')->get(),
        ];
    }
}; ?>

<div>
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <flux:heading size="lg">My Groups</flux:heading>
            <flux:button
                variant="primary"
                wire:navigate
                href="{{ route('groups.create') }}"
            >
                Create Group
            </flux:button>
        </div>

        @if($ownedGroups->isNotEmpty())
            <div class="mb-8">
                <flux:heading size="base" class="mb-4">Groups I Own</flux:heading>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($ownedGroups as $group)
                        <a
                            href="{{ route('groups.show', $group) }}"
                            wire:navigate
                            class="block p-6 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600 transition-colors"
                        >
                            <div class="flex justify-between items-start mb-2">
                                <flux:heading size="sm">{{ $group->name }}</flux:heading>
                                <flux:badge variant="primary">Owner</flux:badge>
                            </div>
                            @if($group->description)
                                <flux:text class="text-sm mb-4">{{ Str::limit($group->description, 100) }}</flux:text>
                            @endif
                            <flux:text class="text-sm text-zinc-500">
                                {{ $group->members_count }} {{ Str::plural('member', $group->members_count) }}
                            </flux:text>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if($groups->whereNotIn('id', $ownedGroups->pluck('id'))->isNotEmpty())
            <div>
                <flux:heading size="base" class="mb-4">Groups I'm In</flux:heading>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($groups->whereNotIn('id', $ownedGroups->pluck('id')) as $group)
                        <a
                            href="{{ route('groups.show', $group) }}"
                            wire:navigate
                            class="block p-6 bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600 transition-colors"
                        >
                            <flux:heading size="sm" class="mb-2">{{ $group->name }}</flux:heading>
                            @if($group->description)
                                <flux:text class="text-sm mb-4">{{ Str::limit($group->description, 100) }}</flux:text>
                            @endif
                            <flux:text class="text-sm text-zinc-500">
                                Owner: {{ $group->owner->name }}
                            </flux:text>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if($groups->isEmpty() && $ownedGroups->isEmpty())
            <div class="text-center py-12">
                <flux:text class="text-zinc-500 mb-4">You're not part of any groups yet.</flux:text>
                <flux:button
                    variant="primary"
                    wire:navigate
                    href="{{ route('groups.create') }}"
                >
                    Create Your First Group
                </flux:button>
            </div>
        @endif
    </div>
</div>
