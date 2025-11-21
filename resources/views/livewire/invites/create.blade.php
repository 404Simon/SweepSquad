<?php

use App\Actions\Invites\CreateInviteAction;
use App\InviteType;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public Group $group;
    public string $type = 'permanent';
    public ?string $expiresInDays = null;

    public function mount(Group $group): void
    {
        $this->group = $group;
    }

    /**
     * Create a new invite.
     */
    public function createInvite(CreateInviteAction $action): void
    {
        $validated = $this->validate([
            'type' => ['required', 'string', 'in:permanent,single_use,time_limited'],
            'expiresInDays' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        $inviteType = InviteType::from($validated['type']);
        $expiresAt = null;

        if ($inviteType === InviteType::TimeLimited && $validated['expiresInDays']) {
            $expiresAt = now()->addDays((int) $validated['expiresInDays']);
        }

        $invite = $action->handle($this->group, Auth::user(), $inviteType, $expiresAt);

        $this->dispatch('invite-created', inviteId: $invite->id);

        session()->flash('success', 'Invite created successfully!');

        $this->reset(['type', 'expiresInDays']);
    }

    public function with(): array
    {
        return [
            'inviteTypes' => [
                'permanent' => 'Permanent (never expires)',
                'single_use' => 'Single Use (one person)',
                'time_limited' => 'Time Limited',
            ],
        ];
    }
}; ?>

<div>
    <flux:modal name="create-invite" class="w-full max-w-md">
        <form wire:submit="createInvite" class="space-y-6">
            <div>
                <flux:heading size="lg">Create Invite Link</flux:heading>
                <flux:text>Generate a link to invite people to {{ $group->name }}</flux:text>
            </div>

            <flux:field>
                <flux:label for="type">Invite Type</flux:label>
                <flux:select id="type" wire:model.live="type">
                    @foreach ($inviteTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </flux:select>
                @error('type')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </flux:field>

            @if ($type === 'time_limited')
                <flux:field>
                    <flux:label for="expiresInDays">Expires In (Days)</flux:label>
                    <flux:input
                        id="expiresInDays"
                        wire:model="expiresInDays"
                        type="number"
                        min="1"
                        max="365"
                        placeholder="7"
                    />
                    @error('expiresInDays')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>
            @endif

            <div class="flex gap-3">
                <flux:button type="submit" variant="primary">
                    Create Invite
                </flux:button>
                <flux:button type="button" variant="ghost" x-on:click="$flux.modal('create-invite').close()">
                    Cancel
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
