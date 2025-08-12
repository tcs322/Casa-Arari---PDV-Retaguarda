<?php

namespace App\DTO\Auth;

use App\Http\Requests\Auth\AuthLoginRequest;

class AuthLoginDTO {
    public function __construct(
        public string $email,
        public string $password
    ) {}

    public static function makeFromRequest(AuthLoginRequest $request): self
    {
        return new self(
            $request->email,
            $request->password
        );
    }
}
