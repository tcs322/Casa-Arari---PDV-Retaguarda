<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AuthLoginAction;
use App\DTO\Auth\AuthLoginDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthLoginRequest;
use Illuminate\Support\Facades\Auth;

class AuthLoginController extends Controller
{
    public function __construct(
        protected  AuthLoginAction $authLoginAction
    ) {

    }

    public function login(AuthLoginRequest $authLoginRequest)
    {
        if($this->authLoginAction->exec(
            AuthLoginDTO::makeFromRequest($authLoginRequest),
            $authLoginRequest
        )) {
            return redirect()->route('dashboard.index');
        }

        return redirect()->route('auth.index')->withErrors([
            'email' => 'Credenciais incorretas'
        ]);
    }

    public function index()
    {
        if(Auth::check()) {
            return redirect()->route('dashboard.index');
        }

        return view('auth.login');
    }
}
