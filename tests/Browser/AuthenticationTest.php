<?php

declare(strict_types=1);

namespace Tests\Browser;

test('user can register', function () {
    visit('/')
        ->assertNoSmoke()
        ->click('Log in')
        ->assertPathIs('/login')
        ->assertSee("Don't have an account?")
        ->click('Sign up')
        ->fill('name', 'Simon')
        ->fill('email', 'leak@me.de')
        ->fill('password', 'thisisnotsecure')
        ->fill('password_confirmation', 'thisisnotsecure')
        ->click('Create account')
        ->assertPathIs('/dashboard');
});

test('guests cannot access dashboard', function () {
    visit('/dashboard')
        ->assertPathIs('/login');
});
