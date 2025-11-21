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
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <flux:heading size="lg">My Groups</flux:heading>
            <flux:button
                variant="primary"
                wire:navigate
                href="{{ route('groups.create') }}"
                class="touch-target w-full sm:w-auto"
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
            <div class="text-center py-16 animate-fade-in">
                <div class="text-7xl mb-4">🏠</div>
                <flux:heading size="lg" class="mb-3">Welcome to SweepSquad!</flux:heading>
                <flux:text class="text-zinc-500 mb-6 max-w-md mx-auto">
                    Create your first group to start organizing and tracking cleaning tasks with your household or roommates.
                </flux:text>
                <flux:button
                    variant="primary"
                    wire:navigate
                    href="{{ route('groups.create') }}"
                    class="touch-target"
                >
                    Create Your First Group
                </flux:button>
            </div>
        @endif
    </div>
</div>
