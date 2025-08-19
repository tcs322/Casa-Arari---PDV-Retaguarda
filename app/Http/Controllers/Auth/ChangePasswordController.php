<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ChangePasswordAction;
use App\DTO\Auth\ChangePasswordDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;

class ChangePasswordController extends Controller
{
    public function __construct(
        protected ChangePasswordAction $action
    ) {}

    public function showForm()
    {
        return view('auth.password_change');
    }

    public function update(ChangePasswordRequest $request)
    {
        $this->action->exec(ChangePasswordDTO::makeFromRequest($request));

        return redirect()->route('dashboard.index')
            ->with('status', 'Senha alterada com sucesso!');
    }
}
