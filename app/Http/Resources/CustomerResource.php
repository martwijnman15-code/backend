<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'contact_name' => $this->contact_name,
            'phone_number' => $this->phone_number,
            'mail_address' => $this->mail_address,
            'company_name' => $this->company_name,
            'created_at' => $this->created_at,
        ];
    }
}
