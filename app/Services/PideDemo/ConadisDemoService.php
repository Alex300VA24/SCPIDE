<?php

namespace App\Services\PideDemo;

use App\Services\Pide\Contracts\ConadisServiceInterface;
use App\Services\PideDemo\Support\DemoDataFactory;

/**
 * Implementación de demostración de {@see ConadisServiceInterface}.
 *
 * No se conecta a CONADIS: devuelve datos ficticios generados localmente.
 */
class ConadisDemoService implements ConadisServiceInterface
{
    public function consultarPersona(string $docNumber): array
    {
        $persona = DemoDataFactory::personaNatural($docNumber);
        $inscrito = DemoDataFactory::seed($docNumber) % 2 === 0;
        $gravedad = 1 + (DemoDataFactory::seed($docNumber) % 3);

        $gravedades = [1 => 'Entero', 2 => 'Moderado', 3 => 'Severo'];

        return [
            'success' => true,
            'message' => $inscrito
                ? 'La persona se encuentra inscrita en el registro del CONADIS. ' . DemoDataFactory::avisoDemo()
                : 'La persona no se encuentra inscrita en el registro del CONADIS. ' . DemoDataFactory::avisoDemo(),
            'errorCode' => null,
            'error_type' => null,
            'data' => [
                'nombre' => $persona['nombres'],
                'apellidoPaterno' => $persona['apellido_paterno'],
                'apellidoMaterno' => $persona['apellido_materno'],
                'fallecido' => false,
                'gravedad' => $inscrito ? $gravedad : -1,
                'gravedadDescripcion' => $inscrito ? $gravedades[$gravedad] : 'No especificado',
                'estado' => $inscrito ? 1 : 0,
                'estadoDescripcion' => $inscrito ? 'Inscrito' : 'No inscrito',
            ],
        ];
    }

    public function actualizarCredenciales(string $username, string $oldPassword, string $newPassword, string $dominio): array
    {
        return [
            'success' => true,
            'message' => 'Demo: la actualización de credenciales no está disponible en este entorno de demostración.',
            'errorCode' => null,
            'error_type' => null,
            'data' => [],
        ];
    }
}
