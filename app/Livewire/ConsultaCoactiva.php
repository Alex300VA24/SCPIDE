<?php

namespace App\Livewire;

use App\Livewire\Concerns\BuildsConsultaPdf;
use App\Livewire\Concerns\HandlesPideConnectionErrors;
use App\Services\Pide\Contracts\CCoactivaServiceInterface;
use Livewire\Component;
use Throwable;

/**
 * Consulta de deudas en cobranza coactiva (SUNAT WS3).
 *
 * Uso estrictamente transaccional: una consulta por envío de formulario.
 * No implementa (ni debe implementarse) carga masiva/batch.
 */
class ConsultaCoactiva extends Component
{
    use BuildsConsultaPdf;
    use HandlesPideConnectionErrors;

    public string $tipoDocumento = '01';

    public string $numeroDocumento = '';

    public bool $searched = false;

    public bool $real = false;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public array $deudas = [];

    public int $deudaActual = 0;

    public string $pdfToken = '';

    public function buscar(): void
    {
        $this->validate(
            [
                'tipoDocumento' => ['required', 'in:01,06'],
                'numeroDocumento' => ['required', 'digits:' . ($this->tipoDocumento === '01' ? 8 : 11)],
            ],
            [
                'tipoDocumento.required' => 'Seleccione el tipo de documento.',
                'tipoDocumento.in' => 'Tipo de documento no válido.',
                'numeroDocumento.required' => 'Ingrese el número de documento.',
                'numeroDocumento.digits' => 'Debe ingresar exactamente :digits dígitos numéricos.',
            ],
        );

        $this->errorMessage = null;
        $this->successMessage = null;
        $this->deudas = [];
        $this->deudaActual = 0;
        $this->real = false;
        $this->pdfToken = '';

        try {
            $resultado = app(CCoactivaServiceInterface::class)
                ->consultarDeudaCoactiva($this->tipoDocumento, $this->numeroDocumento);
        } catch (Throwable $e) {
            report($e);
            $this->errorMessage = 'No se pudo conectar con el servicio PIDE. Inténtalo nuevamente.';
            $this->searched = true;
            $this->dispatch('pide-alert', message: $this->errorMessage, type: 'danger');

            return;
        }

        if (!$resultado['success']) {
            $this->errorMessage = $this->friendlyPideMessage($resultado, 'SUNAT', 'Servicio PIDE no disponible en este momento.');
            $this->searched = true;
            $this->dispatch('pide-alert', message: $this->errorMessage, type: $this->isPideTransportError($resultado) ? 'danger' : 'warning');

            return;
        }

        $this->real = true;
        $this->deudas = $resultado['data'];
        $this->deudaActual = 0;
        $this->successMessage = empty($this->deudas)
            ? 'No se encontraron deudas en cobranza coactiva.'
            : 'Consulta realizada exitosamente.';
        $this->pdfToken = $this->buildPdfToken();
        $this->searched = true;
        $this->dispatch('pide-alert', message: $this->successMessage, type: empty($this->deudas) ? 'info' : 'success');
    }

    public function resetSearch(): void
    {
        $this->reset('numeroDocumento', 'searched', 'real', 'deudas', 'deudaActual', 'errorMessage', 'successMessage', 'pdfToken');
        $this->resetValidation();
    }

    private function buildPdfToken(): string
    {
        if ($this->deudas === []) {
            return '';
        }

        $columns = ['Contribuyente', 'RUC', 'Entidad', 'Periodo', 'Monto (S/)', 'Fecha transferencia', 'Fecha actualización'];

        $rows = array_map(static fn (array $deuda): array => [
            $deuda['nomRuc'] ?? '',
            $deuda['numRuc'] ?? '',
            $deuda['desEntidad'] ?? '',
            $deuda['perDoc'] ?? '',
            number_format((float) ($deuda['mtoDeuda'] ?? 0), 2),
            $deuda['fecTraCoa'] ?? '',
            $deuda['fecAct'] ?? '',
        ], $this->deudas);

        return $this->cacheConsultaPdf([
            'entity' => ['name' => 'SUNAT', 'logo' => 'sunat-logo-sin-fondo.png'],
            'title' => 'Cobranza Coactiva',
            'subtitle' => 'Deudas en cobranza coactiva administradas por SUNAT',
            'meta' => [
                ($this->tipoDocumento === '01' ? 'DNI' : 'RUC').' consultado' => $this->numeroDocumento,
                'Deudas encontradas' => (string) count($this->deudas),
            ],
            'filename' => 'coactiva-'.$this->numeroDocumento,
            'sections' => [
                [
                    'heading' => 'Detalle de deudas coactivas',
                    'type' => 'table',
                    'columns' => $columns,
                    'rows' => $rows,
                ],
            ],
        ]);
    }

    public function seleccionarDeuda(int $indice): void
    {
        if (!array_key_exists($indice, $this->deudas)) {
            return;
        }

        $this->deudaActual = $indice;
    }

    public function deudaAnterior(): void
    {
        $this->deudaActual = max(0, $this->deudaActual - 1);
    }

    public function deudaSiguiente(): void
    {
        $ultimoIndice = max(0, count($this->deudas) - 1);
        $this->deudaActual = min($ultimoIndice, $this->deudaActual + 1);
    }

    public function render()
    {
        return view('livewire.consulta-coactiva');
    }
}
