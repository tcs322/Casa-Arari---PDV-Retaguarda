<?php

namespace App\Http\Requests\App\Usuario;

use Illuminate\Foundation\Http\FormRequest;

class UsuarioStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "name" => [
                "required", "min:5", "max:254"
            ],
            "email" => [
                "required", "min:5", "max:254", "email", "unique:users,email"
            ],
            "situacao" => [
                "required"
            ]
        ];
    }
}
