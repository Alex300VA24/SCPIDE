/**
 * Estado visual del dashboard. Mantiene comportamiento Alpine fuera del Blade
 * y centraliza navegación, persistencia del sidebar y accesibilidad del modal.
 */
window.dashboardUi = (wire, initialSectionTitle) => ({
    navigationOpen: wire.entangle('navigationOpen').live,
    logoutOpen: false,
    logoutSubmitting: false,
    loadingSectionTitle: initialSectionTitle,
    sidebarCollapsed: window.innerWidth > 900 && localStorage.getItem('sidebar_collapsed') === 'true',
    previousFocus: null,

    toggleCollapse() {
        this.sidebarCollapsed = !this.sidebarCollapsed;
        localStorage.setItem('sidebar_collapsed', String(this.sidebarCollapsed));
    },

    openLogout() {
        if (this.logoutSubmitting) return;

        this.previousFocus = document.activeElement;
        this.logoutOpen = true;
        this.$nextTick(() => this.$refs.logoutCancel?.focus());
    },

    closeLogout() {
        this.logoutOpen = false;
        this.logoutSubmitting = false;
        this.$nextTick(() => this.previousFocus?.focus?.());
    },

    trapLogoutFocus(event) {
        const controls = [...this.$refs.logoutDialog.querySelectorAll('button:not([disabled])')];
        const first = controls[0];
        const last = controls.at(-1);

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last?.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first?.focus();
        }
    },

    submitLogout() {
        if (this.logoutSubmitting) return;

        this.logoutSubmitting = true;
        this.$nextTick(() => this.$root.querySelector('[data-logout-form]')?.submit());
    },
});
