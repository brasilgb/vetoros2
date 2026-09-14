<?php

use App\Models\Tenant;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'company_name' => 'Test Company',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertDatabaseCount('tenants', 1);
    $this->assertDatabaseCount('companies', 1);
    $this->assertDatabaseHas('companies', [
        'trade_name' => 'Test Company',
        'type' => 'headquarters',
        'parent_id' => null,
    ]);
    $this->assertDatabaseHas('company_user', [
        'is_default' => true,
    ]);
});

test('registration always creates a tenant-owned headquarters', function () {
    $this->post(route('register.store'), [
        'name' => 'Another User',
        'company_name' => 'Another Company',
        'email' => 'another@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $tenant = Tenant::query()->where('name', 'Another Company')->first();

    expect($tenant)->not->toBeNull();
    $this->assertDatabaseHas('companies', [
        'tenant_id' => $tenant->id,
        'trade_name' => 'Another Company',
        'type' => 'headquarters',
    ]);
});
