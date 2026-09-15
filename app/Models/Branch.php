<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use App\Enums\CompanyType;
use Database\Factories\BranchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class Branch extends Model
{
    /** @use HasFactory<BranchFactory> */
    use BelongsToTenant, HasFactory;

    protected $fillable = ['company_id', 'name', 'active'];

    protected $attributes = ['active' => true];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (Branch $branch): void {
            $company = Company::withoutGlobalScopes()->find($branch->company_id);
            $companyType = $company?->getAttribute('type');
            $companyType = $companyType instanceof CompanyType
                ? $companyType
                : CompanyType::tryFrom((string) $companyType);

            if (! $company || (int) $company->tenant_id !== (int) $branch->tenant_id || $companyType !== CompanyType::HEADQUARTERS) {
                throw new LogicException('Branch must belong to a headquarters in the current tenant.');
            }
        });
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'branch_user')
            ->withPivot('tenant_id')
            ->withTimestamps();
    }

    /** @return HasMany<Order, $this> */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
