<div class="modulo-legacy consulta-legacy sunarp-legacy page" style="--source:{{ $page['accent'] }}">
    <div class="sunarp-loading-backdrop" wire:loading.flex wire:target="searchSunarp,selectPartida" role="status" aria-live="polite" aria-label="Cargando información registral">
        <div class="sunarp-loading-modal">
            <span class="sunarp-loading-brand" aria-hidden="true"><img src="{{ asset('assets/images/logo_pide_sin_texto.png') }}" alt=""><span class="sunarp-loading-spinner"></span></span>
            <strong>Consultando SUNARP</strong>
            <span>Cargando partidas e imágenes registrales…</span>
        </div>
    </div>

    <section class="page-title consulta-page-title">
        <span class="consulta-source-logo"><img src="{{ asset('assets/images/sunarp-logo-sin-fondo.png') }}" alt="SUNARP"></span>
        <div>
            <h1>{{ $page['title'] }} - {{ $page['source'] }}</h1>
            <p>{{ $page['description'] }}</p>
        </div>
    </section>

    <section class="content-wrapper sunarp-type-selector" aria-label="Tipo de consulta">
        <button type="button" class="sunarp-type-option {{ $tab === 'natural' ? 'active' : '' }}" wire:click="setTab('natural')"><x-icon name="user" /> Persona natural</button>
        <button type="button" class="sunarp-type-option {{ $tab === 'juridica' ? 'active' : '' }}" wire:click="setTab('juridica')"><x-icon name="building" /> Persona jurídica</button>
        <button type="button" class="sunarp-type-option {{ $tab === 'partida' ? 'active' : '' }}" wire:click="setTab('partida')"><x-icon name="document" /> Por partida</button>
    </section>

    <section class="content-wrapper sunarp-search-card">
        @if($tab !== 'partida')
            @php
                $hasSelectedPerson = $selectedPerson !== [];
                $isNaturalPerson = $tab === 'natural';
                $entityLabel = $isNaturalPerson ? 'Persona' : 'Persona jurídica';
                $selectedName = $isNaturalPerson
                    ? ($selectedPerson['nombres_completos'] ?? trim(($selectedPerson['nombres'] ?? '').' '.($selectedPerson['apellido_paterno'] ?? '').' '.($selectedPerson['apellido_materno'] ?? '')))
                    : ($selectedPerson['razon_social'] ?? '');
                $selectedDocument = $isNaturalPerson ? ($selectedPerson['dni'] ?? '') : ($selectedPerson['ruc'] ?? '');
                $nameParts = array_values(array_filter(preg_split('/\s+/', trim($selectedName)) ?: []));
                $selectedInitials = strtoupper(implode('', array_map(fn ($part) => mb_substr($part, 0, 1), array_slice($nameParts, 0, 2))));
            @endphp

            <div class="sunarp-query-head">
                <h2 class="section-header consulta-section-header"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Parámetros de consulta</h2>
                <span class="sunarp-step-badge">Paso {{ $hasSelectedPerson ? '2' : '1' }} de 2</span>
            </div>

            @if(!$hasSelectedPerson)
                <div class="sunarp-person-intro">
                    <strong>{{ $entityLabel }}</strong>
                    <p>Primero busca y selecciona {{ $isNaturalPerson ? 'una persona' : 'una persona jurídica' }} para continuar.</p>
                </div>
                <div class="sunarp-empty-selection">
                    <span class="sunarp-selection-avatar" aria-hidden="true"><x-icon :name="$isNaturalPerson ? 'user' : 'building'" /></span>
                    <strong>Aún no has seleccionado {{ $isNaturalPerson ? 'una persona' : 'una persona jurídica' }}</strong>
                    <button type="button" class="consulta-button consulta-button-source sunarp-find-person" wire:click="openSearchModal">
                        <x-icon name="search" /> Buscar {{ $isNaturalPerson ? 'persona' : 'persona jurídica' }}
                    </button>
                </div>
                @error('selectedPerson') <span class="field-error sunarp-selection-error" role="alert">{{ $message }}</span> @enderror
                <div class="sunarp-query-footer">
                    <p><x-icon name="info" /> La búsqueda se abrirá en una ventana de selección.</p>
                    <div class="sunarp-main-actions-row">
                        <button type="button" class="consulta-button consulta-button-source" disabled aria-disabled="true"><x-icon name="fa-solid fa-lock" /> Consultar</button>
                        <button type="button" class="consulta-button consulta-button-clear" wire:click="resetSearch"><x-icon name="eraser" /> Limpiar</button>
                    </div>
                </div>
            @else
                <div class="sunarp-person-intro"><strong>{{ $entityLabel }} seleccionada</strong></div>
                <div class="sunarp-selected-person">
                    <span class="sunarp-selection-avatar sunarp-selection-initials" aria-hidden="true">{{ $selectedInitials ?: ($isNaturalPerson ? 'PN' : 'PJ') }}</span>
                    <span class="sunarp-selected-person-data">
                        <strong>{{ $selectedName }}</strong>
                        <small>{{ $isNaturalPerson ? 'DNI' : 'RUC' }} {{ $selectedDocument ?: 'no disponible' }}</small>
                    </span>
                    <span class="sunarp-selected-badge"><x-icon name="check" /> Seleccionada</span>
                    <button type="button" class="sunarp-change-person" wire:click="openSearchModal"><x-icon :name="$isNaturalPerson ? 'user' : 'building'" /> Cambiar {{ $isNaturalPerson ? 'persona' : 'persona jurídica' }}</button>
                </div>
                <div class="sunarp-query-footer sunarp-query-footer-selected">
                    <div class="sunarp-main-actions-row">
                        <button type="button" class="consulta-button consulta-button-source" wire:click="searchSunarp" wire:loading.attr="disabled" wire:target="searchSunarp,selectPartida">
                            <span wire:loading.remove wire:target="searchSunarp,selectPartida"><x-icon name="search" /> Consultar</span>
                            <span wire:loading.flex wire:target="searchSunarp,selectPartida"><i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Consultando SUNARP…</span>
                        </button>
                        <button type="button" class="consulta-button consulta-button-clear" wire:click="resetSearch" wire:loading.attr="disabled" wire:target="searchSunarp,selectPartida"><x-icon name="eraser" /> Limpiar</button>
                    </div>
                </div>
            @endif
        @else
            <h2 class="section-header consulta-section-header"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Parámetros de consulta</h2>
            <div class="sunarp-search-grid">
                <div class="field sunarp-person-field">
                    <label for="sunarp-query">Número de partida:</label>
                    <input id="sunarp-query" type="text" wire:model="busqueda" maxlength="20" placeholder="Escribe el número de partida">
                    @error('busqueda') <span class="field-error" role="alert">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label for="sunarp-office">Oficina Registral:</label>
                    <select id="sunarp-office" wire:model="oficina">
                        <option value="">Seleccione una oficina</option>
                        @foreach($page['oficinas'] as $office)
                            <option value="{{ $office['value'] }}">{{ $office['label'] }}</option>
                        @endforeach
                    </select>
                    @if(empty($page['oficinas'])) <span class="consulta-hint">El catálogo se cargará cuando SUNARP esté disponible.</span> @endif
                    @error('oficina') <span class="field-error" role="alert">{{ $message }}</span> @enderror
                </div>

                <div class="sunarp-main-actions">
                    <span class="field-label-spacer" aria-hidden="true">&nbsp;</span>
                    <div class="sunarp-main-actions-row">
                        <button type="button" class="consulta-button consulta-button-source" wire:click="searchSunarp" wire:loading.attr="disabled" wire:target="searchSunarp,selectPartida">
                            <span wire:loading.remove wire:target="searchSunarp,selectPartida"><x-icon name="search" /> Consultar</span>
                            <span wire:loading.flex wire:target="searchSunarp,selectPartida"><i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Consultando SUNARP…</span>
                        </button>
                        <button type="button" class="consulta-button consulta-button-clear" wire:click="resetSearch" wire:loading.attr="disabled" wire:target="searchSunarp,selectPartida"><x-icon name="eraser" /> Limpiar</button>
                    </div>
                </div>
            </div>
        @endif
    </section>

    @if($statusMessage)
        <div class="consulta-alert {{ $statusType }}" role="status">
            <x-icon :name="$statusType === 'success' ? 'check' : ($statusType === 'danger' ? 'warning' : 'info')" />
            <span>{{ $statusMessage }}</span>
        </div>
    @endif

    @if($partidas)
        <section class="content-wrapper sunarp-partidas-selector" aria-labelledby="partidas-title">
            <div class="sunarp-section-title">
                <div><h2 id="partidas-title"><x-icon name="document" /> Partidas encontradas</h2><p>Selecciona una partida para cargar su detalle.</p></div>
                <span class="sunarp-count">{{ count($partidas) }} registros</span>
            </div>
            <div class="sunarp-partidas-grid" aria-label="Partidas registrales encontradas">
                @foreach($visiblePartidas as $index => $partida)
                    @php
                        $partidaNumero = $partida['numero_partida'] ?? $partida['numeroPartida'] ?? '-';
                        $partidaActiva = ($selectedPartida['numero_partida'] ?? $selectedPartida['numeroPartida'] ?? null) === $partidaNumero;
                        $partidaEstado = $partida['estado'] ?? 'Registrada';
                    @endphp
                    <button
                        type="button"
                        class="sunarp-partida-card {{ $partidaActiva ? 'active' : '' }}"
                        wire:click="selectPartida({{ $index }})"
                        wire:loading.attr="disabled"
                        wire:target="selectPartida"
                        aria-pressed="{{ $partidaActiva ? 'true' : 'false' }}"
                        aria-label="Ver partida número {{ $partidaNumero }}"
                    >
                        <span class="sunarp-partida-number"><x-icon name="document" /> <span>Partida N°</span> <strong>{{ $partidaNumero }}</strong></span>
                        <span class="sunarp-partida-status"><i class="fa-solid fa-circle" aria-hidden="true"></i> {{ $partidaEstado }}</span>
                        <span class="sunarp-partida-office"><i class="fa-solid fa-building" aria-hidden="true"></i> {{ $partida['oficina'] ?? 'Oficina no indicada' }}</span>
                        @if(!empty($partida['numero_placa'] ?? $partida['numeroPlaca'] ?? null))
                            <span class="sunarp-partida-extra"><i class="fa-solid fa-car" aria-hidden="true"></i> Placa {{ $partida['numero_placa'] ?? $partida['numeroPlaca'] }}</span>
                        @elseif(!empty($partida['libro']))
                            <span class="sunarp-partida-extra"><i class="fa-solid fa-book" aria-hidden="true"></i> {{ $partida['libro'] }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
            @if($partidasLastPage > 1)
                <nav class="sunarp-pagination" aria-label="Paginación de partidas">
                    <button type="button" wire:click="setPartidasPage({{ $partidasPage - 1 }})" @disabled($partidasPage === 1)><i class="fa-solid fa-chevron-left" aria-hidden="true"></i> Anterior</button>
                    <span>Página {{ $partidasPage }} de {{ $partidasLastPage }}</span>
                    <button type="button" wire:click="setPartidasPage({{ $partidasPage + 1 }})" @disabled($partidasPage === $partidasLastPage)>Siguiente <i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
                </nav>
            @endif
        </section>
    @endif

    @if($selectedPartida)
        @php
            $record = $selectedPartida;
            $isNatural = $tab === 'natural';
            $identityFields = array_filter([
                'Nombres' => $isNatural ? ($record['nombre'] ?? $selectedPerson['nombres'] ?? null) : null,
                'Apellido Paterno' => $isNatural ? ($record['apPaterno'] ?? $selectedPerson['apellido_paterno'] ?? null) : null,
                'Apellido Materno' => $isNatural ? ($record['apMaterno'] ?? $selectedPerson['apellido_materno'] ?? null) : null,
                'Razón Social' => $tab === 'juridica' ? ($record['razon_social'] ?? $selectedPerson['razon_social'] ?? null) : null,
                'Tipo Documento' => $tab === 'partida' ? null : ($record['tipo_documento'] ?? ($isNatural ? 'DNI' : 'RUC')),
                'Nro. Documento' => $tab === 'partida' ? null : ($record['numero_documento'] ?? ($isNatural ? ($selectedPerson['dni'] ?? null) : ($selectedPerson['ruc'] ?? null))),
                'Nro. Partida' => $record['numero_partida'] ?? $record['numeroPartida'] ?? $busqueda,
                'Nro. Placa' => $record['numero_placa'] ?? $record['numeroPlaca'] ?? null,
                'Estado' => $record['estado'] ?? null,
                'Zona' => $record['zona'] ?? $record['codigo_zona'] ?? null,
                'Libro' => $record['libro'] ?? null,
                'Oficina' => $record['oficina'] ?? null,
                'Dirección' => $record['direccion'] ?? null,
            ], fn ($value) => $value !== null && $value !== '');
            $images = array_values($detail['imagenes'] ?? []);
            $vehicle = $detail['datos_vehiculo'] ?? [];
        @endphp

        <x-consulta-print-header
            entity-logo="sunarp-logo-sin-fondo.png"
            entity="SUNARP"
            title="Consulta de Partida Registral - SUNARP"
            subtitle="Superintendencia Nacional de los Registros Públicos"
        />

        <section class="sunarp-detail-layout {{ $isNatural ? 'with-photo' : '' }}">
            @if($isNatural)
                <article class="content-wrapper consulta-photo-card">
                    <h2><x-icon name="camera" /> Fotografía</h2>
                    <div class="consulta-photo-frame">
                        @if(!empty($selectedPerson['foto']))
                            <img src="{{ str_starts_with($selectedPerson['foto'], 'data:image') ? $selectedPerson['foto'] : 'data:image/jpeg;base64,'.$selectedPerson['foto'] }}" alt="Fotografía de la persona seleccionada">
                        @else
                            <x-icon name="user" /><span>Sin fotografía</span>
                        @endif
                    </div>
                </article>
            @endif
            <article class="content-wrapper consulta-info-card">
                <h2><x-icon name="fa-solid fa-address-card" /> Información Registral</h2>
                <div class="consulta-info-grid">
                    @foreach($identityFields as $label => $value)
                        <div class="consulta-info-item {{ in_array($label, ['Oficina', 'Dirección'], true) ? 'full' : '' }}"><span>{{ $label }}</span><strong class="{{ !$value ? 'is-empty' : '' }}">{{ $value ?: '-' }}</strong></div>
                    @endforeach
                </div>

                <x-consulta-export-actions
                    :token="$infoPdfToken"
                    :can-pdf="(bool) $infoPdfToken"
                    :can-print="true"
                />
            </article>
        </section>

        @if($images)
            <section class="glass sunarp-detail-section sunarp-viewer" x-data="{ image: 0, zoom: 1, previewOpen: false }" x-on:keydown.escape.window="previewOpen = false">
                <div class="sunarp-section-title"><div><h2><x-icon name="fa-solid fa-file-image" /> Visor de Documentos</h2><p>Revisa y descarga las páginas registrales.</p></div></div>
                <div class="sunarp-viewer-toolbar">
                    <label>Página:
                        <select x-model.number="image" x-on:change="zoom = 1">
                            @foreach($images as $index => $image)<option value="{{ $index }}">Página {{ $image['pagina'] ?? $image['numero_secuencial'] ?? $index + 1 }}</option>@endforeach
                        </select>
                    </label>
                    <div class="sunarp-zoom-controls">
                        <button type="button" x-on:click="zoom = Math.max(.5, zoom - .25)" aria-label="Reducir"><i class="fa-solid fa-minus"></i></button>
                        <button type="button" x-on:click="zoom = 1" aria-label="Restaurar"><i class="fa-solid fa-rotate-right"></i></button>
                        <button type="button" x-on:click="zoom = Math.min(3, zoom + .25)" aria-label="Aumentar"><i class="fa-solid fa-plus"></i></button>
                        <span x-text="`${Math.round(zoom * 100)}%`"></span>
                    </div>
                    @foreach($images as $index => $image)
                        @if(!empty($image['imagen_base64']))
                            <div class="sunarp-image-actions" x-show="image === {{ $index }}" x-cloak>
                                <button type="button" class="sunarp-action-view" x-on:click="previewOpen = true" aria-label="Ver página {{ $index + 1 }} en tamaño completo"><i class="fa-solid fa-eye" aria-hidden="true"></i> Ver</button>
                                <a class="sunarp-action-download" href="data:{{ $image['mime'] ?? 'image/jpeg' }};base64,{{ $image['imagen_base64'] }}" download="partida_{{ $record['numero_partida'] ?? $record['numeroPartida'] ?? 'registral' }}_pagina_{{ $index + 1 }}.{{ match ($image['mime'] ?? 'image/jpeg') { 'image/gif' => 'gif', 'image/png' => 'png', 'image/webp' => 'webp', 'image/bmp' => 'bmp', default => 'jpg' } }}"><i class="fa-solid fa-download" aria-hidden="true"></i> Descargar</a>
                            </div>
                        @endif
                    @endforeach
                </div>
                <form method="POST" action="{{ route('consulta.pdf') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $pdfToken }}">
                    <button type="submit" class="sunarp-pdf-button" @disabled(!$pdfToken)>
                        <i class="fa-solid fa-file-pdf" aria-hidden="true"></i> Descargar todas en PDF
                    </button>
                </form>
                <div class="sunarp-image-stage">
                    @foreach($images as $index => $image)
                        @if(!empty($image['imagen_base64']))
                            <div class="sunarp-image-page" x-show="image === {{ $index }}" x-bind:style="`width: ${zoom * 100}%`" x-cloak>
                                <img src="data:{{ $image['mime'] ?? 'image/jpeg' }};base64,{{ $image['imagen_base64'] }}" alt="Página registral {{ $index + 1 }}">
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="sunarp-thumbnails">
                    @foreach($images as $index => $image)
                        @if(!empty($image['imagen_base64']))<button type="button" x-on:click="image = {{ $index }}; zoom = 1" x-bind:class="image === {{ $index }} ? 'active' : ''"><img src="data:{{ $image['mime'] ?? 'image/jpeg' }};base64,{{ $image['imagen_base64'] }}" alt="Miniatura {{ $index + 1 }}"></button>@endif
                    @endforeach
                </div>
                <div class="sunarp-image-preview" x-show="previewOpen" x-cloak x-transition.opacity x-on:click.self="previewOpen = false" role="dialog" aria-modal="true" aria-label="Vista completa de página registral">
                    <button type="button" class="sunarp-preview-close" x-on:click="previewOpen = false" aria-label="Cerrar vista completa"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                    <div class="sunarp-preview-content">
                        @foreach($images as $index => $image)
                            @if(!empty($image['imagen_base64']))<img x-show="image === {{ $index }}" src="data:{{ $image['mime'] ?? 'image/jpeg' }};base64,{{ $image['imagen_base64'] }}" alt="Página registral {{ $index + 1 }} en tamaño completo" loading="lazy">@endif
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if($vehicle)
            <section class="glass sunarp-detail-section">
                <div class="sunarp-section-title"><div><h2><x-icon name="car" /> Información Vehicular</h2></div></div>
                <div class="sunarp-vehicle-grid">
                    @foreach(['placa'=>'Placa','marca'=>'Marca','modelo'=>'Modelo','anoFabricacion'=>'Año','color'=>'Color','nro_motor'=>'N° Motor','carroceria'=>'Carrocería','codCategoria'=>'Categoría','estado'=>'Estado'] as $key => $label)
                        @if(isset($vehicle[$key]) && $vehicle[$key] !== '')<div><span>{{ $label }}</span><strong>{{ $vehicle[$key] }}</strong></div>@endif
                    @endforeach
                </div>
            </section>
        @endif
    @endif

    @if($activeModal)
        <div class="sunarp-modal-backdrop" data-ui-modal wire:click.self="closeModal" x-data x-on:keydown.escape.window="$wire.closeModal()" x-init="$nextTick(() => $refs.searchInput?.focus())">
            <section class="sunarp-modal-panel" role="dialog" aria-modal="true" aria-labelledby="sunarp-modal-title">
                <header><h2 id="sunarp-modal-title"><x-icon :name="$activeModal === 'natural' ? 'user' : 'building'" /> Búsqueda de Personas {{ $activeModal === 'natural' ? 'Naturales' : 'Jurídicas' }}</h2><button type="button" wire:click="closeModal" aria-label="Cerrar"><x-icon name="close" /></button></header>
                <div class="sunarp-modal-body">
                    @if($activeModal === 'natural')
                        <form wire:submit="searchNatural" class="sunarp-modal-form">
                            <div class="field"><label for="natural-dni">DNI:</label><input x-ref="searchInput" id="natural-dni" wire:model="naturalDni" inputmode="numeric" maxlength="8" placeholder="Ingrese 8 dígitos">@error('naturalDni')<span class="field-error">{{ $message }}</span>@enderror</div>
                            <input type="hidden" wire:model="dniUsuario">
                            <div class="sunarp-modal-actions"><button class="consulta-button consulta-button-source" type="submit" wire:loading.attr="disabled" wire:target="searchNatural"><span wire:loading.remove wire:target="searchNatural"><x-icon name="search" /> Buscar</span><span wire:loading.flex wire:target="searchNatural"><i class="fa-solid fa-spinner fa-spin"></i> Buscando…</span></button><button class="consulta-button consulta-button-clear" type="button" wire:click="$set('naturalDni', '')"><x-icon name="eraser" /> Limpiar</button></div>
                        </form>
                    @else
                        <form wire:submit="searchJuridica" class="sunarp-modal-form">
                            <div class="sunarp-juridica-mode"><label><input type="radio" wire:model.live="juridicaMode" value="ruc"><span>Por RUC</span></label><label><input type="radio" wire:model.live="juridicaMode" value="razonSocial"><span>Por razón social</span></label></div>
                            <div class="field"><label for="juridica-query">{{ $juridicaMode === 'ruc' ? 'RUC:' : 'Razón Social:' }}</label><input x-ref="searchInput" id="juridica-query" wire:model="juridicaQuery" inputmode="{{ $juridicaMode === 'ruc' ? 'numeric' : 'text' }}" maxlength="{{ $juridicaMode === 'ruc' ? 11 : 255 }}" placeholder="{{ $juridicaMode === 'ruc' ? 'Ingrese 11 dígitos' : 'Ingrese la razón social' }}">@error('juridicaQuery')<span class="field-error">{{ $message }}</span>@enderror</div>
                            <div class="sunarp-modal-actions"><button class="consulta-button consulta-button-source" type="submit" wire:loading.attr="disabled" wire:target="searchJuridica"><span wire:loading.remove wire:target="searchJuridica"><x-icon name="search" /> Buscar</span><span wire:loading.flex wire:target="searchJuridica"><i class="fa-solid fa-spinner fa-spin"></i> Buscando…</span></button><button class="consulta-button consulta-button-clear" type="button" wire:click="$set('juridicaQuery', '')"><x-icon name="eraser" /> Limpiar</button></div>
                        </form>
                    @endif

                    @if($people)
                        <div class="sunarp-modal-results"><p><strong>{{ count($people) }} resultado(s)</strong> obtenidos de {{ $activeModal === 'natural' ? 'RENIEC' : 'SUNAT' }}</p><div class="sunarp-partidas-table-wrap"><table class="sunarp-table"><thead><tr>@if($activeModal === 'natural')<th>DNI</th><th>Nombres completos</th><th>Foto</th>@else<th>RUC</th><th>Razón Social</th><th>Estado</th><th>Condición</th>@endif<th>Acción</th></tr></thead><tbody>
                            @foreach($people as $index => $person)
                                <tr>@if($activeModal === 'natural')<td data-label="DNI"><strong>{{ $person['dni'] ?? '-' }}</strong></td><td data-label="Nombres">{{ $person['nombres_completos'] ?? trim(($person['nombres'] ?? '').' '.($person['apellido_paterno'] ?? '').' '.($person['apellido_materno'] ?? '')) }}</td><td data-label="Foto">@if(!empty($person['foto']))<img class="sunarp-person-thumb" src="{{ str_starts_with($person['foto'], 'data:image') ? $person['foto'] : 'data:image/jpeg;base64,'.$person['foto'] }}" alt="">@else—@endif</td>@else
                                    @php
                                        $esActivo = isset($person['estado_activo']) ? in_array(strtoupper($person['estado_activo']), ['SI','SÍ','ACTIVO','S']) : ($person['estado_contribuyente'] ?? null);
                                        $esActivoLabel = isset($person['estado_activo']) ? ($esActivo ? 'Activo' : 'No Activo') : ($person['estado_contribuyente'] ?? '-');
                                        $esHabido = isset($person['estado_habido']) ? in_array(strtoupper($person['estado_habido']), ['SI','SÍ','HABIDO','S']) : ($person['condicion_domicilio'] ?? null);
                                        $esHabidoLabel = isset($person['estado_habido']) ? ($esHabido ? 'Habido' : 'No Habido') : ($person['condicion_domicilio'] ?? '-');
                                    @endphp
                                    <td data-label="RUC"><strong>{{ $person['ruc'] ?? '-' }}</strong></td><td data-label="Razón social">{{ $person['razon_social'] ?? '-' }}</td><td data-label="Estado"><span class="sunarp-status-badge {{ $esActivo ? 'success' : 'danger' }}">{{ $esActivoLabel }}</span></td><td data-label="Condición"><span class="sunarp-status-badge {{ $esHabido ? 'info' : 'danger' }}">{{ $esHabidoLabel }}</span></td>@endif<td data-label="Acción"><button type="button" class="sunarp-select-button" wire:click="selectPerson({{ $index }})">Seleccionar</button></td></tr>
                            @endforeach
                        </tbody></table></div></div>
                    @endif
                </div>
            </section>
        </div>
    @endif
</div>
