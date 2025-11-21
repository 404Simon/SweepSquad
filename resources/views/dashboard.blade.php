<x-layouts.app :title="__('Dashboard')">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <flux:heading size="lg" class="mb-6">Dashboard</flux:heading>

        {{-- User Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            {{-- Total Coins --}}
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center gap-3 mb-2">
                    <flux:icon.currency-dollar class="size-8 text-yellow-500" />
                    <div>
                        <flux:text class="text-sm text-zinc-500">Total coins</flux:text>
                        <flux:heading size="lg">{{ number_format(Auth::user()->total_coins) }}</flux:heading>
                    </div>
                </div>
            </div>

            {{-- Current Streak --}}
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center gap-3 mb-2">
                    <flux:icon.fire class="size-8 text-orange-500" />
                    <div>
                        <flux:text class="text-sm text-zinc-500">Current streak</flux:text>
                        <flux:heading size="lg">{{ Auth::user()->current_streak }} day streak</flux:heading>
                    </div>
                </div>
            </div>

            {{-- Longest Streak --}}
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center gap-3 mb-2">
                    <flux:icon.trophy class="size-8 text-purple-500" />
                    <div>
                        <flux:text class="text-sm text-zinc-500">Longest streak</flux:text>
                        <flux:heading size="lg">{{ Auth::user()->longest_streak }} day streak</flux:heading>
                    </div>
                </div>
            </div>
        </div>

        {{-- Groups Section --}}
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="md">My Groups</flux:heading>
                <flux:button variant="primary" wire:navigate href="{{ route('groups.index') }}">
                    View All Groups
                </flux:button>
            </div>

            @php
                $userGroups = Auth::user()->groups()->with('owner')->limit(5)->get();
            @endphp

            @if($userGroups->isEmpty())
                <div class="text-center py-8">
                    <div class="text-5xl mb-3">👥</div>
                    <flux:heading size="base" class="mb-2">No Groups Yet</flux:heading>
                    <flux:text class="text-zinc-500 mb-4">Join or create a group to start cleaning together!</flux:text>
                    <flux:button variant="primary" wire:navigate href="{{ route('groups.create') }}">
                        Create a Group
                    </flux:button>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($userGroups as $group)
                        <a href="{{ route('groups.show', $group) }}" wire:navigate class="block">
                            <div class="flex items-center justify-between p-4 rounded-lg bg-zinc-50 dark:bg-zinc-900 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                                <div>
                                    <flux:heading size="sm">{{ $group->name }}</flux:heading>
                                    <flux:text class="text-sm text-zinc-500">{{ $group->members_count }} {{ Str::plural('member', $group->members_count) }}</flux:text>
                                </div>
                                <flux:icon.chevron-right class="size-5 text-zinc-400" />
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
