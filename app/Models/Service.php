<?php

namespace App\Models;

use App\Enums\BillingCycle;
use App\Enums\ServiceStatus;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $customer_id
 * @property string $name
 * @property string|null $description
 * @property string $price
 * @property string $currency
 * @property BillingCycle $billing_cycle
 * @property ServiceStatus $status
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Customer|null $customer
 */
#[Fillable(['customer_id', 'name', 'description', 'price', 'currency', 'billing_cycle', 'status', 'starts_at', 'ends_at'])]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory, SoftDeletes;

    /**
     * The model's default attribute values, mirroring the schema defaults.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'price' => 0,
        'currency' => 'USD',
        'billing_cycle' => BillingCycle::Monthly->value,
        'status' => ServiceStatus::Pending->value,
    ];

    /**
     * Get the customer that owns the service.
     *
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Scope the query to services matching the given free text search.
     *
     * @param  Builder<Service>  $query
     */
    public function scopeSearch(Builder $query, string $term): void
    {
        $query->where(function (Builder $query) use ($term): void {
            $query->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * Get the total amount the service bills over a single year.
     */
    public function annualValue(): float
    {
        return round((float) $this->price * $this->billing_cycle->occurrencesPerYear(), 2);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'billing_cycle' => BillingCycle::class,
            'status' => ServiceStatus::class,
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }
}
