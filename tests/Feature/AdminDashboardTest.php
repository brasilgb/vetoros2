<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to login from the admin dashboard', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

test('regular users cannot access the admin dashboard', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('root admins can access the dashboard without tenant context', function () {
    $user = User::factory()->create([
        'name' => 'Root Admin',
        'is_root_admin' => true,
    ]);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/dashboard')
            ->where('auth.user.name', 'Root Admin')
            ->where('tenant', null)
            ->where('currentCompany', null)
        );
});
