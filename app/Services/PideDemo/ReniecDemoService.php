<?php

namespace App\Services\PideDemo;

use App\Services\Pide\Contracts\ReniecServiceInterface;
use App\Services\PideDemo\Support\DemoDataFactory;

/**
 * Implementación de demostración de {@see ReniecServiceInterface}.
 *
 * No se conecta a RENIEC: devuelve datos ficticios generados localmente
 * para que el repositorio público muestre el flujo de consulta sin
 * exponer la integración real.
 */
class ReniecDemoService implements ReniecServiceInterface
{
    public function consultarDNI(string $dni): array
    {
        $persona = DemoDataFactory::personaNatural($dni);
        $ubigeo = DemoDataFactory::ubigeo($dni);

        return [
            'success' => true,
            'message' => DemoDataFactory::avisoDemo(),
            'data' => [
                'dni' => $dni,
                'nombres' => $persona['nombres'],
                'apellido_paterno' => $persona['apellido_paterno'],
                'apellido_materno' => $persona['apellido_materno'],
                'estado_civil' => 'SOLTERO',
                'direccion' => 'AV. DEMOSTRACION NRO. 123, ' . $ubigeo['distrito'],
                'restriccion' => '-',
                'ubigeo' => $ubigeo['codigo'],
                'foto' => null,
            ],
        ];
    }

    public function obtenerDatosRENIEC(string $dni): array
    {
        $persona = DemoDataFactory::personaNatural($dni);

        return [
            'success' => true,
            'message' => DemoDataFactory::avisoDemo(),
            'data' => [[
                'tipo' => 'PERSONA_NATURAL',
                'dni' => $dni,
                'nombres' => $persona['nombres'],
                'apellido_paterno' => $persona['apellido_paterno'],
                'apellido_materno' => $persona['apellido_materno'],
                'foto' => null,
                'nombres_completos' => trim($persona['nombres'] . ' ' . $persona['apellido_paterno'] . ' ' . $persona['apellido_materno']),
            ]],
            'total' => 1,
        ];
    }

    public function actualizarPasswordRENIEC(string $credencialAnterior, string $credencialNueva, string $nuDni): array
    {
        return [
            'success' => true,
            'message' => 'Demo: la actualización de contraseña no está disponible en este entorno de demostración.',
        ];
    }
}
