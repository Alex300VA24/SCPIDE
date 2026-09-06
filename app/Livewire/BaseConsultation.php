<?php

namespace App\Livewire;

use App\Http\Requests\ConsultaRequest;
use App\Livewire\Concerns\HandlesPideConnectionErrors;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

abstract class BaseConsultation extends Component
{
    use HandlesPideConnectionErrors;

    public string $busqueda = '';

    public string $dniUsuario = '';

    public bool $searched = false;

    public bool $real = false;

    public string $oficina = '';

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public array $result = [];

    public ?string $photo = null;

    public bool $pideCredentialExpired = false;

    public bool $connectionFallbackAvailable = false;

    public bool $showFictitiousModal = false;

    public bool $usingFictitiousData = false;

    public string $pdfToken = '';

    protected bool $pideConnectionFailed = false;

    /**
     * Minutos que un resultado de consulta permanece disponible en caché
     * para poder generar el PDF sin volver a confiar en datos del cliente.
     */
    private const PDF_TOKEN_TTL_MINUTES = 10;

    public function mount(): void
    {
        $this->dniUsuario = (string) (auth()->user()?->persona?->documento_numero ?? '');
    }

    abstract protected function page(): array;

    /**
     * Intenta la consulta real contra PIDE. Devuelve null si el componente
     * no tiene integración real o si la consulta no fue exitosa.
     */
    protected function attemptReal(): ?array
    {
        return null;
    }

    public function search(): void
    {
        $needsOficina = $this->page()['needsOficina'] ?? false;

        $this->validate(
            ConsultaRequest::buildRules($this->page()['rules'], false, $needsOficina),
            ConsultaRequest::validationMessages(),
            ConsultaRequest::validationAttributes($this->page()['field']),
        );

        $this->errorMessage = null;
        $this->successMessage = null;
        $this->photo = null;
        $this->pideCredentialExpired = false;
        $this->connectionFallbackAvailable = false;
        $this->showFictitiousModal = false;
        $this->usingFictitiousData = false;
        $this->pideConnectionFailed = false;
        $this->pdfToken = '';
        $real = null;

        try {
            $real = $this->attemptReal();
        } catch (Throwable $e) {
            report($e);
            $this->pideConnectionFailed = true;
            $this->errorMessage = 'No se puede consultar PIDE por un error de conexión.';
        }

        if ($real !== null) {
            $this->result = $real;
            $this->real = true;
            $this->successMessage = 'Consulta realizada exitosamente.';
            $this->pdfToken = $this->resolvePdfToken();
        } else {
            $this->errorMessage ??= 'Servicio PIDE no disponible en este momento.';
            $this->result = [];
            $this->real = false;

            if ($this->pideConnectionFailed && $this->fictitiousFallbackEnabled()) {
                $this->errorMessage = 'No se puede consultar PIDE por un error de conexión.';
                $this->connectionFallbackAvailable = true;
                $this->showFictitiousModal = true;
            }
        }

        if (! $this->connectionFallbackAvailable) {
            $this->dispatch(
                'pide-alert',
                message: $this->successMessage ?? $this->errorMessage,
                type: $this->successMessage ? 'success' : ($this->pideConnectionFailed ? 'danger' : 'warning'),
            );
        }

        $this->searched = true;
    }

    public function resetSearch(): void
    {
        $this->reset(
            'busqueda',
            'searched',
            'result',
            'real',
            'errorMessage',
            'successMessage',
            'photo',
            'oficina',
            'pdfToken',
            'connectionFallbackAvailable',
            'showFictitiousModal',
            'usingFictitiousData',
        );
        $this->resetValidation();
    }

    public function openFictitiousModal(): void
    {
        if ($this->connectionFallbackAvailable && $this->fictitiousFallbackEnabled()) {
            $this->showFictitiousModal = true;
        }
    }

    public function closeFictitiousModal(): void
    {
        $this->showFictitiousModal = false;
    }

    public function useFictitiousData(): void
    {
        if (! $this->connectionFallbackAvailable || ! $this->fictitiousFallbackEnabled()) {
            $this->showFictitiousModal = false;

            return;
        }

        $this->result = $this->page()['result'];
        $this->real = false;
        $this->searched = true;
        $this->usingFictitiousData = true;
        $this->connectionFallbackAvailable = false;
        $this->showFictitiousModal = false;
        $this->errorMessage = 'Datos ficticios cargados. Esta información no proviene de PIDE.';
        $this->successMessage = null;
        $this->pdfToken = '';

        $this->dispatch('pide-alert', message: $this->errorMessage, type: 'warning');
    }

    /**
     * Marca el fallo de conexión y deja `errorMessage` listo para mostrarse
     * al usuario: un mensaje de negocio tal cual si el servicio lo devolvió,
     * o uno genérico y entendible si lo que falló fue el transporte (cURL,
     * timeout, DNS, etc.) — nunca la excepción técnica cruda.
     */
    protected function markPideFailure(array $response, string $servicio = 'PIDE', ?string $fallbackMessage = null): void
    {
        $this->pideConnectionFailed = $this->isPideTransportError($response);
        $this->errorMessage = $this->friendlyPideMessage($response, $servicio, $fallbackMessage);
    }

    private function fictitiousFallbackEnabled(): bool
    {
        // Fallback a datos ficticios solo en producción. En dev/testing,
        // exponer error de PIDE = developer debugging.
        return app()->environment('production');
    }

    /**
     * Token que permite regenerar el PDF de la consulta desde la caché del
     * servidor. Por defecto usa el flujo RENIEC (DniPdfController); las
     * consultas que exportan con el formato genérico ordenado —logo de la
     * municipalidad y de la entidad— lo sobreescriben.
     */
    protected function resolvePdfToken(): string
    {
        return $this->cacheResultForPdf();
    }

    /**
     * Guarda el resultado validado de la consulta en caché del servidor,
     * asociado al usuario autenticado, para que DniPdfController pueda
     * generar el PDF sin depender de datos enviados por el navegador.
     */
    private function cacheResultForPdf(): string
    {
        $token = (string) Str::uuid();

        Cache::put(
            "reniec_result:{$token}",
            [
                'user_id' => auth()->id(),
                'result' => $this->result,
                'photo' => $this->photo,
            ],
            now()->addMinutes(self::PDF_TOKEN_TTL_MINUTES),
        );

        return $token;
    }

    public function render()
    {
        return view('livewire.consulta', ['page' => $this->page()]);
    }
}
