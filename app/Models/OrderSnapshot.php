<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use Database\Factories\OrderSnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class OrderSnapshot extends Model
{
    /** @use HasFactory<OrderSnapshotFactory> */
    use BelongsToTenant, HasFactory;

    public $timestamps = false;

    protected $fillable = ['order_id', 'customer_data', 'equipment_data', 'company_data', 'branch_data', 'created_at'];

    protected function casts(): array
    {
        return ['customer_data' => 'array', 'equipment_data' => 'array', 'company_data' => 'array', 'branch_data' => 'array', 'created_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (OrderSnapshot $snapshot): void {
            $order = Order::withoutGlobalScopes()->find($snapshot->order_id);
            if (! $order || (int) $order->tenant_id !== (int) $snapshot->tenant_id) {
                throw new LogicException('Order snapshot must belong to an order in the current tenant.');
            }
        });
        static::updating(fn (): never => throw new LogicException('Order snapshot is immutable.'));
        static::deleting(fn (): never => throw new LogicException('Order snapshot is immutable.'));
    }

    public static function createForOrder(Order $order): self
    {
        if (static::withoutGlobalScopes()->where('tenant_id', $order->tenant_id)->where('order_id', $order->getKey())->exists()) {
            throw new LogicException('An order can have only one initial snapshot.');
        }

        $customer = $order->customer()->withoutGlobalScopes()->firstOrFail();
        $company = $order->company()->withoutGlobalScopes()->firstOrFail();
        $branch = $order->branch()->withoutGlobalScopes()->firstOrFail();
        $equipment = $order->customerEquipment()->withoutGlobalScopes()->first();
        $equipmentType = $order->equipmentType()->withoutGlobalScopes()->firstOrFail();

        return static::create([
            'tenant_id' => $order->tenant_id,
            'order_id' => $order->getKey(),
            'customer_data' => ['customer_id' => $customer->getKey(), ...self::attributes($customer, [
                'customer_number', 'type', 'name', 'legal_name', 'trade_name', 'cpf', 'cnpj',
                'state_registration', 'municipal_registration', 'birth_date', 'email', 'phone',
                'mobile', 'whatsapp', 'contact_name', 'contact_phone', 'contact_email',
                'contact_whatsapp', 'zip_code', 'state', 'city', 'district', 'street',
                'number', 'complement',
            ])],
            'equipment_data' => $equipment === null ? ['equipment_type' => self::attributes($equipmentType, ['equipment_type_number', 'name'])] : [
                'customer_equipment_id' => $equipment->getKey(),
                ...self::attributes($equipment, ['equipment_number', 'brand', 'model', 'serial_number', 'imei', 'asset_tag', 'color', 'description']),
                'equipment_type' => self::attributes($equipmentType, ['equipment_type_number', 'name']),
            ],
            'company_data' => ['company_id' => $company->getKey(), ...self::attributes($company, ['legal_name', 'trade_name', 'cnpj', 'state_registration', 'email', 'phone', 'whatsapp', 'zip_code', 'street', 'number', 'complement', 'district', 'city', 'state'])],
            'branch_data' => ['branch_id' => $branch->getKey(), ...self::attributes($branch, ['name', 'company_id', 'active'])],
        ]);
    }

    /** @return array<string, mixed> */
    /** @param array<int, string> $keys */
    private static function attributes(Model $model, array $keys): array
    {
        return collect($keys)->mapWithKeys(function (string $key) use ($model): array {
            $value = $model->getAttribute($key);

            return [$key => $value instanceof \DateTimeInterface ? $value->format('Y-m-d') : $value];
        })->all();
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
