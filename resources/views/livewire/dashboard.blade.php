<div
    class="dashboard-container"
    x-data="dashboardUi($wire, @js($this->sectionTitle()))"
    x-init="$watch('navigationOpen', value => document.body.classList.toggle('overflow-y-hidden', value))"
    @click.capture="const target = $event.target.closest('[data-navigation-title]'); if (target && $root.contains(target)) loadingSectionTitle = target.dataset.navigationTitle"
    @close-dashboard-navigation.window="navigationOpen = false"
    @keydown.escape.window="navigationOpen = false; logoutOpen = false"
>
    <button type="button" class="mobile-menu-btn" @click="navigationOpen = true" aria-label="Abrir menú" :aria-expanded="navigationOpen.toString()">
        <x-icon name="menu" />
    </button>

    <div class="sidebar-overlay" x-cloak x-show="navigationOpen" x-transition.opacity @click="navigationOpen = false" aria-hidden="true"></div>

    <aside class="sideBar" :class="{ 'mobile-open': navigationOpen, 'collapsed': sidebarCollapsed }" aria-label="Navegación principal">
        <header class="sidebar-header">
            <button type="button" class="sidebar-close-btn" @click="navigationOpen = false" aria-label="Cerrar menú"><x-icon name="close" /></button>
            <img src="{{ asset('assets/images/muni2.png') }}" alt="Plataforma de Interoperabilidad del Estado" class="w-9 h-9 object-contain">
            <div class="sidebar-brand-text">
                <div class="sidebar-title font-heading">MDE</div>
                <div class="sidebar-subtitle">SCPIDE</div>
            </div>
        </header>

        <nav class="sidebar-nav">
            <div class="sidebar-navigation-list" role="list">
            @forelse ($sections as $module)
                @if (empty($module['children']))
                    <div class="option-wrap" role="listitem">
                        <button
                            type="button"
                            wire:click="selectSection('{{ $module['key'] }}')"
                            data-navigation-title="{{ trim($module['label'].(! empty($module['tabs']) ? ' · '.$module['tabs'][0]['label'] : '')) }}"
                            @click="navigationOpen = false"
                            class="option {{ $activeSection === $module['key'] ? 'active' : '' }}"
                            @if ($activeSection === $module['key']) aria-current="page" @endif
                        >
                            <span class="containerIconOption"><x-icon :name="$module['icon']" /></span>
                            <span class="sidebar-label">{{ $module['label'] }}</span>
                        </button>
                        <span class="option-tooltip">{{ $module['label'] }}</span>
                    </div>
                @else
                    <div x-data="{ open: {{ collect($module['children'])->contains('key', $activeSection) ? 'true' : 'false' }} }" class="sidebar-branch option-wrap" role="listitem">
                        <button
                            type="button"
                            class="option has-submenu"
                            :class="{ 'open': open }"
                            @click="if (sidebarCollapsed) { sidebarCollapsed = false; localStorage.setItem('sidebar_collapsed', false); open = true; } else { open = !open; }"
                            :aria-expanded="open.toString()"
                        >
                            <span class="containerIconOption"><x-icon :name="$module['icon']" /></span>
                            <span class="sidebar-label">{{ $module['label'] }}</span>
                            <span class="submenu-icon" aria-hidden="true"><x-icon name="chevron" /></span>
                        </button>
                        <span class="option-tooltip">{{ $module['label'] }}</span>
                        <div class="submenu" x-cloak x-show="open" x-transition.origin.top>
                            @foreach ($module['children'] as $child)
                                <button
                                    type="button"
                                    wire:click="selectSection('{{ $child['key'] }}')"
                                    data-navigation-title="{{ trim($child['label'].(! empty($child['tabs']) ? ' · '.$child['tabs'][0]['label'] : '')) }}"
                                    @click="navigationOpen = false"
                                    class="suboption {{ $activeSection === $child['key'] ? 'active' : '' }}"
                                    @if ($activeSection === $child['key']) aria-current="page" @endif
                                >
                                    <span><x-icon :name="$child['icon']" /></span>
                                    <span>{{ $child['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            @empty
                <p class="navigation-empty">No existen módulos asignados.</p>
            @endforelse
            </div>
        </nav>

        <div class="user-section">
            <div class="user-info">
                <span class="user-avatar">{{ mb_substr(auth()->user()->username, 0, 1) }}</span>
                <span class="user-details">
                    <strong class="user-name">{{ auth()->user()->username }}</strong>
                    <small class="user-role">{{ auth()->user()->roles->pluck('nombre')->join(', ') ?: 'Sin rol' }}</small>
                </span>
            </div>
            <button type="button" class="logout-btn" @click="openLogout()"><x-icon name="logout" /><span class="logout-text">Cerrar Sesión</span></button>
        </div>
    </aside>

    <button type="button" class="sidebar-toggle-btn" x-show="!navigationOpen" @click="toggleCollapse()" :style="{ left: (sidebarCollapsed ? 62 : 226) + 'px' }" aria-label="Contraer/Expandir menú">
        <span :class="{ 'is-collapsed': sidebarCollapsed }" class="toggle-icon"><x-icon name="collapse" /></span>
    </button>

    <main id="contenido" class="main-content" :class="{ 'collapsed': sidebarCollapsed }" tabindex="-1" aria-live="polite">
        <header class="dashboard-header glass">
            <div class="dashboard-header-brand">
                <img src="{{ asset('assets/images/logo-pide-2-sin-fondo.png') }}"
                    alt="Plataforma de Interoperabilidad del Estado"
                    class="dashboard-header-logo">
                <div>
                    <span class="dashboard-header-eyebrow">Municipalidad Distrital de La Esperanza</span>
                    <h1>Sistema de Consultas PIDE</h1>
                    <p>Consultas interinstitucionales PIDE para una atención pública ágil y segura</p>
                </div>
            </div>
            <div class="dashboard-header-meta">
                <span class="header-date"><x-icon name="calendar" />{{ now()->format('d/m/Y H:i') }}</span>
            </div>
        </header>

        <div class="spa-status" wire:loading.flex wire:target="selectSection, selectTab" role="status" aria-live="polite">
            <div class="spa-status-card">
                <div class="spa-status-visual" aria-hidden="true">
                    <span class="spa-status-ring"></span>
                    <img class="spa-status-logo" src="{{ asset('assets/images/logo_pide_sin_texto.png') }}" alt="">
                </div>
                <span class="spa-status-kicker">Sistema PIDE</span>
                <h2 class="spa-status-title">Cargando sección</h2>
                <p class="spa-status-subtitle">Preparando el módulo de <span x-text="loadingSectionTitle">{{ $this->sectionTitle() }}</span>…</p>
                <div class="spa-status-progress" aria-hidden="true"><span></span></div>
            </div>
        </div>
        <div wire:loading.remove wire:target="selectSection, selectTab" class="page-content active" wire:key="section-{{ $this->renderKey() }}">
            @php($activeModule = $this->activeModule())

            @if (! empty($activeModule['tabs']))
                <div class="module-tabs" role="tablist" aria-label="{{ $activeModule['label'] ?? '' }}">
                    @foreach ($activeModule['tabs'] as $tab)
                        <button
                            type="button"
                            role="tab"
                            wire:click="selectTab('{{ $tab['key'] }}')"
                            data-navigation-title="{{ trim(($activeModule['label'] ?? '').' · '.$tab['label']) }}"
                            class="module-tab {{ $this->activeTab === $tab['key'] ? 'active' : '' }}"
                            aria-selected="{{ $this->activeTab === $tab['key'] ? 'true' : 'false' }}"
                            @if ($this->activeTab === $tab['key']) aria-current="true" @endif
                        >
                            <x-icon :name="$tab['icon']" />
                            <span>{{ $tab['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            @endif

            @php($renderKey = $this->renderKey())
            @if ($renderKey === 'password')
                @include('livewire.sections.password')
            @elseif ($renderKey === 'inicio')
                @include('livewire.sections.inicio')
            @elseif ($component = $this->componentFor($renderKey))
                <livewire:dynamic-component :component="$component" :key="$renderKey" />
            @else
                @include('livewire.sections.construccion')
            @endif
        </div>
    </main>

    <div data-ui-modal class="modal-overlay pide-credential-overlay" x-cloak x-show="logoutOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" role="presentation" @click.self="closeLogout()">
        <section x-ref="logoutDialog" class="modal-content pide-credential-modal pide-logout-modal" role="dialog" aria-modal="true" aria-labelledby="logout-title" aria-describedby="logout-description" x-show="logoutOpen" @keydown.tab="trapLogoutFocus($event)" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-4 scale-95">
            <header class="pide-credential-header pide-logout-header">
                <div class="pide-credential-heading">
                    <span class="pide-credential-icon pide-logout-icon"><x-icon name="logout" /></span>
                    <div>
                        <h2 id="logout-title">¿Cerrar sesión?</h2>
                        <p id="logout-description">¿Deseas cerrar la sesión actual?</p>
                    </div>
                </div>
                <button type="button" class="pide-modal-close" aria-label="Cerrar modal" @click="closeLogout()">
                    <x-icon name="close" />
                </button>
            </header>

            <div class="pide-credential-form">
                <p class="pide-fallback-copy">Se cerrará tu sesión y volverás a la pantalla de acceso. Deberás iniciar sesión nuevamente para continuar.</p>

                <div class="pide-credential-actions">
                    <button x-ref="logoutCancel" type="button" class="pide-modal-button pide-modal-secondary" @click="closeLogout()">Cancelar</button>
                    <form method="POST" action="{{ route('logout') }}" data-logout-form @submit.prevent="submitLogout()">
                        @csrf
                        <button type="submit" class="pide-modal-button pide-logout-confirm" :disabled="logoutSubmitting">
                            <span x-show="!logoutSubmitting"><x-icon name="logout" /> Cerrar sesión</span>
                            <span x-show="logoutSubmitting"><span class="loading-spinner"></span> Cerrando...</span>
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <livewire:pide-password-modal wire:key="pide-password-modal" />
    <livewire:pide-credential-modal wire:key="pide-credential-modal" />
</div>
