<?php

namespace Modules\Identity\DTOs;

use Illuminate\Http\Request;

class UserData
{
    public function __construct(
        public ?string $name = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $username = null,
        public ?string $status = null,
        public ?string $password = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: $request->has('name') ? $request->name : null,
            firstName: $request->has('first_name') ? $request->first_name : null,
            lastName: $request->has('last_name') ? $request->last_name : null,
            email: $request->has('email') ? $request->email : null,
            phone: $request->has('phone') ? $request->phone : null,
            username: $request->has('username') ? $request->username : null,
            status: $request->has('status') ? $request->status : null, // Will be handled in Action
            password: $request->filled('password') ? $request->password : null,
        );
    }

    public function toUpdateArray(): array
    {
        $map = [
            'name'       => $this->name,
            'first_name' => $this->firstName,
            'last_name'  => $this->lastName,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'username'   => $this->username,
            'status'     => $this->status,
            'password'   => $this->password,
        ];

        return collect($map)
            ->filter(function ($value, $key) {
                if ($key === 'status' && $value === null) {
                    return false;
                }

                return $value !== null;
            })
            ->all();
    }
}