<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use App\Enums\CompanyType;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use BelongsToTenant, HasFactory;

    protected $attributes = [
        'type' => CompanyType::HEADQUARTERS->value,
        'is_active' => true,
    ];

    protected $fillable = [
        'parent_id',
        'type',
        'legal_name',
        'trade_name',
        'cnpj',
        'state_registration',
        'email',
        'phone',
        'whatsapp',
        'zip_code',
        'street',
        'number',
        'complement',
        'district',
        'city',
        'state',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => CompanyType::class,
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Company $company): void {
            $company->ensureValidHierarchy();
        });
    }

    /** @return BelongsTo<Tenant, $this> */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /** @return BelongsTo<Company, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            Company::class,
            'parent_id'
        );
    }

    /** @return HasMany<Company, $this> */
    public function branches(): HasMany
    {
        return $this->hasMany(
            Company::class,
            'parent_id'
        )->where('type', CompanyType::BRANCH);
    }

    /** @return HasMany<Branch, $this> */
    public function operationalBranches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot([
                'tenant_id',
                'is_default',
            ])
            ->withTimestamps();
    }

    /** @return HasMany<Order, $this> */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @param  Builder<Company>  $query
     * @return Builder<Company>
     */
    public function scopeHeadquarters(Builder $query): Builder
    {
        return $query->where(
            'type',
            CompanyType::HEADQUARTERS
        );
    }

    /**
     * @param  Builder<Company>  $query
     * @return Builder<Company>
     */
    public function scopeBranches(Builder $query): Builder
    {
        return $query->where(
            'type',
            CompanyType::BRANCH
        );
    }

    private function ensureValidHierarchy(): void
    {
        $typeAttribute = $this->getAttributeFromArray('type');
        $type = $typeAttribute instanceof CompanyType
            ? $typeAttribute
            : CompanyType::tryFrom((string) $typeAttribute);

        if ($type === null) {
            throw new LogicException('Company type is invalid.');
        }

        if ($type === CompanyType::HEADQUARTERS && $this->parent_id !== null) {
            throw new LogicException('A headquarters cannot have a parent company.');
        }

        if ($type === CompanyType::BRANCH && $this->parent_id === null) {
            throw new LogicException('A branch must belong to a headquarters.');
        }

        if ($type === CompanyType::HEADQUARTERS) {
            $query = static::query()->headquarters();

            if ($this->exists) {
                $query->where(
                    $query->getModel()->qualifyColumn('id'),
                    '!=',
                    $this->getKey(),
                );
            }

            if ($query->exists()) {
                throw new LogicException('A tenant can have only one headquarters.');
            }

            return;
        }

        $parent = static::query()->find($this->parent_id);

        $parentType = $parent?->getAttributeFromArray('type');

        if (! $parent || $parentType !== CompanyType::HEADQUARTERS->value) {
            throw new LogicException('A branch must belong to a headquarters in the current tenant.');
        }

        if (! $parent->is_active) {
            throw new LogicException('A branch must belong to an active headquarters.');
        }

        if ($this->exists && (int) $parent->getKey() === (int) $this->getKey()) {
            throw new LogicException('A company cannot be its own parent.');
        }
    }
}
