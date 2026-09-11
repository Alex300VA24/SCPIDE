<?php

namespace App\UseCases\Password;

use App\Models\HistorialAuditoria;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Caso de uso: persistir la nueva contraseña local de un usuario.
 *
 * El hash de la contraseña y el registro de auditoría se escriben dentro
 * de una única transacción: si la auditoría falla, el hash no queda
 * cambiado, y viceversa.
 */
class ChangeLocalPassword
{
    public function handle(
        Usuario $usuario,
        string $newPassword,
        string $operacion = 'CAMBIO_PASSWORD',
        ?string $observacion = 'Cambio de contraseña local en el entorno de demostración.',
    ): void {
        DB::transaction(function () use ($usuario, $newPassword, $operacion, $observacion): void {
            $usuario->forceFill([
                'password_hash' => Hash::make($newPassword),
                'requiere_cambio_password' => false,
                'fecha_actualizacion_password' => now(),
            ])->save();

            HistorialAuditoria::create([
                'tabla' => 'usuarios',
                'registro_id' => $usuario->id,
                'operacion' => $operacion,
                'usuario_id' => $usuario->id,
                'fecha' => now(),
                'ip' => request()?->ip(),
                'observacion' => $observacion,
            ]);
        });

        Log::info('Cambio de contraseña local registrado', [
            'operation' => $operacion,
            'user_id' => $usuario->id,
        ]);
    }
}
