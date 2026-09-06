<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Retorna HTML print-friendly con el mismo formato del PDF, para que
 * el usuario pueda imprimir directamente desde el navegador.
 */
final class ConsultaPrintController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'uuid'],
        ]);

        $entry = Cache::get("consulta_pdf:{$data['token']}");

        abort_if($entry === null, 404, 'La consulta expiró o no existe. Vuelva a realizarla.');
        abort_unless(($entry['user_id'] ?? null) === $request->user()->id, 403);

        $entityLogoFile = (string) ($entry['entity']['logo'] ?? '');

        return view('print.consulta', [
            'entityName' => (string) ($entry['entity']['name'] ?? ''),
            'muniLogo' => $this->imageFileDataUri(public_path('assets/images/muni2.png')),
            'entityLogo' => $entityLogoFile !== ''
                ? $this->imageFileDataUri(public_path('assets/images/'.$entityLogoFile))
                : null,
            'title' => (string) ($entry['title'] ?? 'Consulta PIDE'),
            'subtitle' => (string) ($entry['subtitle'] ?? ''),
            'meta' => $entry['meta'] ?? [],
            'photo' => $this->imageDataUri($entry['photo'] ?? null),
            'sections' => $entry['sections'] ?? [],
        ]);
    }

    private function imageFileDataUri(string $path): ?string
    {
        if (! is_file($path)) {
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
        $mime ??= is_array($detected) ? ($detected['mime'] ?? null) : null;

        if (! in_array($mime, ['image/jpeg', 'image/png'], true)) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($binary);
    }
}
