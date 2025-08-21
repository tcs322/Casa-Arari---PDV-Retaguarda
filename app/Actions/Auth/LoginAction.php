<?php

namespace App\Actions\Auth;

use App\Enums\SituacaoUsuarioEnum;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LoginAction {

    public function __construct(

    ) { }

    public function exec(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $credentials['situacao'] = SituacaoUsuarioEnum::ATIVO()->value;
        
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard.index'));
        }

        return back()->withErrors([
            'email' => 'Credenciais inválidas ou usuário inativo.',
        ]);
    }
}
