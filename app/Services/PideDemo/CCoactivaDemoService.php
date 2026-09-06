<?php

namespace App\Services\PideDemo;

use App\Services\Pide\Contracts\CCoactivaServiceInterface;
use App\Services\PideDemo\Support\DemoDataFactory;

/**
 * Implementación de demostración de {@see CCoactivaServiceInterface}.
 *
 * No se conecta a SUNAT WS3: devuelve datos ficticios generados localmente.
 */
class CCoactivaDemoService implements CCoactivaServiceInterface
{
    public function consultarDeudaCoactiva(string $tipoDocumento, string $numeroDocumento): array
    {
        $tieneDeuda = DemoDataFactory::seed($numeroDocumento) % 2 === 0;

        if (!$tieneDeuda) {
            return [
                'success' => true,
                'message' => 'No se encontraron deudas en cobranza coactiva. ' . DemoDataFactory::avisoDemo(),
                'errorCode' => null,
                'data' => [],
            ];
        }

        return [
            'success' => true,
            'message' => DemoDataFactory::avisoDemo(),
            'errorCode' => null,
            'data' => [[
                'nomRuc' => DemoDataFactory::razonSocial($numeroDocumento),
                'numRuc' => $tipoDocumento === '06' ? $numeroDocumento : '20' . str_pad($numeroDocumento, 9, '0', STR_PAD_LEFT),
                'mtoDeuda' => 1500.75,
                'fecAct' => now()->format('d/m/Y'),
                'fecTraCoa' => now()->subMonths(4)->format('d/m/Y'),
                'desEntidad' => 'SUNAT (demo)',
                'perDoc' => now()->subMonths(6)->format('m/Y'),
            ]],
        ];
    }
}
