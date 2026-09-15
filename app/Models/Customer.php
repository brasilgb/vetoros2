<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use BelongsToTenant, HasFactory;

    protected $attributes = ['type' => 'individual', 'is_active' => true];

    protected $fillable = [
        'customer_number', 'type', 'name', 'legal_name', 'trade_name', 'cpf', 'cnpj',
        'state_registration', 'municipal_registration', 'birth_date', 'email', 'phone',
        'mobile', 'whatsapp', 'contact_name', 'contact_phone', 'contact_email',
        'contact_whatsapp', 'zip_code', 'state', 'city', 'district', 'street',
        'number', 'complement', 'observations', 'is_active',
    ];

    protected function casts(): array
    {
        return ['birth_date' => 'date', 'is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (Customer $customer): void {
            if (! in_array($customer->type, ['individual', 'company'], true)) {
                throw new InvalidArgumentException('Customer type is invalid.');
            }
        });
    }

    public function setCpfAttribute(?string $value): void
    {
        $this->attributes['cpf'] = $this->digitsOnly($value);
    }

    public function setCnpjAttribute(?string $value): void
    {
        $this->attributes['cnpj'] = $this->digitsOnly($value);
    }

    public function setZipCodeAttribute(?string $value): void
    {
        $this->attributes['zip_code'] = $this->digitsOnly($value);
    }

    public function setPhoneAttribute(?string $value): void
    {
        $this->attributes['phone'] = $this->digitsOnly($value);
    }

    public function setMobileAttribute(?string $value): void
    {
        $this->attributes['mobile'] = $this->digitsOnly($value);
    }

    public function setWhatsappAttribute(?string $value): void
    {
        $this->attributes['whatsapp'] = $this->digitsOnly($value);
    }

    public function setContactPhoneAttribute(?string $value): void
    {
        $this->attributes['contact_phone'] = $this->digitsOnly($value);
    }

    public function setContactWhatsappAttribute(?string $value): void
    {
        $this->attributes['contact_whatsapp'] = $this->digitsOnly($value);
    }

    /** @return HasMany<CustomerEquipment, $this> */
    public function customerEquipments(): HasMany
    {
        return $this->hasMany(CustomerEquipment::class);
    }

    /** @return HasMany<Order, $this> */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    private function digitsOnly(?string $value): ?string
    {
        return $value === null ? null : preg_replace('/\D+/', '', $value);
    }
}
