<div class="modulo-legacy consulta-legacy page" style="--source:{{ $page['accent'] }}">
    <section class="page-title consulta-page-title">
        <span class="consulta-source-logo"><img src="{{ asset('assets/images/'.($page['source'] === 'RENIEC' ? 'reniec' : 'sunat').'-logo-sin-fondo.png') }}" alt="{{ $page['source'] }}"></span>
        <div>
            <h1>{{ $page['title'] }} - {{ $page['source'] }}</h1>
            <p>{{ $page['description'] }}</p>
        </div>
    </section>

    <section class="content-wrapper consulta-search-card">
        <h2 class="section-header consulta-section-header"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Parámetros de consulta</h2>
        <form wire:submit="search" class="consulta-search-form" novalidate>
            <div class="field consulta-main-field">
                <label for="query"><x-icon :name="$page['source'] === 'RENIEC' ? 'id' : 'document'" /> {{ $page['field'] }}</label>
                <input
                    id="query"
                    type="text"
                    wire:model="busqueda"
                    maxlength="{{ $page['source'] === 'RENIEC' ? 8 : 11 }}"
                    inputmode="numeric"
                    autocomplete="off"
                    placeholder="{{ $page['placeholder'] }}"
                    x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').slice(0, {{ $page['source'] === 'RENIEC' ? 8 : 11 }})"
                    aria-describedby="query-hint query-error"
                >
                <span id="query-hint" class="consulta-hint">{{ $page['hint'] }}</span>
                @error('busqueda') <span id="query-error" class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            @if($page['needsCredentials'] ?? false)
                <input type="hidden" wire:model="dniUsuario">
            @endif

            <div class="consulta-search-actions">
                <span class="field-label-spacer" aria-hidden="true">&nbsp;</span>
                <div class="consulta-search-actions-row">
                    <button class="consulta-button consulta-button-source" type="submit" wire:loading.attr="disabled" wire:target="search">
                        <span wire:loading.remove wire:target="search"><x-icon name="search" /> Buscar</span>
                        <span wire:loading.flex wire:target="search"><i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Consultando…</span>
                    </button>
                    <button class="consulta-button consulta-button-clear" type="button" wire:click="resetSearch" wire:loading.attr="disabled" wire:target="search" aria-label="Limpiar consulta">
                        <x-icon name="eraser" /><span class="sr-only">Limpiar</span>
                    </button>
                </div>
            </div>
        </form>
    </section>

    @if($successMessage || $errorMessage)
        <div class="consulta-alert {{ $successMessage ? 'success' : (($connectionFallbackAvailable ?? false) ? 'danger' : 'warning') }}" role="status">
            <x-icon :name="$successMessage ? 'check' : 'warning'" />
            <span>{{ $successMessage ?? $errorMessage }}</span>
        </div>
    @endif

    @production
        @if($connectionFallbackAvailable)
            <section class="consulta-connection-fallback" role="region" aria-labelledby="connection-fallback-title">
                <span class="consulta-connection-fallback-icon"><x-icon name="warning" /></span>
                <div>
                    <h2 id="connection-fallback-title">Consulta PIDE no disponible</h2>
                    <p>No fue posible obtener datos por un error de conexión. Puedes reintentar o visualizar información ficticia claramente identificada.</p>
                </div>
                <div class="consulta-connection-actions">
                    <button type="button" class="consulta-button consulta-button-source" wire:click="search" wire:loading.attr="disabled" wire:target="search">
                        <span wire:loading.remove wire:target="search"><x-icon name="search" /> Reintentar</span>
                        <span wire:loading.flex wire:target="search"><i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Consultando…</span>
                    </button>
                    <button type="button" class="consulta-button consulta-fictitious-button" wire:click="openFictitiousModal">
                        <x-icon name="document" /> Usar datos ficticios
                    </button>
                </div>
            </section>
        @endif

        @if($usingFictitiousData)
            <div class="consulta-fictitious-notice" role="status">
                <x-icon name="info" />
                <span><strong>Datos ficticios:</strong> contenido demostrativo, no obtenido de PIDE y no válido para trámites.</span>
            </div>
        @endif
    @endproduction

    @if($page['source'] !== 'RENIEC')
        <x-consulta-print-header
            :entity-logo="$page['logo'] ?? null"
            :entity="$page['source']"
            :title="$page['title']"
            :subtitle="$page['description'] ?? ''"
        />
    @endif

    <section class="consulta-legacy-results {{ ($page['hasPhoto'] ?? false) ? 'with-photo' : '' }}" aria-live="polite">
        @if($page['hasPhoto'] ?? false)
            <article class="content-wrapper consulta-photo-card">
                <h2><x-icon name="camera" /> Fotografía</h2>
                <div class="consulta-photo-frame">
                    @if($photo)
                        <img src="{{ $photo }}" alt="Fotografía de la persona consultada">
                    @else
                        <x-icon name="user" />
                        <span>Sin fotografía</span>
                    @endif
                </div>
            </article>
        @endif

        <article class="content-wrapper consulta-info-card">
            <h2><x-icon :name="$page['source'] === 'RENIEC' ? 'user' : 'building'" /> {{ $page['resultTitle'] ?? 'Información' }}</h2>
            <div class="consulta-info-grid {{ $page['source'] === 'SUNAT' ? 'sunat-grid' : '' }}">
                @foreach(array_keys($page['result']) as $label)
                    @php($featured = $page['featuredFields'][$label] ?? null)
                    @php($valor = $searched ? (($result[$label] ?? '') !== '' ? $result[$label] : '-') : '-')
                    <div class="consulta-info-item {{ in_array($label, $page['fullWidthFields'] ?? [], true) ? 'full' : '' }} {{ $featured ? 'featured '.$featured : '' }}">
                        <span>{{ $label }}</span>
                        <strong class="{{ $valor === '-' ? 'is-empty' : '' }}">{{ $valor }}</strong>
                    </div>
                @endforeach
            </div>

            @if($page['source'] === 'RENIEC')
                <div class="consulta-result-actions">
                    <form method="POST" action="{{ route('consulta.dni.pdf') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $pdfToken ?? '' }}">
                        <button type="submit" class="consulta-button consulta-button-pdf" @disabled(!($searched && $real && ($pdfToken ?? '')))><x-icon name="pdf" /> Exportar PDF</button>
                    </form>
                    <button type="button" class="consulta-button consulta-button-print" onclick="window.print()" @disabled(!($searched && ($real || $usingFictitiousData)))><x-icon name="print" /> Imprimir</button>
                </div>
            @else
                <x-consulta-export-actions
                    :token="$pdfToken ?? ''"
                    :can-pdf="$searched && $real && ($pdfToken ?? '')"
                    :can-print="$searched && ($real || ($usingFictitiousData ?? false))"
                />
            @endif
        </article>
    </section>

    @production
        <div
            data-ui-modal
            class="modal-overlay pide-credential-overlay"
            x-cloak
            x-show="open"
            x-data="{
                open: @entangle('showFictitiousModal'),
                previousFocus: null,
                init() {
                    this.$watch('open', (visible) => {
                        if (visible) {
                            this.previousFocus = document.activeElement;
                            this.$nextTick(() => this.$refs.confirm?.focus());
                        } else {
                            this.previousFocus?.focus?.();
                        }
                    });
                },
                close() {
                    this.open = false;
                    this.$wire.closeFictitiousModal();
                },
                trapFocus(event) {
                    const controls = [...this.$refs.dialog.querySelectorAll('button:not([disabled])')];
                    const first = controls[0];
                    const last = controls[controls.length - 1];

                    if (event.shiftKey && document.activeElement === first) {
                        event.preventDefault();
                        last.focus();
                    } else if (!event.shiftKey && document.activeElement === last) {
                        event.preventDefault();
                        first.focus();
                    }
                }
            }"
            role="presentation"
            @click.self="close()"
            @keydown.escape.window="if (open) close()"
            @keydown.tab="if (open) trapFocus($event)"
        >
            <section
                x-ref="dialog"
                class="modal-content pide-credential-modal pide-fallback-modal"
                role="dialog"
                aria-modal="true"
                aria-labelledby="pide-fallback-title"
                aria-describedby="pide-fallback-description pide-fallback-warning"
                x-show="open"
            >
                <header class="pide-credential-header pide-fallback-header">
                    <div class="pide-credential-heading">
                        <span class="pide-credential-icon pide-fallback-icon"><x-icon name="warning" /></span>
                        <div>
                            <h2 id="pide-fallback-title">Error de conexión con PIDE</h2>
                            <p id="pide-fallback-description">La consulta no pudo completarse en este momento.</p>
                        </div>
                    </div>

                    <button type="button" class="pide-modal-close" aria-label="Cerrar modal" @click="close()">
                        <x-icon name="close" />
                    </button>
                </header>

                <div class="pide-credential-form">
                    <p class="pide-fallback-copy">Puedes continuar llenando el resultado con datos ficticios para visualizar el formato.</p>
                    <div id="pide-fallback-warning" class="pide-fallback-warning">
                        <x-icon name="info" />
                        <span>Estos datos no provienen de PIDE, no representan una consulta oficial y no son válidos para trámites.</span>
                    </div>

                    <div class="pide-credential-actions">
                        <button type="button" class="pide-modal-button pide-modal-secondary" @click="close()">Cancelar</button>
                        <button x-ref="confirm" type="button" class="pide-modal-button pide-modal-primary pide-fallback-confirm" wire:click="useFictitiousData" wire:loading.attr="disabled" wire:target="useFictitiousData">
                            <span wire:loading.remove wire:target="useFictitiousData"><x-icon name="document" /> Llenar con datos ficticios</span>
                            <span wire:loading wire:target="useFictitiousData"><span class="loading-spinner"></span> Cargando...</span>
                        </button>
                    </div>
                </div>
            </section>
        </div>
    @endproduction
</div>
