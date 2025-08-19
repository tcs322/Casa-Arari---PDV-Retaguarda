<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LoginAction;
use App\Actions\Auth\LogoutAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct(
        protected LoginAction $loginAction,
        protected LogoutAction $logoutAction
    ) {}

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        return $this->loginAction->exec($request);
    }

    public function logout(Request $request)
    {
        return $this->logoutAction->exec($request);
    }
}
