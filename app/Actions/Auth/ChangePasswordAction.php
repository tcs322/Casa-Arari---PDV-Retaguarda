<?php

namespace App\Actions\Auth;

use App\DTO\Auth\ChangePasswordDTO;
use App\Enums\MustChangePasswordEnum;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChangePasswordAction 
{
    public function __construct() { }

    public function exec(ChangePasswordDTO $dto): User
    {
        $user = Auth::user();
        
        $user->password = Hash::make($dto->password);
        $user->must_change_password = MustChangePasswordEnum::NO()->value;

        $user->save();

        return $user;
    }
}
