<?php

namespace App\Services;

use App\Models\HistorialAuditoria;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Cambia exclusivamente la contraseña local del usuario de demostración.
 */
class ChangeUserPasswordService
{
    public function change(Usuario $usuario, string $currentPassword, string $newPassword): void
    {
        $usuario->forceFill([
            'password_hash' => Hash::make($newPassword),
            'requiere_cambio_password' => false,
            'fecha_actualizacion_password' => now(),
        ])->save();

        HistorialAuditoria::create([
            'tabla' => 'usuarios',
            'registro_id' => $usuario->id,
            'operacion' => 'CAMBIO_PASSWORD',
            'usuario_id' => $usuario->id,
            'fecha' => now(),
            'ip' => request()?->ip(),
            'observacion' => 'Cambio de contraseña local en el entorno de demostración.',
        ]);

        Log::info('Cambio de contraseña local registrado', [
            'operation' => 'CAMBIO_PASSWORD',
            'user_id' => $usuario->id,
        ]);
    }
}
