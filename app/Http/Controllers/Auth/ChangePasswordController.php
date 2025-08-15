<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Usuario\UsuarioUpdateAction;
use App\Enums\MustChangePasswordEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    public function __construct(
        protected UsuarioUpdateAction $usuarioUpdateAction
    ) {}

    public function showForm()
    {
        return view('auth.password_change');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = Auth::user();
        
        $user->password = Hash::make($request->password);
        $user->must_change_password = MustChangePasswordEnum::NO()->value;

        $user->save();

        return redirect()->route('dashboard.index')
            ->with('status', 'Senha alterada com sucesso!');
    }
}
