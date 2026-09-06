<?php

namespace App\Livewire;

use App\Livewire\Concerns\BuildsConsultaPdf;
use App\Livewire\Concerns\HandlesPideConnectionErrors;
use App\Services\Pide\Contracts\ConadisServiceInterface;
use Livewire\Component;
use Throwable;

final class ConsultaConadis extends Component
{
    use BuildsConsultaPdf;
    use HandlesPideConnectionErrors;

    public string $numeroDocumento = '';

    public bool $searched = false;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public array $result = [];

    public string $pdfToken = '';

    public function consultar(): void
    {
        $this->validate([
            'numeroDocumento' => ['required', 'digits:8'],
        ], [
            'numeroDocumento.required' => 'Ingresa el número de DNI.',
            'numeroDocumento.digits' => 'El DNI debe contener exactamente 8 dígitos.',
        ]);

        $this->reset('searched', 'errorMessage', 'successMessage', 'result', 'pdfToken');

        try {
            $respuesta = app(ConadisServiceInterface::class)->consultarPersona($this->numeroDocumento);
        } catch (Throwable $exception) {
            report($exception);
            $respuesta = [
                'success' => false,
                'message' => 'No se pudo conectar con CONADIS. Inténtalo nuevamente.',
                'error_type' => 'transport',
            ];
        }

        $this->searched = true;

        if (! ($respuesta['success'] ?? false)) {
            $this->errorMessage = $this->friendlyPideMessage($respuesta, 'CONADIS', 'No fue posible completar la consulta CONADIS.');

            $this->dispatch('pide-alert', message: $this->errorMessage, type: 'danger');

            return;
        }

        $this->result = $respuesta['data'] ?? [];
        $this->successMessage = $respuesta['message'] ?? 'Consulta CONADIS completada.';
        $this->pdfToken = $this->buildPdfToken();
        $this->dispatch('pide-alert', message: $this->successMessage, type: 'success');
    }

    public function resetSearch(): void
    {
        $this->reset('numeroDocumento', 'searched', 'errorMessage', 'successMessage', 'result', 'pdfToken');
        $this->resetValidation();
    }

    private function buildPdfToken(): string
    {
        if ($this->result === []) {
            return '';
        }

        $gravedad = ($this->result['gravedad'] ?? -1) >= 0 ? $this->result['gravedad'].' · ' : '';

        return $this->cacheConsultaPdf([
            'entity' => ['name' => 'CONADIS', 'logo' => 'logo_conadis.png'],
            'title' => 'Consulta de Persona con Discapacidad',
            'subtitle' => 'Registro Nacional de Personas con Discapacidad — CONADIS',
            'meta' => ['DNI consultado' => $this->numeroDocumento],
            'filename' => 'conadis-'.$this->numeroDocumento,
            'sections' => [
                [
                    'heading' => 'Datos del registro',
                    'type' => 'fields',
                    'rows' => [
                        'Nombres' => $this->result['nombre'] ?? '',
                        'Apellido paterno' => $this->result['apellidoPaterno'] ?? '',
                        'Apellido materno' => $this->result['apellidoMaterno'] ?? '',
                        'Gravedad registrada' => trim($gravedad.($this->result['gravedadDescripcion'] ?? 'No especificado')),
                        'Condición de fallecimiento' => ($this->result['fallecido'] ?? false)
                            ? 'Registrado como fallecido'
                            : 'No registrado como fallecido',
                        'Estado de inscripción' => $this->result['estadoDescripcion'] ?? 'No especificado',
                    ],
                ],
            ],
        ]);
    }

    public function render()
    {
        return view('livewire.consulta-conadis');
    }
}
