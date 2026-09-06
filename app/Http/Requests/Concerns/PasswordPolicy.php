<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Rules\Password;

/**
 * Política compartida para contraseñas locales de usuarios demo.
 */
class PasswordPolicy
{
    /**
     * @return array<int, mixed>
     */
    public static function rules(): array
    {
        return [
            'string',
            Password::min(8)->mixedCase()->numbers()->symbols(),
        ];
    }
}
