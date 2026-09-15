<?php

namespace Tests\Feature\Orders;

use App\Models\Tenant;
use App\Services\TenantSequenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class OrderSequenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sequence_starts_at_one_and_increments_per_key(): void
    {
        $tenant = Tenant::factory()->create(['slug' => 'sequence']);
        $tenant->makeCurrent();
        $service = app(TenantSequenceService::class);

        $this->assertSame(1, $service->next('orders'));
        $this->assertSame(2, $service->next('orders'));
        $this->assertSame(1, $service->next('quotes'));
    }

    public function test_same_key_is_isolated_between_tenants(): void
    {
        $tenantA = Tenant::factory()->create(['slug' => 'sequence-a']);
        $tenantB = Tenant::factory()->create(['slug' => 'sequence-b']);
        $service = app(TenantSequenceService::class);

        $tenantA->makeCurrent();
        $this->assertSame(1, $service->next('orders'));
        $tenantB->makeCurrent();
        $this->assertSame(1, $service->next('orders'));
    }

    public function test_sequence_requires_current_tenant_and_key(): void
    {
        $this->expectException(LogicException::class);
        app(TenantSequenceService::class)->next('');
    }
}
