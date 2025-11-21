@props(['dirtiness' => 0])

<div class="flex items-center justify-center">
    <div class="relative w-32 h-32">
        {{-- Background circle --}}
        <svg class="transform -rotate-90 w-32 h-32">
            <circle
                cx="64"
                cy="64"
                r="56"
                stroke="currentColor"
                stroke-width="8"
                fill="none"
                class="text-zinc-200 dark:text-zinc-700"
            />
            {{-- Progress circle --}}
            <circle
                cx="64"
                cy="64"
                r="56"
                stroke="currentColor"
                stroke-width="8"
                fill="none"
                class="{{ $dirtiness >= 100 ? 'text-red-500' : ($dirtiness >= 80 ? 'text-yellow-500' : 'text-green-500') }}"
                stroke-dasharray="{{ 2 * pi() * 56 }}"
                stroke-dashoffset="{{ 2 * pi() * 56 * (1 - min($dirtiness, 100) / 100) }}"
                stroke-linecap="round"
            />
        </svg>
        {{-- Percentage text --}}
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="text-center">
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    {{ round($dirtiness) }}%
                </div>
                <div class="text-xs text-zinc-500 dark:text-zinc-400">
                    @if ($dirtiness >= 100)
                        Overdue
                    @elseif ($dirtiness >= 80)
                        Needs Attention
                    @elseif ($dirtiness < 20)
                        Fresh
                    @else
                        Clean
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
