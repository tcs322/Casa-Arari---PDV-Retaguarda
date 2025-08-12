<?php

namespace App\Actions\Auth;

use App\DTO\Auth\AuthLoginDTO;
use App\Http\Requests\Auth\AuthLoginRequest;
use Exception;
use Illuminate\Support\Facades\Auth;

class AuthLoginAction {

    public function __construct(

    ) { }

    public function exec(
        AuthLoginDTO $dto,
        AuthLoginRequest $authLoginRequest
        ): bool
    {
        if(Auth::check()) {
            return true;
        }

        if (Auth::attempt((array)$dto)) {
            return $authLoginRequest->session()->regenerate();
        }

        return false;
    }
}
