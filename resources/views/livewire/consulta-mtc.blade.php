<div class="modulo-legacy consulta-legacy page" style="--source:#1d4ed8">
    <section class="page-title consulta-page-title">
        <span class="consulta-source-logo"><img src="{{ asset('assets/images/mtc-logo-sin-fondo.png') }}" alt="MTC"></span>
        <div>
            <h1>Récord de Conductor - MTC</h1>
            <p>Ministerio de Transportes y Comunicaciones · Licencias, papeletas y sanciones vigentes</p>
        </div>
    </section>

    <section class="content-wrapper consulta-search-card">
        <h2 class="section-header consulta-section-header"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Parámetros de consulta</h2>
        <form wire:submit="consultar" class="consulta-search-form mtc-form" novalidate>
            <div class="mtc-query-scope" role="note" aria-label="Alcance de la consulta">
                <span class="mtc-query-scope-icon"><x-icon name="search" /></span>
                <span>
                    <strong>Una consulta, tres resultados</strong>
                    <small>Se consultarán papeletas, última licencia y sanciones del conductor.</small>
                </span>
            </div>

            <div class="field consulta-main-field">
                <label for="tipo-documento"><x-icon name="id" /> Tipo de documento</label>
                <select id="tipo-documento" wire:model="tipoDocumento" class="mtc-select" aria-describedby="tipo-error">
                    <option value="1">DNI</option>
                    <option value="2">Carné de Extranjería</option>
                </select>
                @error('tipoDocumento') <span id="tipo-error" class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            <div class="field consulta-main-field">
                <label for="numero-documento"><x-icon name="document" /> Número de documento</label>
                <input
                    id="numero-documento"
                    type="text"
                    wire:model="numeroDocumento"
                    maxlength="15"
                    inputmode="numeric"
                    autocomplete="off"
                    placeholder="Ingrese el número del documento"
                    x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').slice(0, 15)"
                    aria-describedby="numero-hint numero-error"
                >
                <span id="numero-hint" class="consulta-hint">Ingresa solo dígitos del documento del conductor.</span>
                @error('numeroDocumento') <span id="numero-error" class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            <div class="consulta-search-actions">
                <span class="field-label-spacer" aria-hidden="true">&nbsp;</span>
                <div class="consulta-search-actions-row">
                    <button class="consulta-button consulta-button-source" type="submit" wire:loading.attr="disabled" wire:target="consultar">
                        <span wire:loading.remove wire:target="consultar"><x-icon name="search" /> Consultar</span>
                        <span wire:loading.flex wire:target="consultar"><i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Consultando…</span>
                    </button>
                    <button class="consulta-button consulta-button-clear" type="button" wire:click="resetSearch" wire:loading.attr="disabled" wire:target="consultar" aria-label="Limpiar consulta">
                        <x-icon name="eraser" /><span class="sr-only">Limpiar</span>
                    </button>
                </div>
            </div>
        </form>
    </section>

    @if($successMessage || $errorMessage)
        <div class="consulta-alert {{ $successMessage ? 'success' : 'danger' }}" role="status">
            <x-icon :name="$successMessage ? 'check' : 'warning'" />
            <span>{{ $successMessage ?? $errorMessage }}</span>
        </div>
    @endif

    @if($searched)
        <x-consulta-print-header
            entity-logo="mtc-logo-sin-fondo.png"
            entity="MTC"
            title="Récord de Conductor - MTC"
            subtitle="Licencias, papeletas y sanciones vigentes"
        />

        <nav class="mtc-operaciones" role="tablist" aria-label="Resultados de la consulta MTC">
            @foreach ($this->operaciones() as $clave => $op)
                @php($cantidad = $clave === 'licencia' ? (empty($results[$clave] ?? []) ? 0 : 1) : count($results[$clave] ?? []))
                <button
                    type="button"
                    role="tab"
                    wire:click="$set('operacion', '{{ $clave }}')"
                    class="mtc-op {{ $operacion === $clave ? 'active' : '' }}"
                    aria-selected="{{ $operacion === $clave ? 'true' : 'false' }}"
                    aria-controls="mtc-result-panel"
                    wire:key="op-{{ $clave }}"
                >
                    <x-icon :name="$op['icon']" />
                    <span>{{ $op['label'] }}</span>
                    @if(isset($operationErrors[$clave]))
                        <span class="mtc-tab-status is-error" aria-label="Error en consulta">!</span>
                    @else
                        <span class="mtc-tab-status" aria-label="{{ $cantidad }} registros">{{ $cantidad }}</span>
                    @endif
                </button>
            @endforeach
        </nav>

        @php($activeResult = $results[$operacion] ?? [])
        <section id="mtc-result-panel" class="glass consulta-legacy-results" role="tabpanel" aria-live="polite">
            @if(isset($operationErrors[$operacion]))
                <div class="mtc-empty-state is-error" role="alert">
                    <x-icon name="warning" />
                    <div>
                        <strong>No se pudo obtener {{ strtolower($this->operaciones()[$operacion]['label']) }}</strong>
                        <p>{{ $operationErrors[$operacion] }}</p>
                    </div>
                </div>
            @elseif($activeResult === [])
                <div class="mtc-empty-state">
                    <x-icon name="check" />
                    <div>
                        <strong>Sin registros</strong>
                        <p>{{ $operationMessages[$operacion] ?? 'No se encontraron datos para el documento consultado.' }}</p>
                    </div>
                </div>
            @elseif($operacion === 'licencia')
                <article class="content-wrapper consulta-info-card">
                    <h2><x-icon name="id" /> Última Licencia de Conducir</h2>
                    <div class="consulta-info-grid">
                        @foreach($this->columnas() as $clave => $etiqueta)
                            @php($valor = $activeResult[$clave] ?? '')
                            <div class="consulta-info-item">
                                <span>{{ $etiqueta }}</span>
                                <strong class="{{ $valor === '' ? 'is-empty' : '' }}">{{ $valor !== '' ? $valor : '-' }}</strong>
                            </div>
                        @endforeach
                    </div>
                </article>
            @else
                <article class="content-wrapper consulta-info-card">
                    <h2><x-icon name="document" /> {{ $operacion === 'sanciones' ? 'Últimas Sanciones' : 'Papeletas Aplicadas' }}</h2>
                    <div class="table-wrap mtc-table">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    @foreach($this->columnas() as $etiqueta)
                                        <th>{{ $etiqueta }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activeResult as $item)
                                    <tr>
                                        @foreach(array_keys($this->columnas()) as $clave)
                                            @php($valor = $item[$clave] ?? '')
                                            <td class="{{ $valor === '' ? 'is-empty' : '' }}">{{ $valor !== '' ? $valor : '-' }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </article>
            @endif
        </section>

        <x-consulta-export-actions
            :token="$pdfToken"
            :can-pdf="(bool) $pdfToken"
            :can-print="count($operationErrors) < count($this->operaciones())"
        />
    @endif
</div>
