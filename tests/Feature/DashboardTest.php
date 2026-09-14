<?php

use App\Models\Company;
use App\Models\Tenant;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $tenant = Tenant::create([
        'name' => 'Tenant A',
        'slug' => 'tenant-a',
        'active' => true,
    ]);
    $tenant->makeCurrent();
    $user = User::factory()->create(['tenant_id' => $tenant->id]);
    $company = Company::create(['trade_name' => 'Empresa A']);
    $user->companies()->attach($company, [
        'tenant_id' => $tenant->id,
        'is_default' => true,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});
