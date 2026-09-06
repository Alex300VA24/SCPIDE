<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Guarda en la caché del servidor el contenido —ya validado— de una consulta
 * PIDE para que App\Http\Controllers\ConsultaPdfController pueda generar el PDF
 * sin volver a confiar en datos enviados por el navegador.
 *
 * El PDF resultante usa el formato genérico ordenado con el logo de la
 * municipalidad y el de la entidad consultada (resources/views/pdf/consulta.blade.php).
 */
trait BuildsConsultaPdf
{
    /**
     * Minutos que el contenido de la consulta permanece disponible para
     * generar el PDF.
     */
    protected int $consultaPdfTtlMinutes = 10;

    /**
     * @param  array{
     *     entity: array{name: string, logo?: ?string},
     *     title: string,
     *     subtitle?: string,
     *     meta?: array<string, string>,
     *     photo?: ?string,
     *     filename?: string,
     *     sections: array<int, array{heading: string, type?: string, columns?: array<int, string>, rows: array}>
     * }  $payload
     * @return string Token asociado al contenido, o cadena vacía si no hay datos que exportar.
     */
    protected function cacheConsultaPdf(array $payload): string
    {
        $sections = array_values(array_filter(
            $payload['sections'] ?? [],
            static fn ($section): bool => is_array($section) && ! empty($section['rows']),
        ));

        if ($sections === []) {
            return '';
        }

        $token = (string) Str::uuid();

        Cache::put(
            "consulta_pdf:{$token}",
            [
                'user_id' => auth()->id(),
                'entity' => [
                    'name' => (string) ($payload['entity']['name'] ?? ''),
                    'logo' => $payload['entity']['logo'] ?? null,
                ],
                'title' => (string) $payload['title'],
                'subtitle' => (string) ($payload['subtitle'] ?? ''),
                'meta' => array_filter($payload['meta'] ?? [], static fn ($value): bool => (string) $value !== ''),
                'photo' => $payload['photo'] ?? null,
                'filename' => (string) ($payload['filename'] ?? 'consulta'),
                'sections' => $sections,
            ],
            now()->addMinutes($this->consultaPdfTtlMinutes),
        );

        return $token;
    }
}
