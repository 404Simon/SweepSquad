<?php

use App\Achievement;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        /** @var User $user */
        $user = Auth::user();

        // Get all achievements
        $allAchievements = Achievement::cases();

        // Get earned achievement codes
        $earnedCodes = $user->earnedAchievements();

        // Group achievements by category
        $groupedAchievements = collect($allAchievements)->groupBy(fn($achievement) => $achievement->category());

        return [
            'groupedAchievements' => $groupedAchievements,
            'earnedCodes' => $earnedCodes,
        ];
    }
}; ?>

<div>
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Your Achievements</flux:heading>
            <flux:text>Unlock achievements by completing various tasks</flux:text>
        </div>

        @foreach ($groupedAchievements as $category => $achievements)
            <div>
                <flux:heading size="md" class="mb-4">{{ $category }}</flux:heading>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($achievements as $achievement)
                        @php
                            $isEarned = in_array($achievement->value, $earnedCodes);
                        @endphp
                        <div class="rounded-lg border p-4 transition-all {{ $isEarned ? 'border-green-500 bg-green-50 dark:border-green-700 dark:bg-green-900/20' : 'border-neutral-200 bg-white opacity-60 dark:border-neutral-700 dark:bg-neutral-800' }}">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <flux:icon icon="{{ $achievement->icon() }}" class="size-8 {{ $isEarned ? 'text-green-600 dark:text-green-400' : 'text-neutral-400' }}" />
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <flux:heading size="sm">{{ $achievement->name() }}</flux:heading>
                                        @if ($isEarned)
                                            <flux:icon.check-badge class="size-5 text-green-600 dark:text-green-400" />
                                        @endif
                                    </div>
                                    <flux:text class="mt-1 text-sm">{{ $achievement->description() }}</flux:text>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
