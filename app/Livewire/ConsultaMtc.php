<?php

namespace App\Livewire;

use App\Livewire\Concerns\BuildsConsultaPdf;
use App\Livewire\Concerns\HandlesPideConnectionErrors;
use App\Services\Pide\Contracts\MtcServiceInterface;
use Livewire\Component;
use Throwable;

class ConsultaMtc extends Component
{
    use BuildsConsultaPdf;
    use HandlesPideConnectionErrors;

    public string $tipoDocumento = '1';

    public string $numeroDocumento = '';

    public string $operacion = 'papeletas';

    public bool $searched = false;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public array $results = [];

    public array $operationMessages = [];

    public array $operationErrors = [];

    public string $pdfToken = '';

    public function consultar(): void
    {
        $this->validate([
            'tipoDocumento' => ['required', 'in:1,2'],
            'numeroDocumento' => ['required', 'regex:/^\d{1,15}$/'],
        ], [
            'tipoDocumento.required' => 'Selecciona el tipo de documento.',
            'tipoDocumento.in' => 'El tipo de documento no es válido.',
            'numeroDocumento.required' => 'Ingresa el número de documento.',
            'numeroDocumento.regex' => 'El número de documento solo debe contener dígitos.',
        ]);

        $this->errorMessage = null;
        $this->successMessage = null;
        $this->results = [];
        $this->operationMessages = [];
        $this->operationErrors = [];
        $this->searched = false;
        $this->pdfToken = '';

        try {
            $servicio = app(MtcServiceInterface::class);

            $consultas = [
                'papeletas' => fn () => $servicio->consultarPapeletas($this->tipoDocumento, $this->numeroDocumento),
                'licencia' => fn () => $servicio->consultarUltimaLicencia($this->tipoDocumento, $this->numeroDocumento),
                'sanciones' => fn () => $servicio->consultarUltimasSanciones($this->tipoDocumento, $this->numeroDocumento),
            ];

            foreach ($consultas as $operacion => $consultar) {
                $resultado = $consultar();

                if ($resultado['success'] ?? false) {
                    $this->results[$operacion] = $resultado['data'] ?? [];
                    $this->operationMessages[$operacion] = $resultado['message'] ?? 'Consulta completada.';
                } else {
                    $this->results[$operacion] = [];
                    $this->operationErrors[$operacion] = $this->friendlyPideMessage($resultado, 'MTC', 'No se pudo consultar este servicio.');
                }
            }
        } catch (Throwable $e) {
            report($e);
            $this->errorMessage = 'No se pudo conectar con el servicio PIDE. Inténtalo nuevamente.';
            $this->dispatch('pide-alert', message: $this->errorMessage, type: 'danger');

            return;
        }

        $this->searched = true;
        $this->pdfToken = $this->buildPdfToken();

        if (count($this->operationErrors) === count($this->operaciones())) {
            $this->errorMessage = 'No fue posible completar las consultas del MTC.';
            $this->dispatch('pide-alert', message: $this->errorMessage, type: 'warning');

            return;
        }

        $completadas = count($this->operaciones()) - count($this->operationErrors);
        $this->successMessage = $completadas === count($this->operaciones())
            ? 'Consulta MTC completada: licencia, papeletas y sanciones disponibles.'
            : "Consulta MTC completada parcialmente ({$completadas} de 3 servicios).";
        $this->dispatch('pide-alert', message: $this->successMessage, type: $this->operationErrors === [] ? 'success' : 'info');
    }

    public function resetSearch(): void
    {
        $this->reset('numeroDocumento', 'operacion', 'searched', 'results', 'operationMessages', 'operationErrors', 'errorMessage', 'successMessage', 'pdfToken');
        $this->resetValidation();
    }

    private function buildPdfToken(): string
    {
        $tipoDocLabel = $this->tipoDocumento === '1' ? 'DNI' : 'Carné de Extranjería';
        $sections = [];

        $licencia = $this->results['licencia'] ?? [];
        if ($licencia !== []) {
            $rows = [];
            foreach ($this->columnas('licencia') as $clave => $etiqueta) {
                $rows[$etiqueta] = $licencia[$clave] ?? '';
            }
            $sections[] = ['heading' => 'Última licencia de conducir', 'type' => 'fields', 'rows' => $rows];
        }

        foreach (['papeletas' => 'Papeletas aplicadas', 'sanciones' => 'Últimas sanciones'] as $clave => $encabezado) {
            $registros = $this->results[$clave] ?? [];
            if ($registros === []) {
                continue;
            }
            $columnas = $this->columnas($clave);
            $sections[] = [
                'heading' => $encabezado,
                'type' => 'table',
                'columns' => array_values($columnas),
                'rows' => array_map(
                    static fn (array $item): array => array_map(
                        static fn (string $col): string => (string) ($item[$col] ?? ''),
                        array_keys($columnas),
                    ),
                    $registros,
                ),
            ];
        }

        return $this->cacheConsultaPdf([
            'entity' => ['name' => 'MTC', 'logo' => 'mtc-logo-sin-fondo.png'],
            'title' => 'Récord de Conductor',
            'subtitle' => 'Ministerio de Transportes y Comunicaciones — Licencias, papeletas y sanciones',
            'meta' => [$tipoDocLabel => $this->numeroDocumento],
            'filename' => 'record-conductor-'.$this->numeroDocumento,
            'sections' => $sections,
        ]);
    }

    public function operaciones(): array
    {
        return [
            'papeletas' => ['label' => 'Papeletas', 'icon' => 'document'],
            'licencia' => ['label' => 'Última Licencia', 'icon' => 'id'],
            'sanciones' => ['label' => 'Sanciones', 'icon' => 'warning'],
        ];
    }

    public function columnas(?string $operacion = null): array
    {
        return match ($operacion ?? $this->operacion) {
            'licencia' => [
                'tipoDoc' => 'Tipo documento',
                'numDocumento' => 'N° documento',
                'numLicencia' => 'N° licencia',
                'categoria' => 'Categoría',
                'apellidoPaterno' => 'Apellido paterno',
                'apellidoMaterno' => 'Apellido materno',
                'nombre' => 'Nombres',
                'restriccion' => 'Restricción',
                'fecRev' => 'Revalidación',
                'fecExp' => 'Expedición',
                'estado' => 'Estado',
            ],
            'sanciones' => [
                'papeleta' => 'Papeleta',
                'falta' => 'Falta',
                'fecInfraccion' => 'Fecha infracción',
                'estado' => 'Estado',
                'descripcion' => 'Descripción',
            ],
            default => [
                'papeleta' => 'Papeleta',
                'falta' => 'Falta',
                'entidad' => 'Entidad',
                'fecInfraccion' => 'Fecha infracción',
                'estado' => 'Estado',
                'puntosFirmes' => 'Puntos firmes',
                'pProceso' => 'Puntos en proceso',
                'tipoPit' => 'Tipo',
            ],
        };
    }

    public function render()
    {
        return view('livewire.consulta-mtc');
    }
}
