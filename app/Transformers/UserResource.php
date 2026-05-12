<?php

namespace Modules\Identity\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        $data = [
            'id'         => $this->id,
            'name'       => $this->name,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'status'     => $this->status,
            'avatar_url' => $this->avatar?->url,
            'created_at' => $this->created_at?->toISOString(),
        ];

        // Only include uuid if enabled in config
        if (config('identity.enable_uuid')) {
            $data['uuid'] = $this->uuid;
        }

        // Only include username if enabled in config
        if (config('identity.enable_username')) {
            $data['username'] = $this->username;
        }

        return $data;
    }
}