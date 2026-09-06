<?php

namespace App\Livewire;

use App\Http\Requests\ConsultaPartidasRequest;
use App\Livewire\Concerns\BuildsConsultaPdf;
use App\Services\Pide\Contracts\SunarpServiceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

class ConsultaPartidas extends BaseConsultation
{
    use BuildsConsultaPdf;

    public string $tab = 'natural';

    public string $infoPdfToken = '';

    public ?string $activeModal = null;

    public string $naturalDni = '';

    public string $juridicaMode = 'ruc';

    public string $juridicaQuery = '';

    public array $people = [];

    public array $selectedPerson = [];

    public array $partidas = [];

    public array $selectedPartida = [];

    public array $detail = [];

    public int $partidasPage = 1;

    public int $partidasPerPage = 8;

    public ?string $statusMessage = null;

    public string $statusType = 'info';

    public function mount(): void
    {
        $this->dniUsuario = (string) (auth()->user()?->persona?->documento_numero ?? '');
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['natural', 'juridica', 'partida'], true) ? $tab : 'natural';
        $this->clearSearchState();
        $this->resetValidation();
    }

    public function openSearchModal(): void
    {
        if ($this->tab === 'partida') {
            return;
        }

        $this->activeModal = $this->tab;
        $this->people = [];
        $this->resetValidation(['naturalDni', 'juridicaQuery']);
    }

    public function closeModal(): void
    {
        $this->activeModal = null;
    }

    public function searchNatural(): void
    {
        $this->validate(
            ConsultaPartidasRequest::naturalRules(),
            ConsultaPartidasRequest::validationMessages(),
            ConsultaPartidasRequest::validationAttributes(),
        );

        $this->runPersonSearch(
            fn (SunarpServiceInterface $service) => $service->buscarPersonaNatural($this->naturalDni),
            'RENIEC'
        );
    }

    public function searchJuridica(): void
    {
        $this->validate(
            ConsultaPartidasRequest::juridicaRules($this->juridicaMode),
            ConsultaPartidasRequest::validationMessages(),
            ConsultaPartidasRequest::validationAttributes($this->juridicaMode),
        );

        $payload = ['tipoBusqueda' => $this->juridicaMode];
        $payload[$this->juridicaMode === 'ruc' ? 'ruc' : 'razonSocial'] = trim($this->juridicaQuery);

        $this->runPersonSearch(fn (SunarpServiceInterface $service) => $service->buscarPersonaJuridica($payload), 'SUNAT');
    }

    public function selectPerson(int $index): void
    {
        abort_unless(isset($this->people[$index]), 404);
        $this->selectedPerson = $this->people[$index];
        $this->activeModal = null;
        $this->setStatus('Persona seleccionada. Haz clic en “Consultar” para buscar sus partidas en SUNARP.', 'info');
    }

    public function searchSunarp(): void
    {
        $this->resetValidation();
        $this->clearResults();

        if ($this->tab === 'partida') {
            $this->validate(
                ConsultaPartidasRequest::partidaRules(),
                ConsultaPartidasRequest::validationMessages(),
                ConsultaPartidasRequest::validationAttributes(),
            );

            [$zona, $oficina] = array_pad(explode('|', $this->oficina, 2), 2, '');
            $this->partidas = [[
                'numero_partida' => $this->busqueda,
                'codigo_zona' => $zona,
                'codigo_oficina' => $oficina,
                'oficina' => $this->oficinaEtiqueta($this->oficina),
                'estado' => 'REGISTRADA',
                'numero_placa' => '',
            ]];
            $this->loadPartida(0);

            return;
        }

        if ($this->selectedPerson === []) {
            $this->addError('selectedPerson', 'Selecciona una persona antes de consultar.');
            $this->setStatus('Selecciona una persona antes de consultar.', 'warning');

            return;
        }

        try {
            $service = app(SunarpServiceInterface::class);
            $response = $this->tab === 'natural'
                ? $service->consultarTSIRSARPNatural(
                    (string) ($this->selectedPerson['apellido_paterno'] ?? ''),
                    (string) ($this->selectedPerson['apellido_materno'] ?? ''),
                    (string) ($this->selectedPerson['nombres'] ?? ''),
                )
                : $service->consultarTSIRSARPJuridica((string) ($this->selectedPerson['razon_social'] ?? ''));

            if (! ($response['success'] ?? false) || empty($response['data'])) {
                $this->setStatus($response['message'] ?? 'No se encontraron registros en SUNARP.', 'warning');

                return;
            }

            $this->partidas = array_values($response['data']);
            $this->setStatus('Se encontraron '.count($this->partidas).' registro(s) en SUNARP.', 'success');
            $this->loadPartida(0);
        } catch (Throwable $e) {
            report($e);
            $this->setStatus('No se pudo conectar con SUNARP. Inténtalo nuevamente.', 'danger');
        }
    }

    public function selectPartida(int $index): void
    {
        abort_unless(isset($this->partidas[$index]), 404);
        $this->loadPartida($index);
    }

    public function setPartidasPage(int $page): void
    {
        $lastPage = max(1, (int) ceil(count($this->partidas) / $this->partidasPerPage));
        $this->partidasPage = min(max($page, 1), $lastPage);
    }

    public function resetSearch(): void
    {
        $this->clearSearchState();
        $this->naturalDni = '';
        $this->juridicaQuery = '';
        $this->oficina = '';
        $this->busqueda = '';
        $this->resetValidation();
    }

    public function render()
    {
        $offset = ($this->partidasPage - 1) * $this->partidasPerPage;

        return view('livewire.consulta-partidas', [
            'page' => $this->page(),
            'visiblePartidas' => array_slice($this->partidas, $offset, $this->partidasPerPage, true),
            'partidasLastPage' => max(1, (int) ceil(count($this->partidas) / $this->partidasPerPage)),
        ]);
    }

    protected function page(): array
    {
        return [
            'title' => 'Consulta de Partidas Registrales',
            'source' => 'SUNARP',
            'description' => 'Superintendencia Nacional de los Registros Públicos',
            'accent' => '#7c3aed',
            'oficinas' => $this->oficinasDisponibles(),
        ];
    }

    private function runPersonSearch(callable $callback, string $source): void
    {
        try {
            $response = $callback(app(SunarpServiceInterface::class));

            if (! ($response['success'] ?? false) || empty($response['data'])) {
                $this->people = [];
                $message = $this->friendlyPideMessage($response, $source, "No se encontraron datos en {$source}.");
                $this->setStatus($message, $this->isPideTransportError($response) ? 'danger' : 'warning');

                return;
            }

            $this->people = array_values($response['data']);
            $this->setStatus('Se encontraron '.count($this->people)." resultado(s) en {$source}.", 'success');
        } catch (Throwable $e) {
            report($e);
            $this->people = [];
            $this->setStatus("No se pudo conectar con {$source}. Inténtalo nuevamente.", 'danger');
        }
    }

    private function loadPartida(int $index): void
    {
        $partida = $this->partidas[$index];
        $this->selectedPartida = $partida;
        $this->detail = [];

        $numero = (string) ($partida['numero_partida'] ?? $partida['numeroPartida'] ?? $this->busqueda);
        $zona = (string) ($partida['codigo_zona'] ?? $partida['zona'] ?? '');
        $oficina = (string) ($partida['codigo_oficina'] ?? $partida['oficina'] ?? '');
        $placa = (string) ($partida['numero_placa'] ?? $partida['numeroPlaca'] ?? '');

        try {
            $response = app(SunarpServiceInterface::class)->cargarDetallePartida($numero, $zona, $oficina, $placa);

            if (! ($response['success'] ?? false)) {
                $message = $this->friendlyPideMessage($response, 'SUNARP', 'No se pudo cargar el detalle de la partida.');
                $this->setStatus($message, $this->isPideTransportError($response) ? 'danger' : 'warning');

                return;
            }

            $this->detail = $response['data'] ?? [];

            $this->selectedPartida = array_merge($partida, $this->detail);
            $this->pdfToken = $this->cachePartidaForPdf($numero);
            $this->infoPdfToken = $this->buildInfoPdfToken($numero);
            $this->searched = true;
        } catch (Throwable $e) {
            report($e);
            $this->setStatus('No se pudo cargar el detalle registral. Inténtalo nuevamente.', 'danger');
        }
    }

    private function clearSearchState(): void
    {
        $this->people = [];
        $this->selectedPerson = [];
        $this->partidas = [];
        $this->selectedPartida = [];
        $this->detail = [];
        $this->partidasPage = 1;
        $this->searched = false;
        $this->statusMessage = null;
        $this->activeModal = null;
        $this->pdfToken = '';
        $this->infoPdfToken = '';
    }

    private function clearResults(): void
    {
        $this->partidas = [];
        $this->selectedPartida = [];
        $this->detail = [];
        $this->partidasPage = 1;
        $this->searched = false;
        $this->statusMessage = null;
        $this->pdfToken = '';
        $this->infoPdfToken = '';
    }

    /**
     * Genera el token del PDF con formato ordenado (logo municipal + SUNARP)
     * de la información registral y vehicular de la partida seleccionada.
     */
    private function buildInfoPdfToken(string $numero): string
    {
        $record = $this->selectedPartida;
        $isNatural = $this->tab === 'natural';

        $identityRows = array_filter([
            'Nombres' => $isNatural ? ($record['nombre'] ?? $this->selectedPerson['nombres'] ?? null) : null,
            'Apellido paterno' => $isNatural ? ($record['apPaterno'] ?? $this->selectedPerson['apellido_paterno'] ?? null) : null,
            'Apellido materno' => $isNatural ? ($record['apMaterno'] ?? $this->selectedPerson['apellido_materno'] ?? null) : null,
            'Razón social' => $this->tab === 'juridica' ? ($record['razon_social'] ?? $this->selectedPerson['razon_social'] ?? null) : null,
            'Tipo documento' => $this->tab === 'partida' ? null : ($record['tipo_documento'] ?? ($isNatural ? 'DNI' : 'RUC')),
            'Nro. documento' => $this->tab === 'partida' ? null : ($record['numero_documento'] ?? ($isNatural ? ($this->selectedPerson['dni'] ?? null) : ($this->selectedPerson['ruc'] ?? null))),
            'Nro. partida' => $record['numero_partida'] ?? $record['numeroPartida'] ?? $numero,
            'Nro. placa' => $record['numero_placa'] ?? $record['numeroPlaca'] ?? null,
            'Estado' => $record['estado'] ?? null,
            'Zona' => $record['zona'] ?? $record['codigo_zona'] ?? null,
            'Libro' => $record['libro'] ?? null,
            'Oficina' => $record['oficina'] ?? null,
            'Dirección' => $record['direccion'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        $sections = [[
            'heading' => 'Información registral',
            'type' => 'fields',
            'rows' => $identityRows,
        ]];

        $vehicle = $this->detail['datos_vehiculo'] ?? [];
        if (is_array($vehicle) && $vehicle !== []) {
            $labels = [
                'placa' => 'Placa', 'marca' => 'Marca', 'modelo' => 'Modelo', 'anoFabricacion' => 'Año',
                'color' => 'Color', 'nro_motor' => 'N° motor', 'carroceria' => 'Carrocería',
                'codCategoria' => 'Categoría', 'estado' => 'Estado',
            ];
            $vehicleRows = [];
            foreach ($labels as $key => $label) {
                if (isset($vehicle[$key]) && $vehicle[$key] !== '') {
                    $vehicleRows[$label] = $vehicle[$key];
                }
            }
            if ($vehicleRows !== []) {
                $sections[] = ['heading' => 'Información vehicular', 'type' => 'fields', 'rows' => $vehicleRows];
            }
        }

        return $this->cacheConsultaPdf([
            'entity' => ['name' => 'SUNARP', 'logo' => 'sunarp-logo-sin-fondo.png'],
            'title' => 'Consulta de Partida Registral',
            'subtitle' => 'Superintendencia Nacional de los Registros Públicos',
            'meta' => ['Partida N°' => $numero],
            'filename' => 'partida-'.preg_replace('/[^A-Za-z0-9_-]/', '', $numero),
            'sections' => $sections,
        ]);
    }

    private function cachePartidaForPdf(string $numero): string
    {
        $images = collect($this->detail['imagenes'] ?? [])
            ->map(function ($entry, int $index): ?string {
                if (!is_array($entry) || !is_string($entry['imagen_base64'] ?? null)) {
                    return null;
                }

                $image = $entry['imagen_base64'];
                $image = preg_replace('/^data:image\/[a-zA-Z0-9.+-]+;base64,/', '', $image) ?? '';
                $image = preg_replace('/\s+/', '', $image) ?? '';
                $binary = base64_decode($image, true);

                if ($binary === false || $binary === '') {
                    return null;
                }

                $info = @getimagesizefromstring($binary);
                $mime = is_array($info) && is_string($info['mime'] ?? null)
                    ? $info['mime']
                    : ($entry['mime'] ?? null);

                if (!is_string($mime) || !str_starts_with($mime, 'image/')) {
                    return null;
                }

                return "data:{$mime};base64,".base64_encode($binary);
            })
            ->filter()
            ->values()
            ->all();

        if ($images === []) {
            return '';
        }

        $token = (string) Str::uuid();

        Cache::put("sunarp_partida_pdf:{$token}", [
            'user_id' => auth()->id(),
            'numero' => preg_replace('/[^A-Za-z0-9_-]/', '', $numero) ?: 'registral',
            'images' => $images,
        ], now()->addMinutes(10));

        return $token;
    }

    private function setStatus(string $message, string $type): void
    {
        $this->statusMessage = $message;
        $this->statusType = $type;
        $this->dispatch('pide-alert', message: $message, type: $type);
    }

    private function oficinasDisponibles(): array
    {
        return Cache::remember('sunarp_oficinas', now()->addDay(), function () {
            $response = app(SunarpServiceInterface::class)->consultarGOficina();

            if (! ($response['success'] ?? false) || empty($response['data'])) {
                return [];
            }

            return collect($response['data'])
                ->map(fn (array $office) => [
                    'value' => ($office['codZona'] ?? '').'|'.($office['codOficina'] ?? ''),
                    'label' => $office['descripcion'] ?? 'Oficina registral',
                ])
                ->filter(fn (array $office) => $office['value'] !== '|')
                ->sortBy('label')
                ->values()
                ->all();
        });
    }

    private function oficinaEtiqueta(string $value): string
    {
        foreach ($this->oficinasDisponibles() as $office) {
            if ($office['value'] === $value) {
                return $office['label'];
            }
        }

        return $value;
    }
}
