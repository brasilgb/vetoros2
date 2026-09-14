<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('creates a global root admin without tenant or company access', function () {
    $this->artisan('root-admin:ensure', [
        '--name' => 'Root Admin',
        '--email' => 'admin@example.com',
    ])
        ->expectsQuestion('Password', 'secure-password')
        ->assertExitCode(0);

    $user = User::query()->where('email', 'admin@example.com')->firstOrFail();

    expect($user->tenant_id)->toBeNull()
        ->and($user->is_root_admin)->toBeTrue()
        ->and(Hash::check('secure-password', $user->password))->toBeTrue();

    $this->assertDatabaseMissing('company_user', ['user_id' => $user->id]);
});

test('is idempotent for an existing root admin', function () {
    $user = User::factory()->create([
        'email' => 'admin@example.com',
    ]);
    $user->forceFill(['is_root_admin' => true])->save();

    $this->artisan('root-admin:ensure', [
        '--name' => 'Updated Root Admin',
        '--email' => 'admin@example.com',
    ])
        ->expectsQuestion('Password (leave empty to keep current)', '')
        ->assertExitCode(0);

    expect(User::query()->whereKey($user->id)->value('name'))->toBe('Updated Root Admin');
    $this->assertDatabaseCount('users', 1);
});

test('does not silently promote a tenant user', function () {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create([
        'email' => 'member@example.com',
        'tenant_id' => $tenant->id,
    ]);

    $this->artisan('root-admin:ensure', [
        '--name' => 'Member',
        '--email' => $user->email,
    ])->assertExitCode(1);

    expect($user->fresh()->is_root_admin)->toBeFalse();
});

test('rejects an invalid email', function () {
    $this->artisan('root-admin:ensure', [
        '--name' => 'Root Admin',
        '--email' => 'not-an-email',
    ])->assertExitCode(1);
});
