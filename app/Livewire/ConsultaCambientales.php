<?php

namespace App\Livewire;

use App\Livewire\Concerns\BuildsConsultaPdf;
use App\Livewire\Concerns\HandlesPideConnectionErrors;
use App\Services\Pide\Contracts\CAmbientalesServiceInterface;
use Livewire\Component;
use Throwable;

/**
 * Consulta de certificaciones ambientales (SENACE WS5).
 *
 * Uso estrictamente transaccional: una consulta por envío de formulario.
 * No implementa (ni debe implementarse) carga masiva/batch.
 */
class ConsultaCambientales extends Component
{
    use BuildsConsultaPdf;
    use HandlesPideConnectionErrors;

    public string $tipoIga = '';

    public string $expediente = '';

    public string $grupoSector = '';

    public string $subSector = '';

    public string $actividad = '';

    public string $nroRuc = '';

    public string $titular = '';

    public string $nroCatalogo = '';

    public string $nomProyecto = '';

    public string $idDepa = '';

    public string $idProv = '';

    public string $idDist = '';

    public string $resolucion = '';

    public bool $searched = false;

    public bool $real = false;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public array $certificaciones = [];

    public string $pdfToken = '';

    public function buscar(): void
    {
        $this->validate(
            [
                'tipoIga' => ['required', 'in:01,03,04,05,09,10,11,12,13,14,15,16,17,18,19,20,21,22,23'],
                'expediente' => ['required', 'string', 'max:50'],
                'grupoSector' => ['required', 'in:1,2,3,4,5'],
                'subSector' => ['required', 'in:1,3,4,5,6,7,8'],
                'actividad' => ['required', 'in:1,3,4,6,11,12'],
                'nroRuc' => ['nullable', 'digits:11'],
                'idDepa' => ['nullable', 'digits:2'],
                'idProv' => ['nullable', 'digits:2'],
                'idDist' => ['nullable', 'digits:2'],
            ],
            [
                'tipoIga.required' => 'Seleccione el tipo de instrumento de gestión ambiental.',
                'tipoIga.in' => 'Tipo de instrumento no válido.',
                'expediente.required' => 'Ingrese el número de expediente.',
                'grupoSector.required' => 'Seleccione el grupo sector.',
                'subSector.required' => 'Seleccione el subsector.',
                'actividad.required' => 'Seleccione la actividad.',
                'nroRuc.digits' => 'El RUC debe tener 11 dígitos.',
                'idDepa.digits' => 'El código de departamento debe tener 2 dígitos.',
                'idProv.digits' => 'El código de provincia debe tener 2 dígitos.',
                'idDist.digits' => 'El código de distrito debe tener 2 dígitos.',
            ],
        );

        $this->errorMessage = null;
        $this->successMessage = null;
        $this->certificaciones = [];
        $this->real = false;
        $this->pdfToken = '';

        try {
            $resultado = app(CAmbientalesServiceInterface::class)->consultar([
                'TipoIga' => $this->tipoIga,
                'Expediente' => $this->expediente,
                'GrupoSector' => $this->grupoSector,
                'SubSector' => $this->subSector,
                'Actividad' => $this->actividad,
                'NroRuc' => $this->nroRuc,
                'Titular' => $this->titular,
                'NroCatalogo' => $this->nroCatalogo,
                'NomProyecto' => $this->nomProyecto,
                'IdDepa' => $this->idDepa,
                'IdProv' => $this->idProv,
                'IdDist' => $this->idDist,
                'Resolucion' => $this->resolucion,
            ]);
        } catch (Throwable $e) {
            report($e);
            $this->errorMessage = 'No se pudo conectar con el servicio PIDE. Inténtalo nuevamente.';
            $this->searched = true;
            $this->dispatch('pide-alert', message: $this->errorMessage, type: 'danger');

            return;
        }

        if (!$resultado['success']) {
            $this->errorMessage = $this->friendlyPideMessage($resultado, 'SENACE', 'Servicio PIDE no disponible en este momento.');
            $this->searched = true;
            $this->dispatch('pide-alert', message: $this->errorMessage, type: $this->isPideTransportError($resultado) ? 'danger' : 'warning');

            return;
        }

        $this->real = true;
        $this->certificaciones = $resultado['data'];
        $this->successMessage = $resultado['message'] ?: (empty($this->certificaciones)
            ? 'No se ha encontrado ningún resultado para los filtros seleccionados.'
            : 'Consulta realizada exitosamente.');
        $this->pdfToken = $this->buildPdfToken();
        $this->searched = true;
        $this->dispatch('pide-alert', message: $this->successMessage, type: empty($this->certificaciones) ? 'info' : 'success');
    }

    public function resetSearch(): void
    {
        $this->reset(
            'expediente', 'nroRuc', 'titular', 'nroCatalogo', 'nomProyecto',
            'idDepa', 'idProv', 'idDist', 'resolucion',
            'searched', 'real', 'certificaciones', 'errorMessage', 'successMessage', 'pdfToken',
        );
        $this->resetValidation();
    }

    private function buildPdfToken(): string
    {
        if ($this->certificaciones === []) {
            return '';
        }

        $sections = [];

        foreach ($this->certificaciones as $indice => $cert) {
            $ubicacion = collect([
                $cert['ubigeo']['departamento'] ?? null,
                $cert['ubigeo']['provincia'] ?? null,
                $cert['ubigeo']['distrito'] ?? null,
            ])->filter()->implode(' / ');

            $sections[] = [
                'heading' => $cert['nombre_proyecto'] ?: 'Certificación ambiental '.($indice + 1),
                'type' => 'fields',
                'rows' => [
                    'Expediente' => $cert['expediente'] ?? '',
                    'Estado' => $cert['estado'] ?? '',
                    'Tipo IGA' => $cert['tipo_iga'] ?? '',
                    'Ente evaluador' => $cert['ente'] ?? '',
                    'Sector' => $cert['sector'] ?? '',
                    'Subsector' => $cert['subsector'] ?? '',
                    'Actividad' => $cert['actividad'] ?? '',
                    'Titular' => $cert['titular'] ?? '',
                    'RUC titular' => $cert['ruc_titular'] ?? '',
                    'Consultora' => $cert['consultora'] ?? '',
                    'RUC consultora' => $cert['ruc_consultora'] ?? '',
                    'N° resolución' => $cert['nro_resol'] ?? '',
                    'Fecha de resolución' => $cert['fec_resol'] ?? '',
                    'Fecha de ingreso' => $cert['fec_ingreso'] ?? '',
                    'N° catálogo' => $cert['catalogo'] ?? '',
                    'Ubicación' => $ubicacion,
                ],
            ];
        }

        return $this->cacheConsultaPdf([
            'entity' => ['name' => 'SENACE', 'logo' => 'senace-logo-sin-fondo.png'],
            'title' => 'Certificaciones Ambientales',
            'subtitle' => 'Registro Administrativo de Certificaciones Ambientales — SENACE',
            'meta' => [
                'Expediente' => $this->expediente,
                'Resultados' => (string) count($this->certificaciones),
            ],
            'filename' => 'certificacion-ambiental-'.$this->expediente,
            'sections' => $sections,
        ]);
    }

    public function render()
    {
        return view('livewire.consulta-cambientales');
    }
}
