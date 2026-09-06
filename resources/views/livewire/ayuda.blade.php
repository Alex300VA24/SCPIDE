<div
    class="modulo-legacy ayuda-legacy"
    x-data="{
        modalOpen: false,
        activeGroup: null,
        activeItem: null,
        query: '',
        lastTrigger: null,
        guides: @js($groups),
        active() { return this.activeGroup === null ? null : this.guides[this.activeGroup]?.items[this.activeItem] ?? null; },
        matches(item) {
            const term = this.query.trim().toLocaleLowerCase('es');
            return !term || `${item.title} ${item.desc}`.toLocaleLowerCase('es').includes(term);
        },
        groupHasMatches(group) { return group.items.some(item => this.matches(item)); },
        matchCount() { return this.guides.flatMap(group => group.items).filter(item => this.matches(item)).length; },
        openGuide(g, i, trigger) { this.activeGroup = g; this.activeItem = i; this.lastTrigger = trigger; this.modalOpen = true; },
        closeGuide() {
            this.modalOpen = false;
            this.$nextTick(() => this.lastTrigger?.focus());
        },
    }"
    @keydown.escape.window="if (modalOpen) closeGuide()"
>
    <section class="ayuda-intro" aria-labelledby="ayuda-title">
        <span class="ayuda-intro-mark" aria-hidden="true"><i class="fa-solid fa-book-open"></i></span>
        <div class="ayuda-intro-copy">
            <span class="ayuda-intro-eyebrow">Documentación de servicios municipales</span>
            <h2 id="ayuda-title">Centro de ayuda PIDE</h2>
            <p>Encuentra instrucciones claras para utilizar las consultas interinstitucionales y administrar el portal de la Municipalidad Distrital de La Esperanza.</p>
        </div>
        <span class="badge badge-info ayuda-version"><i class="fa-solid fa-file-lines" aria-hidden="true"></i> Manual v2.1</span>
    </section>

    <div class="content-wrapper ayuda-content">
        <div class="ayuda-tools" role="search">
            <div class="ayuda-search">
                <label class="sr-only" for="ayuda-search-input">Buscar en las guías</label>
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input
                    id="ayuda-search-input"
                    type="search"
                    x-model.debounce.150ms="query"
                    placeholder="Buscar una guía, consulta o tarea…"
                    autocomplete="off"
                >
                <button type="button" x-cloak x-show="query" @click="query = ''; $nextTick(() => $el.closest('.ayuda-search').querySelector('input').focus())" aria-label="Limpiar búsqueda">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>
            <p class="ayuda-result-count" aria-live="polite"><strong x-text="matchCount()"></strong> <span x-text="matchCount() === 1 ? 'guía disponible' : 'guías disponibles'"></span></p>
        </div>

        <nav class="ayuda-index" aria-label="Categorías de ayuda">
            @foreach ($groups as $gi => $group)
                <a href="#ayuda-grupo-{{ $gi }}" x-show="groupHasMatches(guides[{{ $gi }}])">
                    <i class="{{ $group['icon'] }}" aria-hidden="true"></i>
                    {{ $group['label'] }}
                </a>
            @endforeach
        </nav>

        @foreach ($groups as $gi => $group)
            <section id="ayuda-grupo-{{ $gi }}" class="ayuda-group" x-show="groupHasMatches(guides[{{ $gi }}])">
                <h2 class="section-header ayuda-section-header"><span class="ayuda-section-icon"><i class="{{ $group['icon'] }}" aria-hidden="true"></i></span> {{ $group['label'] }}</h2>

                <div class="ayuda-grid">
                    @foreach ($group['items'] as $ii => $item)
                        <button
                            type="button"
                            class="ayuda-card"
                            x-show="matches(guides[{{ $gi }}].items[{{ $ii }}])"
                            @click="openGuide({{ $gi }}, {{ $ii }}, $el)"
                            aria-haspopup="dialog"
                            style="--ayuda-order: {{ $ii }}"
                        >
                            <span class="ayuda-card-top">
                                <span class="ayuda-chip {{ $item['chip'] }}"><i class="{{ $item['icon'] }}" aria-hidden="true"></i></span>
                                <span class="ayuda-open-icon" aria-hidden="true"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                            </span>
                            <span class="ayuda-card-title">{{ $item['title'] }}</span>
                            <span class="ayuda-card-desc">{{ $item['desc'] }}</span>
                            <span class="ayuda-card-cta">Ver guía <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                        </button>
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="ayuda-empty" x-cloak x-show="matchCount() === 0" role="status">
            <span><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
            <h2>No encontramos esa guía</h2>
            <p>Prueba con términos como DNI, RUC, SUNARP, MTC, papeletas, coactiva, ambientales, CONADIS, contraseña, usuario o módulo.</p>
            <button type="button" class="btn btn-secondary" @click="query = ''">Ver todas las guías</button>
        </div>
    </div>

    <div
        data-ui-modal
        class="modal-backdrop"
        role="presentation"
        x-cloak
        x-show="modalOpen"
        x-transition:enter="ease-out duration-250"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-effect="if (modalOpen) $nextTick(() => $refs.ayudaModalClose?.focus())"
        @click.self="closeGuide()"
    >
        <section
            class="modal-panel ayuda-modal-panel"
            role="dialog"
            aria-modal="true"
            aria-labelledby="ayuda-dialog-title"
            x-show="modalOpen"
            x-transition:enter="ease-out duration-250"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            @keydown.tab.prevent="$refs.ayudaModalClose?.focus()"
        >
            <template x-if="active()">
                <div>
                    <div class="modal-heading">
                        <div class="ayuda-modal-head">
                            <span class="ayuda-chip" :class="active().chip"><i :class="active().icon" aria-hidden="true"></i></span>
                            <h2 id="ayuda-dialog-title" x-text="active().title"></h2>
                        </div>
                        <button type="button" class="modal-close" x-ref="ayudaModalClose" @click="closeGuide()" aria-label="Cerrar guía">
                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="modal-panel-body ayuda-body">
                        <p x-text="active().desc"></p>

                        <ol class="ayuda-steps">
                            <template x-for="(step, index) in active().steps" :key="index">
                                <li x-text="step"></li>
                            </template>
                        </ol>

                        <ul class="ayuda-tips" x-show="active().tips.length">
                            <template x-for="(tip, index) in active().tips" :key="index">
                                <li><i class="fa-solid fa-lightbulb ayuda-tip-icon" aria-hidden="true"></i><span><strong>Consejo:</strong> <span x-text="tip"></span></span></li>
                            </template>
                        </ul>

                        <p class="ayuda-note" x-show="active().note">
                            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> <span x-text="active().note"></span>
                        </p>
                    </div>
                </div>
            </template>
        </section>
    </div>
</div>
