<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
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
            'slug' => $this->slug,
            'tax_id' => $this->tax_id,
            'address' => $this->address,
            'city' => $this->city,
            'zip' => $this->zip,
            'created_at' => $this->created_at->format('j/M/Y H:i'),
            'updated_at' => $this->updated_at->format('j/M/Y H:i'),
        ];
    }
}
