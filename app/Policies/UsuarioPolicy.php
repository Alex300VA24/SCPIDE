<?php

namespace App\Policies;

use App\Models\Usuario;

/**
 * Autorización de las operaciones sobre la contraseña de un Usuario.
 *
 * Concentra en un solo lugar quién puede cambiar una contraseña y cuándo
 * ese cambio debería replicarse como credencial ante un servicio externo.
 * Este repositorio es un prototipo público: solo muestra la funcionalidad,
 * sin la integración real, por eso la sincronización externa está siempre
 * deshabilitada.
 */
class UsuarioPolicy
{
    /**
     * ¿Puede $actor cambiar la contraseña de $objetivo?
     *
     * El cambio de contraseña es autoservicio: solo el propio usuario
     * autenticado puede modificar su credencial desde este flujo.
     */
    public function updatePassword(Usuario $actor, Usuario $objetivo): bool
    {
        return $actor->is($objetivo);
    }

    /**
     * ¿El cambio debe sincronizarse contra un servicio externo?
     *
     * En el prototipo público nunca: no hay integración real que
     * sincronizar. El punto de decisión existe para que el flujo de
     * casos de uso sea el mismo que el del sistema completo.
     */
    public function syncPidePassword(Usuario $actor, Usuario $objetivo): bool
    {
        return false;
    }
}
