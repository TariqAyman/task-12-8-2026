<?php

namespace App\Http\Resources;

use App\Support\Auth\IssuedToken;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin IssuedToken
 */
class TokenResource extends JsonResource
{
    /**
     * The "data" wrapper is omitted so the token stays at the top level.
     *
     * @var string|null
     */
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'access_token' => $this->plainTextToken,
            'token_type' => 'bearer',
            'expires_in' => $this->expiresIn,
        ];
    }
}
