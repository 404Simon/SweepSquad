<?php

use App\Actions\Invites\RevokeInviteAction;
use App\Models\Group;
use App\Models\GroupInvite;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    public Group $group;

    public function mount(Group $group): void
    {
        $this->group = $group;
    }

    #[On('invite-created')]
    public function refreshInvites(): void
    {
        // This will trigger a re-render
    }

    /**
     * Revoke an invite.
     */
    public function revokeInvite(int $inviteId, RevokeInviteAction $action): void
    {
        $invite = GroupInvite::findOrFail($inviteId);

        // Verify the invite belongs to this group
        if ($invite->group_id !== $this->group->id) {
            abort(403);
        }

        $action->handle($invite);

        session()->flash('success', 'Invite revoked successfully!');
    }

    /**
     * Copy invite link to clipboard.
     */
    public function copyInviteLink(string $uuid): void
    {
        $this->dispatch('copy-to-clipboard', url: route('invites.accept', $uuid));
    }

    public function with(): array
    {
        return [
            'invites' => $this->group->invites()
                ->with('creator')
                ->latest()
                ->get(),
        ];
    }
}; ?>

<div class="space-y-4">
    <div class="flex items-center justify-between">
        <flux:heading size="md">Invite Links</flux:heading>
        <flux:button 
            variant="primary" 
            size="sm"
            x-on:click="$flux.modal('create-invite').show()"
        >
            Create Invite
        </flux:button>
    </div>

    @if ($invites->isEmpty())
        <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
            <p>No invite links yet. Create one to invite people to your group!</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($invites as $invite)
                <div class="flex items-center justify-between p-4 border rounded-lg dark:border-zinc-700">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <flux:badge variant="{{ $invite->isValid() ? 'primary' : 'default' }}">
                                {{ str_replace('_', ' ', ucfirst($invite->type->value)) }}
                            </flux:badge>
                            @if (!$invite->isValid())
                                <flux:badge variant="danger">
                                    @if ($invite->used_at)
                                        Used
                                    @else
                                        Expired
                                    @endif
                                </flux:badge>
                            @endif
                        </div>
                        <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                            Created by {{ $invite->creator->name }}
                            @if ($invite->expires_at)
                                · Expires {{ $invite->expires_at->diffForHumans() }}
                            @endif
                            @if ($invite->used_at)
                                · Used {{ $invite->used_at->diffForHumans() }}
                            @endif
                        </div>
                        @if ($invite->isValid())
                            <div class="mt-2">
                                <code class="text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">
                                    {{ route('invites.accept', $invite->uuid) }}
                                </code>
                            </div>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($invite->isValid())
                            <flux:button 
                                variant="ghost" 
                                size="sm"
                                wire:click="copyInviteLink('{{ $invite->uuid }}')"
                            >
                                Copy Link
                            </flux:button>
                        @endif
                        <flux:button 
                            variant="ghost" 
                            size="sm"
                            wire:click="revokeInvite({{ $invite->id }})"
                            wire:confirm="Are you sure you want to revoke this invite?"
                        >
                            Revoke
                        </flux:button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('copy-to-clipboard', (event) => {
            navigator.clipboard.writeText(event.url).then(() => {
                alert('Invite link copied to clipboard!');
            });
        });
    });
</script>

    @if ($invites->isEmpty())
        <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
            <p>No invite links yet. Create one to invite people to your group!</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($invites as $invite)
                <div class="flex items-center justify-between p-4 border rounded-lg dark:border-zinc-700">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <flux:badge variant="{{ $invite->isValid() ? 'primary' : 'default' }}">
                                {{ str_replace('_', ' ', ucfirst($invite->type->value)) }}
                            </flux:badge>
                            @if (!$invite->isValid())
                                <flux:badge variant="danger">
                                    @if ($invite->used_at)
                                        Used
                                    @else
                                        Expired
                                    @endif
                                </flux:badge>
                            @endif
                        </div>
                        <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                            Created by {{ $invite->creator->name }}
                            @if ($invite->expires_at)
                                · Expires {{ $invite->expires_at->diffForHumans() }}
                            @endif
                            @if ($invite->used_at)
                                · Used {{ $invite->used_at->diffForHumans() }}
                            @endif
                        </div>
                        @if ($invite->isValid())
                            <div class="mt-2">
                                <code class="text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">
                                    {{ route('invites.accept', $invite->uuid) }}
                                </code>
                            </div>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($invite->isValid())
                            <flux:button 
                                variant="ghost" 
                                size="sm"
                                wire:click="copyInviteLink('{{ $invite->uuid }}')"
                            >
                                Copy Link
                            </flux:button>
                        @endif
                        <flux:button 
                            variant="ghost" 
                            size="sm"
                            wire:click="revokeInvite({{ $invite->id }})"
                            wire:confirm="Are you sure you want to revoke this invite?"
                        >
                            Revoke
                        </flux:button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('copy-to-clipboard', (event) => {
            navigator.clipboard.writeText(event.url).then(() => {
                alert('Invite link copied to clipboard!');
            });
        });
    });
</script>
