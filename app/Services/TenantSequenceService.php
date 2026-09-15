<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantSequence;
use Illuminate\Support\Facades\DB;
use LogicException;

class TenantSequenceService
{
    public function next(string $key): int
    {
        $tenant = Tenant::current();
        if (! $tenant || trim($key) === '') {
            throw new LogicException('A current tenant and non-empty sequence key are required.');
        }

        return DB::transaction(function () use ($tenant, $key): int {
            DB::table('tenant_sequences')->insertOrIgnore([
                'tenant_id' => $tenant->getKey(),
                'key' => $key,
                'current_value' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $sequence = TenantSequence::withoutGlobalScopes()
                ->where('tenant_id', $tenant->getKey())
                ->where('key', $key)
                ->lockForUpdate()
                ->firstOrFail();
            $sequence->current_value++;
            $sequence->save();

            return $sequence->current_value;
        });
    }
}
