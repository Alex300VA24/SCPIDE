<div class="modulo-legacy consulta-legacy page" style="--source:#166534">
    <section class="page-title consulta-page-title">
        <span class="consulta-source-logo"><img src="{{ asset('assets/images/senace-logo-sin-fondo.png') }}" alt="SENACE"></span>
        <div>
            <h1>Certificaciones Ambientales - SENACE</h1>
            <p>Registro Administrativo de Certificaciones Ambientales de proyectos evaluados o en evaluación</p>
        </div>
    </section>

    <section class="content-wrapper consulta-search-card">
        <h2 class="section-header consulta-section-header"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Parámetros de consulta</h2>
        <form wire:submit="buscar" class="consulta-search-form" novalidate>
            @php
                $igaOptions = [
                    '' => 'Seleccione…',
                    '01' => 'Plan de Participación Ciudadana',
                    '03' => 'Estudio de Impacto Ambiental Detallado',
                    '04' => 'Informe Técnico Sustentatorio',
                    '05' => 'Modificación de EIA Detallado',
                    '09' => 'IGAPRO',
                    '10' => 'Declaración de Impacto Ambiental',
                    '11' => 'Modificación de Declaración de Impacto Ambiental',
                    '12' => 'Estudio de Impacto Ambiental',
                    '13' => 'Modificación de Estudio de Impacto Ambiental',
                    '14' => 'Estudio de Impacto Ambiental Semidetallado',
                    '15' => 'Modificación de EIA Semidetallado',
                    '16' => 'Evaluación Ambiental Estratégica',
                    '17' => 'Plan Ambiental',
                    '18' => 'Plan de Compensación y Reasentamiento',
                    '19' => 'Plan de Gestión Ambiental',
                    '20' => 'Plan de Manejo Ambiental',
                    '21' => 'Plan de Adecuación Ambiental',
                    '22' => 'Plan de Abandono',
                    '23' => 'Complementario',
                ];
            @endphp
            <div class="field">
                <label for="tipoIga"><x-icon name="document" /> Tipo de instrumento (IGA)</label>
                <div class="ss-select" x-data="{ open: false, q: '' }" @click.outside="open = false">
                    <button type="button" id="tipoIga" class="ss-select-trigger" @click="open = !open; if (open) { q = ''; $nextTick(() => $refs.igaSearch?.focus()); }" :aria-expanded="open.toString()" aria-haspopup="listbox">
                        <span class="ss-select-label">{{ $igaOptions[$tipoIga] ?? 'Seleccione…' }}</span>
                        <i class="fa-solid fa-chevron-down ss-select-caret" aria-hidden="true"></i>
                    </button>
                    <div class="ss-select-list" x-cloak x-show="open" x-transition.origin.top role="listbox" aria-label="Tipo de instrumento IGA">
                        <div class="ss-select-search">
                            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                            <input type="search" x-ref="igaSearch" x-model="q" placeholder="Buscar tipo de instrumento..." aria-label="Buscar tipo de instrumento">
                        </div>
                        <ul class="ss-select-options">
                            @foreach ($igaOptions as $value => $label)
                                <li role="option" aria-selected="{{ $tipoIga === $value ? 'true' : 'false' }}" x-show="!q || '{{ addslashes($label) }}'.toLowerCase().includes(q.toLowerCase())">
                                    <button type="button" class="ss-select-option {{ $tipoIga === $value ? 'is-selected' : '' }}" wire:click="$set('tipoIga', '{{ $value }}')" @click="open = false">
                                        {{ $label }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @error('tipoIga') <span class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            <div class="field consulta-main-field">
                <label for="expediente"><x-icon name="document" /> N° de expediente</label>
                <input id="expediente" type="text" wire:model="expediente" maxlength="50" autocomplete="off" placeholder="Ej. 2998007">
                @error('expediente') <span class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="grupoSector"><x-icon name="archive" /> Grupo sector</label>
                <select id="grupoSector" wire:model="grupoSector">
                    <option value="">Seleccione…</option>
                    <option value="1">Energía y Minas</option>
                    <option value="2">Transportes y Comunicaciones</option>
                    <option value="3">Agricultura</option>
                    <option value="4">Salud</option>
                    <option value="5">Vivienda, Construcción y Saneamiento</option>
                </select>
                @error('grupoSector') <span class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="subSector"><x-icon name="archive" /> Subsector</label>
                <select id="subSector" wire:model="subSector">
                    <option value="">Seleccione…</option>
                    <option value="1">Minería</option>
                    <option value="3">Transportes</option>
                    <option value="4">Agricultura</option>
                    <option value="5">Salud</option>
                    <option value="6">Vivienda</option>
                    <option value="7">Electricidad</option>
                    <option value="8">Hidrocarburos</option>
                </select>
                @error('subSector') <span class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="actividad"><x-icon name="archive" /> Actividad</label>
                <select id="actividad" wire:model="actividad">
                    <option value="">Seleccione…</option>
                    <option value="1">Minería</option>
                    <option value="3">Transportes</option>
                    <option value="4">Agricultura - Riego</option>
                    <option value="6">Salud</option>
                    <option value="11">Electricidad</option>
                    <option value="12">Hidrocarburos</option>
                </select>
                @error('actividad') <span class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="nroRuc"><x-icon name="id" /> RUC del titular (opcional)</label>
                <input id="nroRuc" type="text" wire:model="nroRuc" maxlength="11" inputmode="numeric" autocomplete="off"
                    x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').slice(0, 11)">
                @error('nroRuc') <span class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="titular"><x-icon name="user" /> Titular (opcional)</label>
                <input id="titular" type="text" wire:model="titular" autocomplete="off">
            </div>

            <div class="field">
                <label for="nomProyecto"><x-icon name="document" /> Nombre del proyecto (opcional)</label>
                <input id="nomProyecto" type="text" wire:model="nomProyecto" autocomplete="off">
            </div>

            <div class="field">
                <label for="nroCatalogo"><x-icon name="archive" /> N° de catálogo (opcional)</label>
                <input id="nroCatalogo" type="text" wire:model="nroCatalogo" autocomplete="off">
            </div>

            <div class="field">
                <label for="resolucion"><x-icon name="document" /> N° de resolución (opcional)</label>
                <input id="resolucion" type="text" wire:model="resolucion" autocomplete="off">
            </div>

            <div class="field">
                <label for="idDepa"><x-icon name="home" /> Ubigeo - Depa. (opcional)</label>
                <input id="idDepa" type="text" wire:model="idDepa" maxlength="2" inputmode="numeric" autocomplete="off"
                    x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').slice(0, 2)">
                @error('idDepa') <span class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="idProv"><x-icon name="home" /> Ubigeo - Prov. (opcional)</label>
                <input id="idProv" type="text" wire:model="idProv" maxlength="2" inputmode="numeric" autocomplete="off"
                    x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').slice(0, 2)">
                @error('idProv') <span class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="idDist"><x-icon name="home" /> Ubigeo - Dist. (opcional)</label>
                <input id="idDist" type="text" wire:model="idDist" maxlength="2" inputmode="numeric" autocomplete="off"
                    x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').slice(0, 2)">
                @error('idDist') <span class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            <div class="consulta-search-actions">
                <span class="field-label-spacer" aria-hidden="true">&nbsp;</span>
                <div class="consulta-search-actions-row">
                    <button class="consulta-button consulta-button-source" type="submit" wire:loading.attr="disabled" wire:target="buscar">
                        <span wire:loading.remove wire:target="buscar"><x-icon name="search" /> Buscar</span>
                        <span wire:loading.flex wire:target="buscar"><i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Consultando…</span>
                    </button>
                    <button class="consulta-button consulta-button-clear" type="button" wire:click="resetSearch" wire:loading.attr="disabled" wire:target="buscar" aria-label="Limpiar consulta">
                        <x-icon name="eraser" /><span class="sr-only">Limpiar</span>
                    </button>
                </div>
            </div>
        </form>
    </section>

    @if($successMessage || $errorMessage)
        <div class="consulta-alert {{ $successMessage ? 'success' : ($real ? 'danger' : 'warning') }}" role="status">
            <x-icon :name="$successMessage ? 'check' : 'warning'" />
            <span>{{ $successMessage ?? $errorMessage }}</span>
        </div>
    @endif

    @if($searched && $real && !empty($certificaciones))
        <x-consulta-print-header
            entity-logo="senace-logo-sin-fondo.png"
            entity="SENACE"
            title="Certificaciones Ambientales - SENACE"
            subtitle="Registro Administrativo de Certificaciones Ambientales"
        />

        <section class="consulta-legacy-results" aria-live="polite">
            @foreach($certificaciones as $cert)
                @php
                    $ubicacion = collect([$cert['ubigeo']['departamento'] ?? null, $cert['ubigeo']['provincia'] ?? null, $cert['ubigeo']['distrito'] ?? null])->filter()->implode(' / ');
                    $certCampos = [
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
                    ];
                @endphp
                <article class="content-wrapper consulta-info-card" style="width: 100%;">
                    <h2><x-icon name="leaf" /> {{ $cert['nombre_proyecto'] ?: 'Certificación ambiental' }}</h2>
                    <div class="consulta-info-grid">
                        @foreach($certCampos as $etiqueta => $valor)
                            <div class="consulta-info-item"><span>{{ $etiqueta }}</span><strong class="{{ $valor === '' ? 'is-empty' : '' }}">{{ $valor !== '' ? $valor : '-' }}</strong></div>
                        @endforeach
                        <div class="consulta-info-item full"><span>Ubicación</span><strong class="{{ $ubicacion === '' ? 'is-empty' : '' }}">{{ $ubicacion !== '' ? $ubicacion : '-' }}</strong></div>
                    </div>

                    @if(!empty($cert['v_acceso']) || !empty($cert['v_lineaBase']))
                        <div class="consulta-info-links">
                            @foreach($cert['v_acceso'] as $enlace)
                                @if(is_string($enlace))
                                    <a href="{{ $enlace }}" target="_blank" rel="noopener noreferrer" class="consulta-button consulta-button-clear"><x-icon name="document" /> Acceso</a>
                                @endif
                            @endforeach
                            @foreach($cert['v_lineaBase'] as $enlace)
                                @if(is_string($enlace))
                                    <a href="{{ $enlace }}" target="_blank" rel="noopener noreferrer" class="consulta-button consulta-button-clear"><x-icon name="document" /> Línea base</a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </article>
            @endforeach

            <x-consulta-export-actions :token="$pdfToken" :can-pdf="(bool) $pdfToken" :can-print="true" />
        </section>
    @endif
</div>
