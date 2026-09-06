<?php

namespace App\Services\PideDemo;

use App\Services\Pide\Contracts\MtcServiceInterface;
use App\Services\PideDemo\Support\DemoDataFactory;

/**
 * Implementación de demostración de {@see MtcServiceInterface}.
 *
 * No se conecta al MTC: devuelve datos ficticios generados localmente.
 */
class MtcDemoService implements MtcServiceInterface
{
    public function consultarPapeletas(string $tipoDocumento, string $numeroDocumento): array
    {
        $tienePapeletas = DemoDataFactory::seed($numeroDocumento) % 2 === 0;

        if (!$tienePapeletas) {
            return $this->exito('No se encontraron papeletas para el administrado. ' . DemoDataFactory::avisoDemo(), []);
        }

        return $this->exito(DemoDataFactory::avisoDemo(), [[
            'numInfraccion' => 1001,
            'codEntidad' => 15,
            'entidad' => 'MUNICIPALIDAD DEMO',
            'papeleta' => 'D-' . substr($numeroDocumento, -4) . '001',
            'fechaFirme' => now()->subMonths(2)->format('d/m/Y'),
            'numeroResolucion' => 'RES-DEMO-0001',
            'falta' => 'M01 - Exceso de velocidad (demo)',
            'fecInfraccion' => now()->subMonths(3)->format('d/m/Y'),
            'fecFirme' => now()->subMonths(2)->format('d/m/Y'),
            'puntosFirmes' => 30,
            'pProceso' => 0,
            'estado' => 'FIRME',
            'tipoPit' => 'DEMO',
        ]]);
    }

    public function consultarUltimaLicencia(string $tipoDocumento, string $numeroDocumento): array
    {
        $persona = DemoDataFactory::personaNatural($numeroDocumento);

        return $this->exito(DemoDataFactory::avisoDemo(), [
            'tipoDoc' => $tipoDocumento,
            'numDocumento' => $numeroDocumento,
            'numLicencia' => 'Q' . str_pad($numeroDocumento, 8, '0', STR_PAD_LEFT),
            'categoria' => 'A-I',
            'apellidoPaterno' => $persona['apellido_paterno'],
            'apellidoMaterno' => $persona['apellido_materno'],
            'nombre' => $persona['nombres'],
            'restriccion' => 'NINGUNA',
            'fecRev' => now()->addYears(2)->format('d/m/Y'),
            'fecExp' => now()->subYears(3)->format('d/m/Y'),
            'estado' => 'VIGENTE',
        ]);
    }

    public function consultarUltimasSanciones(string $tipoDocumento, string $numeroDocumento): array
    {
        $tieneSanciones = DemoDataFactory::seed($numeroDocumento) % 3 === 0;

        if (!$tieneSanciones) {
            return $this->exito('No se encontraron sanciones vigentes para el administrado. ' . DemoDataFactory::avisoDemo(), []);
        }

        return $this->exito(DemoDataFactory::avisoDemo(), [[
            'numInfraccion' => 2002,
            'papeleta' => 'D-' . substr($numeroDocumento, -4) . '002',
            'falta' => 'M14 - Conducir sin licencia vigente (demo)',
            'fecInfraccion' => now()->subMonths(1)->format('d/m/Y'),
            'estado' => 'VIGENTE',
            'descripcion' => 'Sanción de demostración',
        ]]);
    }

    private function exito(string $mensaje, array $data): array
    {
        return [
            'success' => true,
            'message' => $mensaje,
            'errorCode' => null,
            'data' => $data,
        ];
    }
}
