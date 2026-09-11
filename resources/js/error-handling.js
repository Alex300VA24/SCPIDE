document.addEventListener('livewire:init', () => {
    let sessionExpiredHandled = false;

    window.Livewire.hook('request', ({ fail }) => {
        fail(({ status, content, preventDefault }) => {
            preventDefault();

            if (status === 419) {
                // Varias peticiones Livewire pueden vencer a la vez (polling, componentes
                // múltiples): solo la primera debe mostrar alerta y redirigir, para evitar
                // el doble modal (alerta + pantalla de sesión vencida) al llegar al login.
                if (sessionExpiredHandled) return;
                sessionExpiredHandled = true;
            }

            let message = 'Ocurrió un error inesperado. Intenta nuevamente en unos momentos.';
            let title = null;

            if (content) {
                try {
                    const data = JSON.parse(content);
                    if (data.message) message = data.message;
                    if (data.title) title = data.title;
                } catch {
                    // no JSON: mensaje genérico
                }
            }

            const alerta = window.pideAlert(message, status === 419 ? 'warning' : 'danger', title);

            if (status === 419 && alerta) {
                alerta.then(() => {
                    window.location.href = window.PIDE_LOGIN_URL || '/login';
                });
            }
        });
    });
});