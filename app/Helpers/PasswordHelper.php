<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordHelper
{
    public static function generateTemporaryPassword(bool $randomPassword = false, int $length = 8): string
    {
        if (! $randomPassword) {
            return Hash::make('123456');
        }
        
        $plainPassword = Str::random($length);

        return Hash::make($plainPassword);
    }
}
