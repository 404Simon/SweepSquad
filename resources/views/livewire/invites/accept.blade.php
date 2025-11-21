<?php

use App\Actions\Invites\AcceptInviteAction;
use App\Models\GroupInvite;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

use function Livewire\Volt\{layout, state};

layout('components.layouts.app');

state(['code']);

new class extends Component {
    public string $code;
    public ?GroupInvite $invite = null;
    public ?string $errorMessage = null;

    public function mount(string $code): void
    {
        $this->code = $code;
        $this->invite = GroupInvite::where('code', $code)
            ->with(['group.members', 'creator'])
            ->first();

        // Check if invite exists and is valid
        if ($this->invite === null) {
            $this->errorMessage = 'Invalid invite code.';
        } elseif (! $this->invite->isValid()) {
            if ($this->invite->used_at !== null) {
                $this->errorMessage = 'This invite has already been used.';
            } else {
                $this->errorMessage = 'This invite has expired.';
            }
        }

        // Check if user is already a member
        if ($this->invite && Auth::check() && $this->invite->group->members()->where('user_id', Auth::id())->exists()) {
            $this->errorMessage = 'You are already a member of this group.';
        }
    }

    /**
     * Accept the invite.
     */
    public function acceptInvite(AcceptInviteAction $action): void
    {
        if (! Auth::check()) {
            session()->put('invite_code', $this->code);
            $this->redirect(route('login'), navigate: true);
            return;
        }

        if ($this->invite === null || ! $this->invite->isValid()) {
            return;
        }

        try {
            $action->handle($this->invite, Auth::user());

            session()->flash('success', 'Successfully joined the group!');

            $this->redirect(route('groups.show', $this->invite->group), navigate: true);
        } catch (\InvalidArgumentException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }
}; ?>

<div>
    <div class="max-w-2xl mx-auto px-4 py-8">
        @if ($errorMessage)
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6">
                <flux:heading size="lg" class="mb-2">Invalid Invite</flux:heading>
                <flux:text>{{ $errorMessage }}</flux:text>
                <div class="mt-4">
                    <flux:button variant="primary" wire:navigate href="{{ route('groups.index') }}">
                        View My Groups
                    </flux:button>
                </div>
            </div>
        @elseif ($invite)
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-6">
                <flux:heading size="lg" class="mb-4">You're Invited!</flux:heading>

                <div class="space-y-4">
                    <div>
                        <flux:text class="text-zinc-600 dark:text-zinc-400">
                            {{ $invite->creator->name }} has invited you to join:
                        </flux:text>
                        <flux:heading size="md" class="mt-2">{{ $invite->group->name }}</flux:heading>
                        @if ($invite->group->description)
                            <flux:text class="mt-2">{{ $invite->group->description }}</flux:text>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                        <flux:badge variant="primary">
                            {{ str_replace('_', ' ', ucfirst($invite->type->value)) }}
                        </flux:badge>
                        @if ($invite->expires_at)
                            <span>· Expires {{ $invite->expires_at->diffForHumans() }}</span>
                        @endif
                    </div>

                    @if ($invite->group->members && $invite->group->members->isNotEmpty())
                        <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
                            <flux:text class="font-semibold mb-2">Group Members ({{ $invite->group->members->count() }})</flux:text>
                            <div class="space-y-2">
                                @foreach ($invite->group->members as $member)
                                    <div class="flex items-center gap-2">
                                        <flux:avatar size="sm" />
                                        <flux:text>{{ $member->name }}</flux:text>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex gap-3 pt-4">
                        <flux:button 
                            type="button" 
                            variant="primary"
                            wire:click="acceptInvite"
                        >
                            Join Group
                        </flux:button>
                        <flux:button 
                            type="button" 
                            variant="ghost"
                            wire:navigate
                            href="{{ route('dashboard') }}"
                        >
                            Decline
                        </flux:button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
