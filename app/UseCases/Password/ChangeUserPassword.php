<?php

namespace App\UseCases\Password;

use App\Models\Usuario;
use Illuminate\Support\Facades\Gate;

/**
 * Caso de uso coordinador del cambio de contraseña de un usuario.
 *
 * Decide, mediante la política {@see \App\Policies\UsuarioPolicy}, si el
 * cambio es solo local o si además debe sincronizarse contra un servicio
 * externo. En este prototipo público la política nunca pide sincronización
 * (no hay integración real), así que el flujo siempre termina en
 * {@see ChangeLocalPassword}, que escribe hash y auditoría dentro de una
 * transacción. La rama de sincronización se mantiene para que la forma del
 * caso de uso coincida con la del sistema completo.
 */
class ChangeUserPassword
{
    public function __construct(
        private readonly ChangeLocalPassword $changeLocalPassword,
    ) {
    }

    public function handle(Usuario $usuario, string $currentPassword, string $newPassword): void
    {
        Gate::forUser($usuario)->authorize('updatePassword', $usuario);

        // El prototipo no expone integración externa: siempre cambio local.
        if (Gate::forUser($usuario)->allows('syncPidePassword', $usuario)) {
            // Reservado para el sistema completo; inalcanzable aquí.
        }

        $this->changeLocalPassword->handle($usuario, $newPassword);
    }
}
