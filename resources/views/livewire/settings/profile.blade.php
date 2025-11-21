<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                        {{ __('Save') }}
                    </flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>

    {{-- Statistics Section --}}
    <x-settings.layout :heading="__('Statistics')" :subheading="__('Your cleaning stats and achievements')">
        <div class="my-6 w-full space-y-6">
            {{-- Stats Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                    <flux:text class="text-sm text-zinc-500 mb-1">Total Coins</flux:text>
                    <flux:heading size="lg">{{ number_format(Auth::user()->total_coins) }}</flux:heading>
                </div>
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                    <flux:text class="text-sm text-zinc-500 mb-1">Current Streak</flux:text>
                    <flux:heading size="lg">{{ Auth::user()->current_streak }} day{{ Auth::user()->current_streak !== 1 ? 's' : '' }}</flux:heading>
                </div>
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                    <flux:text class="text-sm text-zinc-500 mb-1">Longest Streak</flux:text>
                    <flux:heading size="lg">{{ Auth::user()->longest_streak }} day{{ Auth::user()->longest_streak !== 1 ? 's' : '' }}</flux:heading>
                </div>
            </div>

            {{-- Achievements Section --}}
            <div>
                <flux:heading size="md" class="mb-4">Achievements</flux:heading>
                @php
                    $achievements = Auth::user()->achievements;
                @endphp
                @if($achievements->isEmpty())
                    <div class="text-center py-8 text-zinc-500">
                        <flux:text>No achievements yet. Keep cleaning to earn your first achievement!</flux:text>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($achievements as $userAchievement)
                            @php
                                $achievement = $userAchievement->achievement();
                            @endphp
                            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                                <div class="flex items-center gap-3 mb-2">
                                    <flux:icon.{{ $achievement->icon() }} class="size-8 text-yellow-500" />
                                    <div>
                                        <flux:heading size="sm">{{ $achievement->name() }}</flux:heading>
                                        <flux:text class="text-xs text-zinc-500">{{ $achievement->description() }}</flux:text>
                                    </div>
                                </div>
                                <flux:text class="text-xs text-zinc-400">Earned {{ $userAchievement->earned_at->diffForHumans() }}</flux:text>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </x-settings.layout>
</section>
