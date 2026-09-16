<?php

namespace App\Models;

use App\Enums\CompanyType;
use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    /** @return HasMany<Company, $this> */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    /** @return HasMany<Branch, $this> */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    /** @return HasMany<OrderAssignmentHistory, $this> */
    public function orderAssignmentHistories(): HasMany
    {
        return $this->hasMany(OrderAssignmentHistory::class);
    }

    /** @return HasMany<User, $this> */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** @return HasOne<Company, $this> */
    public function headquarters(): HasOne
    {
        return $this->hasOne(Company::class)
            ->where('type', CompanyType::HEADQUARTERS);
    }

    /** @return HasMany<Customer, $this> */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /** @return HasMany<EquipmentType, $this> */
    public function equipmentTypes(): HasMany
    {
        return $this->hasMany(EquipmentType::class);
    }

    /** @return HasMany<CustomerEquipment, $this> */
    public function customerEquipments(): HasMany
    {
        return $this->hasMany(CustomerEquipment::class);
    }

    /** @return HasMany<ChecklistTemplate, $this> */
    public function checklistTemplates(): HasMany
    {
        return $this->hasMany(ChecklistTemplate::class);
    }

    /** @return HasMany<Order, $this> */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** @return HasMany<TenantSequence, $this> */
    public function sequences(): HasMany
    {
        return $this->hasMany(TenantSequence::class);
    }

    /** @return HasMany<BudgetTemplate, $this> */
    public function budgetTemplates(): HasMany
    {
        return $this->hasMany(BudgetTemplate::class);
    }

    /** @return HasMany<Budget, $this> */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }
}
