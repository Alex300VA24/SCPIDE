<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Acceso denegado</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-ink">
        <script>
            window.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'error',
                    iconColor: '#b42318',
                    title: 'Acceso denegado',
                    html: '<p style="color:#526079;font-size:.95rem;line-height:1.6;">No tienes permisos para acceder a este módulo.</p>',
                    confirmButtonText: 'Ir al inicio',
                    confirmButtonColor: '#b42318',
                    background: '#ffffff',
                    backdrop: 'rgba(15, 23, 42, .55)',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    customClass: {
                        popup: 'pide-swal-popup',
                        title: 'pide-swal-title',
                        confirmButton: 'pide-swal-confirm',
                    },
                }).then(function () {
                    window.location.href = '{{ url('/pide/inicio') }}';
                });
            });
        </script>
    </body>
</html>
