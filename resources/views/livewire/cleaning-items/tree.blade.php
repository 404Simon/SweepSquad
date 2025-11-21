<?php

use App\Models\CleaningItem;
use App\Models\Group;
use Livewire\Volt\Component;

use function Livewire\Volt\{layout, state, mount, computed};

layout('components.layouts.app');

state(['groupId', 'group']);

mount(function (int $groupId) {
    $this->groupId = $groupId;
    $this->group = Group::query()->findOrFail($groupId);
});

$rootItems = computed(fn () => CleaningItem::query()
    ->where('group_id', $this->groupId)
    ->roots()
    ->orderBy('order')
    ->with(['children'])
    ->get()
);

new class extends Component {
    public function renderItem(CleaningItem $item, int $level = 0): string
    {
        $indent = str_repeat('pl-6 ', $level);
        $dirtiness = $item->cleaning_frequency_hours ? round($item->dirtiness_percentage) : null;
        $badgeColor = '';
        
        if ($dirtiness !== null) {
            if ($item->is_overdue) {
                $badgeColor = 'danger';
            } elseif ($item->needs_attention) {
                $badgeColor = 'warning';
            } else {
                $badgeColor = 'success';
            }
        }
        
        $html = '<div class="' . $indent . '">';
        $html .= '<a href="' . route('cleaning-items.show', $item->id) . '" wire:navigate class="block p-4 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition mb-2">';
        $html .= '<div class="flex items-center justify-between">';
        $html .= '<div><div class="font-medium">' . e($item->name) . '</div>';
        
        if ($dirtiness !== null) {
            $html .= '<div class="text-sm text-zinc-500 dark:text-zinc-400">Dirtiness: ' . $dirtiness . '%</div>';
        }
        
        $html .= '</div>';
        
        if ($dirtiness !== null) {
            $html .= '<span class="badge badge-' . $badgeColor . '">' . $item->coins_available . ' coins</span>';
        }
        
        $html .= '</div></a>';
        
        foreach ($item->children()->orderBy('order')->get() as $child) {
            $html .= $this->renderItem($child, $level + 1);
        }
        
        $html .= '</div>';
        
        return $html;
    }
}; ?>

<div>
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="mb-6 flex items-center justify-between">
            <flux:heading size="lg">{{ $group->name }} - Cleaning Items</flux:heading>
            <flux:button
                variant="primary"
                wire:navigate
                href="{{ route('cleaning-items.create', ['groupId' => $groupId]) }}"
            >
                Add Root Item
            </flux:button>
        </div>

        @if ($this->rootItems->count() > 0)
            <div class="space-y-2">
                @foreach ($this->rootItems as $item)
                    {!! $this->renderItem($item) !!}
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <flux:text class="text-zinc-500 dark:text-zinc-400">
                    No cleaning items yet. Create one to get started!
                </flux:text>
            </div>
        @endif
    </div>
</div>
