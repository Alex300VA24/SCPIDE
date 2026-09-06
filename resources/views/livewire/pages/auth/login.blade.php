<?php

use App\Auth\PendingCuiAuthentication;
use App\Livewire\Forms\LoginForm;
use App\Livewire\ValidarCuiModal;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        try {
            $this->validate();
            $usuarioId = $this->form->verifyCredentials();
        } catch (ValidationException $exception) {
            app(PendingCuiAuthentication::class)->clear();

            foreach ($exception->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError($field, $message);
                }
            }

            $this->dispatch('login-action-finished');

            return;
        }

        app(PendingCuiAuthentication::class)->start($usuarioId, $this->form->remember);
        $this->form->reset('password');
        $this->dispatch('open-validar-cui-modal')->to(ValidarCuiModal::class);
    }
}; ?>

<div
    class="login-container"
    x-data="{ showPassword: false, loginPending: false }"
    @login-transition-complete.window="loginPending = false"
    @login-action-finished.window="loginPending = false"
>
    <section class="login-left" aria-labelledby="system-name">
        <div class="header-section">
            <img class="muni-logo" src="{{ asset('assets/images/muni2.png') }}" alt="Plataforma de Interoperabilidad del Estado">
            <span class="system-kicker">Acceso institucional</span>
            <h1 id="system-name">Sistema PIDE</h1>
            <p>Municipalidad Distrital de La Esperanza</p>
        </div>

        <div class="divider-bar" aria-hidden="true"></div>

        <div
            class="service-carousel"
            role="region"
            aria-roledescription="carrusel"
            aria-label="Servicios interoperables disponibles"
            x-data="{
                activeService: 0,
                serviceCount: 6,
                timer: null,
                hovered: false,
                hasFocus: false,
                reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
                start() {
                    if (this.hovered || this.hasFocus || this.reducedMotion || this.timer) return;
                    this.timer = setInterval(() => this.activeService = (this.activeService + 1) % this.serviceCount, 2500);
                },
                stop() {
                    clearInterval(this.timer);
                    this.timer = null;
                },
                restart() {
                    this.stop();
                    this.start();
                }
            }"
            x-init="start()"
            @mouseenter="hovered = true; stop()"
            @mouseleave="hovered = false; start()"
            @focusin="hasFocus = true; stop()"
            @focusout="if (!$el.contains($event.relatedTarget)) { hasFocus = false; start(); }"
        >
            <div class="service-carousel-heading">
                <p class="entity-label"><i class="fa-solid fa-link" aria-hidden="true"></i> Servicios interoperables</p>
            </div>

            <div class="service-slides">
                <article class="service-slide reniec" aria-label="RENIEC" x-show="activeService === 0" x-transition.opacity.duration.350ms :aria-hidden="(activeService !== 0).toString()">
                    <div class="service-logo"><img src="{{ asset('assets/images/reniec-logo-sin-fondo.png') }}" alt=""></div>
                    <div class="service-copy"><span>Identidad ciudadana</span><p>Consulta datos de identidad mediante DNI, estado del documento y fotografía registrada.</p></div>
                </article>
                <article class="service-slide sunat" aria-label="SUNAT" x-cloak x-show="activeService === 1" x-transition.opacity.duration.350ms :aria-hidden="(activeService !== 1).toString()">
                    <div class="service-logo"><img src="{{ asset('assets/images/sunat-logo-sin-fondo.png') }}" alt=""></div>
                    <div class="service-copy"><span>Información tributaria</span><p>Consulta RUC, razón social, estado del contribuyente, domicilio fiscal y cobranza coactiva.</p></div>
                </article>
                <article class="service-slide sunarp" aria-label="SUNARP" x-cloak x-show="activeService === 2" x-transition.opacity.duration.350ms :aria-hidden="(activeService !== 2).toString()">
                    <div class="service-logo"><img src="{{ asset('assets/images/sunarp-logo-sin-fondo.png') }}" alt=""></div>
                    <div class="service-copy"><span>Registros públicos</span><p>Busca partidas registrales de personas naturales y jurídicas, vehículos y propiedades.</p></div>
                </article>
                <article class="service-slide senace" aria-label="SENACE" x-cloak x-show="activeService === 3" x-transition.opacity.duration.350ms :aria-hidden="(activeService !== 3).toString()">
                    <div class="service-logo"><img src="{{ asset('assets/images/senace-logo-sin-fondo.png') }}" alt=""></div>
                    <div class="service-copy"><span>Gestión ambiental</span><p>Consulta certificaciones ambientales, expedientes, sectores y estados de evaluación.</p></div>
                </article>
                <article class="service-slide mtc" aria-label="MTC" x-cloak x-show="activeService === 4" x-transition.opacity.duration.350ms :aria-hidden="(activeService !== 4).toString()">
                    <div class="service-logo"><img src="{{ asset('assets/images/mtc-logo-sin-fondo.png') }}" alt=""></div>
                    <div class="service-copy"><span>Transporte y conducción</span><p>Consulta récord de conductor, licencias emitidas, papeletas y sanciones vigentes.</p></div>
                </article>
                <article class="service-slide conadis" aria-label="CONADIS" x-cloak x-show="activeService === 5" x-transition.opacity.duration.350ms :aria-hidden="(activeService !== 5).toString()">
                    <div class="service-logo"><img src="{{ asset('assets/images/logo_conadis.jfif') }}" alt=""></div>
                    <div class="service-copy"><span>Inclusión y discapacidad</span><p>Consulta estado de inscripción en el Registro Nacional de Personas con Discapacidad.</p></div>
                </article>
            </div>

            <div class="carousel-dots" role="group" aria-label="Seleccionar servicio">
                <template x-for="index in serviceCount" :key="index">
                    <button type="button" @click="activeService = index - 1; restart()" :class="{ 'is-active': activeService === index - 1 }" :aria-current="activeService === index - 1 ? 'true' : 'false'" :aria-label="`Mostrar servicio ${index} de ${serviceCount}`"></button>
                </template>
            </div>
        </div>

        <p class="login-left-note"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Contenido protegido del Estado</p>

    </section>

    <section class="login-right" aria-labelledby="login-title">
        <div class="login-card">
            <div class="login-header">
                <img src="{{ asset('assets/images/logo-pide-2-sin-fondo.png') }}" class="login-icon" alt="Plataforma de Interoperabilidad del Estado">
                <p class="login-kicker"><i class="fa-solid fa-lock" aria-hidden="true"></i> Inicio de sesión seguro</p>
                <h2 class="login-title" id="login-title">Bienvenido al sistema</h2>
                <p class="login-subtitle">Ingresa tus credenciales institucionales para continuar.</p>
            </div>

            <x-auth-session-status class="legacy-auth-status" :status="session('status')" />

            <p class="login-security-note" aria-label="Credenciales de demostración">
                <i class="fa-solid fa-flask" aria-hidden="true"></i>
                Demo: usuario <strong>admin</strong>, contraseña <strong>DemoSCPIDE2026!</strong> y CUI <strong>0</strong>.
            </p>

            <form id="formLogin" wire:submit="login" class="login-form" novalidate @submit="loginPending = true">
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <div class="input-wrapper has-icon">
                        <input wire:model="form.username" type="text" id="username" name="username" autocomplete="username" placeholder="Ingrese su usuario" required autofocus aria-describedby="username-error">
                        <i class="fas fa-user icon-left" aria-hidden="true"></i>
                    </div>
                    @error('form.username') <p id="username-error" class="legacy-field-error" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-wrapper password-container has-icon">
                        <input wire:model="form.password" :type="showPassword ? 'text' : 'password'" id="password" name="password" placeholder="Ingrese su contraseña" required autocomplete="current-password" aria-describedby="password-error">
                        <i class="fas fa-lock icon-left" aria-hidden="true"></i>
                        <button type="button" class="toggle-password" @click="showPassword = !showPassword" :aria-pressed="showPassword.toString()" :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                            <i class="fas" :class="showPassword ? 'fa-eye' : 'fa-eye-slash'" aria-hidden="true"></i>
                        </button>
                    </div>
                    @error('form.password') <p id="password-error" class="legacy-field-error" role="alert">{{ $message }}</p> @enderror
                </div>

                <button type="submit" id="btnLogin" :disabled="loginPending" wire:loading.attr="disabled" wire:target="login">
                    <span x-show="!loginPending"><i class="fas fa-sign-in-alt" aria-hidden="true"></i> Ingresar al Sistema</span>
                    <span x-cloak x-show="loginPending"><i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Ingresando...</span>
                </button>
            </form>

            <p class="login-security-note"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> Tus credenciales se procesan mediante conexión segura.</p>
        </div>
    </section>

    <livewire:validar-cui-modal />
</div>
