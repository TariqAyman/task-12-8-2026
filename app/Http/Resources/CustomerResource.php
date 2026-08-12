<?php

namespace App\Http\Resources;

use App\Models\Customer;
use App\OpenApi\Schemas\CustomerSchema;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Customer
 *
 * @see CustomerSchema for the documented representation
 */
class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company' => $this->company,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'status' => $this->status->value,
            'notes' => $this->notes,
            'services_count' => $this->whenCounted('services'),
            'services' => ServiceResource::collection($this->whenLoaded('services')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
