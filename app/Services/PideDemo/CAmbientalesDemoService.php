<?php

namespace App\Services\PideDemo;

use App\Services\Pide\Contracts\CAmbientalesServiceInterface;
use App\Services\PideDemo\Support\DemoDataFactory;

/**
 * Implementación de demostración de {@see CAmbientalesServiceInterface}.
 *
 * No se conecta a SENACE WS5: devuelve datos ficticios generados localmente.
 */
class CAmbientalesDemoService implements CAmbientalesServiceInterface
{
    public function consultar(array $parametros): array
    {
        $expediente = (string) ($parametros['Expediente'] ?? 'DEMO-0001');

        return [
            'success' => true,
            'message' => DemoDataFactory::avisoDemo(),
            'code' => '1',
            'data' => [[
                'actividad' => 'Actividad de demostración',
                'catalogo' => (string) ($parametros['NroCatalogo'] ?? '000'),
                'consultora' => 'CONSULTORA AMBIENTAL DEMO S.A.C.',
                'ente' => 'SENACE (demo)',
                'estado' => 'APROBADO',
                'expediente' => $expediente,
                'fec_ingreso' => now()->subMonths(8)->format('d/m/Y'),
                'fec_resol' => now()->subMonths(2)->format('d/m/Y'),
                'id_certificacion' => '1',
                'nombre_proyecto' => 'Proyecto de demostración ' . $expediente,
                'nro_resol' => 'RD-DEMO-0001-2026',
                'ruc_consultora' => '20100000001',
                'ruc_titular' => (string) ($parametros['NroRuc'] ?? '20100000002'),
                'sector' => 'Sector demo',
                'subsector' => 'Subsector demo',
                'tipo_iga' => (string) ($parametros['TipoIga'] ?? '01'),
                'titular' => (string) ($parametros['Titular'] ?? 'TITULAR DEMOSTRACION S.A.C.'),
                'ubigeo' => [
                    'departamento' => 'LIMA',
                    'provincia' => 'LIMA',
                    'distrito' => 'MIRAFLORES',
                    'id_ubigeo' => '150122',
                ],
                'v_acceso' => [],
                'v_lineaBase' => [],
            ]],
        ];
    }
}
