<?php

namespace App\Services\PideDemo;

use App\Services\Pide\Contracts\SunatServiceInterface;
use App\Services\PideDemo\Support\DemoDataFactory;

/**
 * Implementación de demostración de {@see SunatServiceInterface}.
 *
 * No se conecta a SUNAT: devuelve datos ficticios generados localmente.
 */
class SunatDemoService implements SunatServiceInterface
{
    public function consultarRUC(string $ruc): array
    {
        return [
            'success' => true,
            'message' => DemoDataFactory::avisoDemo(),
            'data' => $this->contribuyente($ruc),
        ];
    }

    public function buscarPorRazonSocial(string $razonSocial): array
    {
        $resultados = [];

        for ($i = 0; $i < 3; $i++) {
            $ruc = '20' . str_pad((string) (100000000 + $i), 9, '0', STR_PAD_LEFT);
            $item = $this->contribuyente($ruc, $razonSocial . ' ' . ($i + 1));
            $item['secuencia'] = $i;
            $resultados[] = $item;
        }

        return [
            'success' => true,
            'message' => DemoDataFactory::avisoDemo(),
            'data' => $resultados,
            'total' => count($resultados),
        ];
    }

    private function contribuyente(string $ruc, ?string $razonSocial = null): array
    {
        $ubigeo = DemoDataFactory::ubigeo($ruc);

        $data = [
            'ruc' => $ruc,
            'razon_social' => $razonSocial ?? DemoDataFactory::razonSocial($ruc),
            'codigo_ubigeo' => $ubigeo['codigo'],
            'departamento' => $ubigeo['departamento'],
            'provincia' => $ubigeo['provincia'],
            'distrito' => $ubigeo['distrito'],
            'cod_dep' => substr($ubigeo['codigo'], 0, 2),
            'cod_prov' => substr($ubigeo['codigo'], 2, 2),
            'cod_dist' => substr($ubigeo['codigo'], 4, 2),
            'tipo_via' => 'AV.',
            'codigo_tipo_via' => '01',
            'nombre_via' => 'DEMOSTRACION',
            'numero' => '123',
            'interior' => '-',
            'tipo_zona' => 'URB.',
            'codigo_tipo_zona' => '01',
            'nombre_zona' => 'LOS DEMO',
            'referencia' => 'FRENTE AL PARQUE',
            'estado_contribuyente' => 'ACTIVO',
            'codigo_estado' => '00',
            'condicion_domicilio' => 'HABIDO',
            'codigo_condicion' => '00',
            'tipo_contribuyente' => 'SOCIEDAD ANONIMA CERRADA',
            'codigo_tipo_contribuyente' => '20',
            'tipo_persona' => 'PERSONA JURIDICA',
            'codigo_tipo_persona' => 'J',
            'actividad_economica' => 'ACTIVIDADES DE DEMOSTRACION',
            'codigo_ciiu' => '0000',
            'dependencia' => 'INTENDENCIA REGIONAL DEMO',
            'codigo_dependencia' => '0021',
            'fecha_actualizacion' => now()->format('d/m/Y'),
            'fecha_alta' => now()->subYears(3)->format('d/m/Y'),
            'fecha_baja' => '-',
            'codigo_secuencia' => '0',
            'libreta_tributaria' => '-',
            'tamaño' => 'PEQUEÑA EMPRESA',
            'es_activo' => true,
            'es_habido' => true,
            'estado_activo' => 'SÍ',
            'estado_habido' => 'SÍ',
        ];

        $data['direccion_completa'] = trim(
            $data['tipo_via'] . ' ' . $data['nombre_via'] . ' NRO. ' . $data['numero'] . ' ' . $data['nombre_zona']
        );

        return $data;
    }
}
