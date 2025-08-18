<?php

namespace App\DTO\Auth;

use App\Http\Requests\Auth\ChangePasswordRequest;

class ChangePasswordDTO {
    public function __construct(
        public string $password
    ) {}

    public static function makeFromRequest(ChangePasswordRequest $request): self
    {
        return new self(
            $request->password
        );
    }
}
