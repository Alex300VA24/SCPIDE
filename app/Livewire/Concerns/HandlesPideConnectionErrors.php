<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Str;

/**
 * Traduce errores técnicos de transporte (cURL, timeouts, DNS) devueltos por
 * los servicios PIDE en un mensaje que el usuario final pueda entender,
 * en vez de mostrar la excepción cruda del proveedor externo.
 *
 * Métodos públicos:
 * - friendlyPideMessage(array $resultado, string $servicio, ?string $fallbackMessage): string
 *   Devuelve mensaje amigable derivado de respuesta PIDE.
 * - isPideTransportError(array $resultado): bool
 *   Detecta si $resultado indica fallo de transporte vs error de negocio.
 *
 * Usado por: Livewire components (Consulta*), Service classes.
 */
trait HandlesPideConnectionErrors
{
    /**
     * Mensajes técnicos que delatan un fallo de transporte/red aunque el
     * servicio no haya marcado `error_type` explícitamente.
     *
     * @return list<string>
     */
    private static function transportErrorHints(): array
    {
        return [
            'curl error',
            "couldn't connect",
            'failed to connect',
            'connection refused',
            'could not resolve host',
            'error de conexión',
            'error de conexion',
            'no se pudo conectar',
            'timed out',
            'timeout',
            'ssl certificate',
        ];
    }

    protected function friendlyPideMessage(array $resultado, string $servicio, ?string $fallbackMessage = null): string
    {
        if ($this->isPideTransportError($resultado)) {
            return "No se pudo conectar con el servicio de {$servicio}. Intenta nuevamente en unos minutos.";
        }

        $message = trim((string) ($resultado['message'] ?? ''));

        return $message !== '' ? $message : ($fallbackMessage ?? "No fue posible completar la consulta con {$servicio}.");
    }

    protected function isPideTransportError(array $resultado): bool
    {
        $errorType = (string) ($resultado['error_type'] ?? '');

        if (in_array($errorType, ['transport', 'connection'], true)) {
            return true;
        }

        $message = Str::lower((string) ($resultado['message'] ?? ''));

        return $message !== '' && Str::contains($message, self::transportErrorHints());
    }
}
