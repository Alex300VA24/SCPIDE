<?php

namespace App\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;

/**
 * Recupera exportaciones PIDE pertenecientes al usuario autenticado y
 * normaliza imágenes antes de entregarlas a las vistas PDF o de impresión.
 */
final class ConsultationExportService
{
    /**
     * @return array<string, mixed>
     */
    public function findOwned(string $token, Authenticatable $user): array
    {
        $entry = Cache::get("consulta_pdf:{$token}");

        abort_if($entry === null, 404, 'La consulta expiró o no existe. Vuelva a realizarla.');
        abort_unless(($entry['user_id'] ?? null) === $user->getAuthIdentifier(), 403);

        return $entry;
    }

    /**
     * Construye datos seguros y homogéneos para ambas vistas de exportación.
     *
     * @param  array<string, mixed>  $entry
     * @return array<string, mixed>
     */
    public function viewData(array $entry): array
    {
        $entityLogoFile = basename((string) ($entry['entity']['logo'] ?? ''));

        return [
            'entityName' => (string) ($entry['entity']['name'] ?? ''),
            'muniLogo' => $this->imageFileDataUri(public_path('assets/images/muni2.png')),
            'entityLogo' => $entityLogoFile !== ''
                ? $this->imageFileDataUri(public_path('assets/images/'.$entityLogoFile))
                : null,
            'title' => (string) ($entry['title'] ?? 'Consulta PIDE'),
            'subtitle' => (string) ($entry['subtitle'] ?? ''),
            'meta' => is_array($entry['meta'] ?? null) ? $entry['meta'] : [],
            'photo' => $this->imageDataUri($entry['photo'] ?? null),
            'sections' => is_array($entry['sections'] ?? null) ? $entry['sections'] : [],
        ];
    }

    /** @param array<string, mixed> $entry */
    public function filename(array $entry): string
    {
        $filename = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($entry['filename'] ?? 'consulta'));

        return ($filename !== '' ? $filename : 'consulta').'.pdf';
    }

    private function imageFileDataUri(string $path): ?string
    {
        if (! is_file($path) || ! is_readable($path)) {
            return null;
        }

        return $this->imageDataUri(base64_encode((string) file_get_contents($path)));
    }

    private function imageDataUri(mixed $image): ?string
    {
        if (! is_string($image) || trim($image) === '') {
            return null;
        }

        $mime = null;
        $payload = $image;

        if (preg_match('/^data:(image\/(?:png|jpe?g));base64,(.*)$/is', $image, $matches) === 1) {
            $mime = strtolower($matches[1]);
            $payload = $matches[2];
        }

        $binary = base64_decode((string) preg_replace('/\s+/', '', $payload), true);

        if ($binary === false) {
            return null;
        }

        $detected = @getimagesizefromstring($binary);
        $detectedMime = is_array($detected) ? ($detected['mime'] ?? null) : null;

        if ($detectedMime === null || ! in_array($detectedMime, ['image/jpeg', 'image/png'], true)) {
            return null;
        }

        // No confiar en MIME declarado por cliente/caché; firma binaria manda.
        return 'data:'.$detectedMime.';base64,'.base64_encode($binary);
    }
}
