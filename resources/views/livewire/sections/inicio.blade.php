<div class="inicio-container">
    <header class="inicio-heading">
        <span class="inicio-heading-mark" aria-hidden="true"><x-icon name="grid" /></span>
        <div class="inicio-heading-copy">
            <span class="inicio-eyebrow">Directorio institucional</span>
            <h2>Consultas interoperables</h2>
            <p>Selecciona una fuente de información para iniciar una consulta segura con las entidades conectadas al sistema PIDE.</p>
        </div>
        <span class="badge badge-info inicio-access-note"><x-icon name="shield" /> Acceso autorizado</span>
    </header>

    <section class="services-grid" aria-label="Consultas disponibles">
        @foreach ([
            ['key'=>'dni','name'=>'RENIEC','tag'=>'Registro Nacional','description'=>'Registro Nacional de Identificación y Estado Civil','class'=>'reniec','accent'=>'#7c3aed','logo'=>'reniec-logo-sin-fondo.png','badge'=>'shield','features'=>['Consulta por DNI','Datos personales','Estado del documento','Foto y firma digital']],
            ['key'=>'ruc','name'=>'SUNAT','tag'=>'Administración Tributaria','description'=>'Superintendencia Nacional de Aduanas y de Administración Tributaria','class'=>'sunat','accent'=>'#c81e1e','logo'=>'sunat-logo-sin-fondo.png','badge'=>'calculator','features'=>['Consulta por RUC','Razón social','Estado del contribuyente','Domicilio fiscal']],
            ['key'=>'partidas','name'=>'SUNARP','tag'=>'Registros Públicos','description'=>'Superintendencia Nacional de los Registros Públicos','class'=>'sunarp','accent'=>'#0f8a62','logo'=>'sunarp-logo-sin-fondo.png','badge'=>'archive','features'=>['Consulta registral','Propiedades inmuebles','Vehículos registrados','Personas jurídicas']],
            ['key'=>'ruc','tab'=>'ccoactiva','name'=>'Cobranza Coactiva','tag'=>'Administración Tributaria','description'=>'Deudas en cobranza coactiva administradas por SUNAT','class'=>'sunat','accent'=>'#c81e1e','logo'=>'sunat-logo-sin-fondo.png','badge'=>'shield','features'=>['Consulta por DNI o RUC','Entidad de la deuda','Periodo tributario','Monto de la deuda']],
            ['key'=>'cert-ambientales','name'=>'Certificaciones Ambientales','tag'=>'Gestión Ambiental','description'=>'Registro Administrativo de Certificaciones Ambientales - SENACE','class'=>'senace','accent'=>'#167342','logo'=>'senace-logo-sin-fondo.png','badge'=>'archive','features'=>['Consulta por expediente','Sector y actividad','Estado de evaluación','Enlaces al expediente digital']],
            ['key'=>'mtc','name'=>'MTC','tag'=>'Transportes y Comunicaciones','description'=>'Récord de conductor: licencias, papeletas y sanciones vigentes','class'=>'mtc','accent'=>'#1d5fbf','logo'=>'mtc-logo-sin-fondo.png','badge'=>'shield','features'=>['Consulta por DNI o CE','Última licencia emitida','Papeletas aplicadas','Sanciones vigentes']],
            ['key'=>'conadis','name'=>'CONADIS','tag'=>'Inclusión y discapacidad','description'=>'Registro Nacional de Personas con Discapacidad','class'=>'conadis','accent'=>'#1769aa','logo'=>'logo_conadis.png','badge'=>'shield','features'=>['Consulta individual por DNI','Estado de inscripción','Gravedad registrada','Condición de fallecimiento']],
        ] as $service)
            @if ($this->canReach($service['key']))
            <article class="service-card {{ $service['class'] }}-card" style="--service-order:{{ $loop->index }};--card-accent:{{ $service['accent'] }}">
                <div class="service-card-body">
                    <div class="service-card-top">
                        <span class="service-logo"><img src="{{ asset('assets/images/'.$service['logo']) }}" alt="{{ $service['name'] }}" loading="lazy"></span>
                    </div>

                    <div class="service-card-heading">
                        <span>{{ $service['tag'] }}</span>
                        <h3>{{ $service['name'] }}</h3>
                    </div>

                    <p class="service-description">{{ $service['description'] }}</p>

                    <ul class="service-features">
                        @foreach ($service['features'] as $feature)
                            <li><span class="feature-check"><x-icon name="check" /></span>{{ $feature }}</li>
                        @endforeach
                    </ul>

                    <button type="button" wire:click="selectSection('{{ $service['key'] }}', '{{ $service['tab'] ?? '' }}')" class="service-btn btn-{{ $service['class'] }}">
                        <x-icon name="search" /> Consultar {{ $service['name'] }}
                    </button>
                </div>
            </article>
            @endif
        @endforeach
    </section>

    <footer class="inicio-footer glass">
        <p><x-icon name="info" /> <strong>Sistema de Consultas PIDE v2.0</strong> <span class="sep">|</span> Plataforma de Interoperabilidad del Estado Peruano</p>
        <p class="footer-note">Acceso autorizado únicamente para entidades del Estado</p>
    </footer>
</div>
