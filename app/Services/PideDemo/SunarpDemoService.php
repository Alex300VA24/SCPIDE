<?php

namespace App\Services\PideDemo;

use App\Services\Pide\Contracts\ReniecServiceInterface;
use App\Services\Pide\Contracts\SunarpServiceInterface;
use App\Services\Pide\Contracts\SunatServiceInterface;
use App\Services\PideDemo\Support\DemoDataFactory;

/**
 * Implementación de demostración de {@see SunarpServiceInterface}.
 *
 * No se conecta a SUNARP: devuelve datos ficticios generados localmente.
 * Reutiliza las implementaciones demo de RENIEC/SUNAT inyectadas, igual que
 * hace el servicio real con las suyas.
 */
class SunarpDemoService implements SunarpServiceInterface
{
    public function __construct(
        private readonly ReniecServiceInterface $reniecService,
        private readonly SunatServiceInterface $sunatService
    ) {
    }

    public function buscarPersonaNatural(string $dni): array
    {
        return $this->reniecService->obtenerDatosRENIEC($dni);
    }

    public function buscarPersonaJuridica(array $input): array
    {
        $tipoBusqueda = $input['tipoBusqueda'] ?? 'ruc';

        if ($tipoBusqueda === 'ruc') {
            $ruc = trim((string) ($input['ruc'] ?? ''));
            $datosSunat = $this->sunatService->consultarRUC($ruc);

            return [
                'success' => true,
                'message' => DemoDataFactory::avisoDemo(),
                'data' => [$datosSunat['data']],
                'total' => 1,
            ];
        }

        if ($tipoBusqueda === 'razonSocial') {
            return $this->sunatService->buscarPorRazonSocial(trim((string) ($input['razonSocial'] ?? '')));
        }

        return ['success' => false, 'message' => 'Tipo de búsqueda inválido. Use "ruc" o "razonSocial"'];
    }

    public function consultarTSIRSARPNatural(string $apellidoPaterno, string $apellidoMaterno, string $nombres): array
    {
        return $this->registroTitularidad($apellidoPaterno . $apellidoMaterno . $nombres, [
            'apPaterno' => $apellidoPaterno ?: 'GARCIA',
            'apMaterno' => $apellidoMaterno ?: 'PEREZ',
            'nombre' => $nombres ?: 'JUAN CARLOS',
            'razon_social' => '',
        ]);
    }

    public function consultarTSIRSARPJuridica(string $razonSocial): array
    {
        return $this->registroTitularidad($razonSocial, [
            'apPaterno' => '',
            'apMaterno' => '',
            'nombre' => '',
            'razon_social' => $razonSocial ?: DemoDataFactory::razonSocial('DEMO'),
        ]);
    }

    private function registroTitularidad(string $semilla, array $identidad): array
    {
        $numeroPartida = '11' . str_pad((string) (DemoDataFactory::seed($semilla) % 999999), 6, '0', STR_PAD_LEFT);

        $item = array_merge($identidad, [
            'libro' => 'PROPIEDAD INMUEBLE',
            'tipo_documento' => '',
            'numero_documento' => '',
            'numero_partida' => $numeroPartida,
            'numero_placa' => '',
            'oficina' => 'ZONA REGISTRAL IX - SEDE LIMA (DEMO)',
            'zona' => '09',
            'estado' => 'VIGENTE',
            'direccion' => 'AV. DEMOSTRACION NRO. 123',
            'registro' => 'PROPIEDAD INMUEBLE',
            'indice' => 0,
            'asientos' => [],
            'imagenes' => [],
            'datos_vehiculo' => [],
            'detalle_cargado' => false,
            'codigo_zona' => '09',
            'codigo_oficina' => '01',
        ]);

        return [
            'success' => true,
            'message' => DemoDataFactory::avisoDemo() . ' Use la carga de detalle para ver asientos e imágenes de ejemplo.',
            'data' => [$item],
            'total' => 1,
            'requiere_carga_bajo_demanda' => true,
        ];
    }

    public function consultarGOficina(): array
    {
        $catalogo = [
            'ZONA REGISTRAL IX - SEDE LIMA (DEMO)' => ['codZona' => '09', 'codOficina' => '01', 'descripcion' => 'ZONA REGISTRAL IX - SEDE LIMA (DEMO)'],
            'ZONA REGISTRAL XII - SEDE AREQUIPA (DEMO)' => ['codZona' => '12', 'codOficina' => '01', 'descripcion' => 'ZONA REGISTRAL XII - SEDE AREQUIPA (DEMO)'],
        ];

        return [
            'success' => true,
            'message' => DemoDataFactory::avisoDemo(),
            'data' => $catalogo,
            'total' => count($catalogo),
        ];
    }

    public function consultarLASIRSARP(string $zona, string $oficina, string $partida): array
    {
        return [
            'success' => true,
            'message' => DemoDataFactory::avisoDemo(),
            'data' => array_merge($this->asientosDemo($partida), [
                'imagenes' => $this->imagenesDemo(),
            ]),
        ];
    }

    public function cargarDetallePartida(string $numeroPartida, string $codigoZona, string $codigoOficina, string $numeroPlaca = ''): array
    {
        $detalle = [
            'asientos' => $this->asientosDemo($numeroPartida)['asientos'],
            'imagenes' => $this->imagenesDemo(),
            'datos_vehiculo' => [],
        ];

        if (trim($numeroPlaca) !== '' && trim($numeroPlaca) !== '-') {
            $detalle['datos_vehiculo'] = [
                'placa' => strtoupper($numeroPlaca),
                'marca' => 'MARCA DEMO',
                'modelo' => 'MODELO DEMO',
                'color' => 'BLANCO',
                'anio_fabricacion' => (string) (now()->year - 3),
                'estado' => 'En circulación (demo)',
            ];
        }

        return [
            'success' => true,
            'message' => 'Detalle cargado (demo). ' . DemoDataFactory::avisoDemo(),
            'data' => $detalle,
        ];
    }

    private function asientosDemo(string $partida): array
    {
        return [
            'asientos' => [[
                'idImgAsiento' => '1',
                'numPag' => '1',
                'tipo' => 'ASIENTO',
                'listPag' => [],
                'categoria' => 'asiento',
                'partida' => $partida,
                'descripcion' => 'Inscripción de dominio (demo)',
            ]],
        ];
    }

    private function imagenesDemo(): array
    {
        return [[
            'asiento' => 1,
            'pagina' => 1,
            'nroPagRef' => '1',
            'mime' => 'image/png',
            'ancho' => 1,
            'alto' => 1,
            'bytes' => 68,
            'imagen_base64' => DemoDataFactory::imagenDemoBase64(),
        ]];
    }
}
