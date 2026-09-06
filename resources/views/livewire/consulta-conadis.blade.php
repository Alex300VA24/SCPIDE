<div class="modulo-legacy consulta-legacy page conadis-page" style="--source:#1769aa">
    <section class="page-title consulta-page-title">
        <span class="consulta-source-logo"><img src="{{ asset('assets/images/logo_conadis.png') }}" alt="CONADIS"></span>
        <div>
            <h1>Consulta de Persona con Discapacidad - CONADIS</h1>
            <p>Registro Nacional de Personas con Discapacidad · Consulta individual y confidencial</p>
        </div>
    </section>

    <section class="content-wrapper consulta-search-card">
        <h2 class="section-header consulta-section-header"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Parámetros de consulta</h2>
        <form wire:submit="consultar" class="consulta-search-form" novalidate>
            <div class="field consulta-main-field">
                <label for="conadis-documento"><x-icon name="id" /> Número de DNI</label>
                <input
                    id="conadis-documento"
                    type="text"
                    wire:model="numeroDocumento"
                    maxlength="8"
                    inputmode="numeric"
                    autocomplete="off"
                    placeholder="Ingrese 8 dígitos"
                    x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').slice(0, 8)"
                    aria-describedby="conadis-documento-hint conadis-documento-error"
                >
                <span id="conadis-documento-hint" class="consulta-hint">Consulta permitida solo de forma individual y bajo demanda.</span>
                @error('numeroDocumento') <span id="conadis-documento-error" class="field-error" role="alert">{{ $message }}</span> @enderror
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
        <div class="consulta-alert {{ $successMessage ? 'success' : 'danger' }}" role="status" aria-live="polite">
            <x-icon :name="$successMessage ? 'check' : 'warning'" />
            <span>{{ $successMessage ?? $errorMessage }}</span>
        </div>
    @endif

    @php($hasConadisResult = $searched && $result !== [])

    <x-consulta-print-header
        entity-logo="logo_conadis.png"
        entity="CONADIS"
        title="Consulta de Persona con Discapacidad - CONADIS"
        subtitle="Registro Nacional de Personas con Discapacidad"
    />

    <section class="consulta-legacy-results" aria-live="polite" aria-label="Resultado de la consulta CONADIS">
            <article class="content-wrapper consulta-info-card conadis-result-card {{ $hasConadisResult ? 'has-result' : 'is-empty' }}">
                <div class="conadis-result-heading">
                    <h2><x-icon name="user" /> Datos del registro</h2>
                    <span class="conadis-status-chip {{ $hasConadisResult ? (($result['estado'] ?? 0) === 1 ? 'is-registered' : 'is-unregistered') : 'is-pending' }}">
                        <x-icon :name="$hasConadisResult && ($result['estado'] ?? 0) === 1 ? 'check' : ($hasConadisResult ? 'warning' : 'clock')" />
                        {{ $hasConadisResult ? ($result['estadoDescripcion'] ?? 'No especificado') : 'Pendiente de consulta' }}
                    </span>
                </div>

                <dl class="consulta-info-grid">
                    <div class="consulta-info-item">
                        <dt>Nombres</dt>
                        <dd>@if($hasConadisResult){{ $result['nombre'] ?? '' }}@else<span class="sr-only">Sin consultar</span>@endif</dd>
                    </div>
                    <div class="consulta-info-item">
                        <dt>Apellido paterno</dt>
                        <dd>@if($hasConadisResult){{ $result['apellidoPaterno'] ?? '' }}@else<span class="sr-only">Sin consultar</span>@endif</dd>
                    </div>
                    <div class="consulta-info-item">
                        <dt>Apellido materno</dt>
                        <dd>@if($hasConadisResult){{ $result['apellidoMaterno'] ?? '' }}@else<span class="sr-only">Sin consultar</span>@endif</dd>
                    </div>
                    <div class="consulta-info-item">
                        <dt>Gravedad registrada</dt>
                        <dd>@if($hasConadisResult){{ ($result['gravedad'] ?? -1) >= 0 ? $result['gravedad'].' · ' : '' }}{{ $result['gravedadDescripcion'] ?? 'No especificado' }}@else<span class="sr-only">Sin consultar</span>@endif</dd>
                    </div>
                    <div class="consulta-info-item">
                        <dt>Condición de fallecimiento</dt>
                        <dd>@if($hasConadisResult){{ ($result['fallecido'] ?? false) ? 'Registrado como fallecido' : 'No registrado como fallecido' }}@else<span class="sr-only">Sin consultar</span>@endif</dd>
                    </div>
                    <div class="consulta-info-item">
                        <dt>Estado de inscripción</dt>
                        <dd>@if($hasConadisResult){{ $result['estadoDescripcion'] ?? 'No especificado' }}@else<span class="sr-only">Sin consultar</span>@endif</dd>
                    </div>
                </dl>

                <p class="conadis-confidentiality"><x-icon name="shield" /> Información confidencial. Uso exclusivo para funciones autorizadas.</p>

                @if($hasConadisResult)
                    <x-consulta-export-actions :token="$pdfToken" :can-pdf="(bool) $pdfToken" :can-print="true" />
                @endif
            </article>
    </section>
</div>
