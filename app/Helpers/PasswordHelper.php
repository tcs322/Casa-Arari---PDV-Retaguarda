<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordHelper
{
    /**
     * Gera uma senha temporária (fixa ou aleatória)
     *
     * @param bool $random Define se a senha será aleatória
     * @param int $length Tamanho da senha aleatória (se aplicável)
     * @return string Senha temporária já com hash
     */
    public static function generateTemporaryPassword(bool $random = false, int $length = 8): string
    {
        // Senha fixa (primeiro acesso)
        if (! $random) {
            return Hash::make('123456');
        }

        // Senha aleatória (ex.: futura melhoria)
        $plainPassword = Str::random($length);

        // Aqui você poderia salvar o $plainPassword em algum log seguro
        // ou enviar por e-mail antes de retornar o hash

        return Hash::make($plainPassword);
    }
}
