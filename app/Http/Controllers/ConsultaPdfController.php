<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/**
 * Genera el PDF, con formato ordenado y logos de la municipalidad y de la
 * entidad, de una consulta PIDE ya realizada por el usuario (SUNAT, SUNARP,
 * MTC, SENACE, CONADIS, cobranza coactiva, etc.).
 *
 * El navegador solo envía el token de la consulta; el contenido se recupera
 * de la caché del servidor (ver App\Livewire\Concerns\BuildsConsultaPdf) y se
 * verifica que la consulta pertenezca al usuario autenticado.
 */
final class ConsultaPdfController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $data = $request->validate([
            'token' => ['required', 'uuid'],
        ]);

        $entry = Cache::get("consulta_pdf:{$data['token']}");

        abort_if($entry === null, 404, 'La consulta expiró o no existe. Vuelva a realizarla.');
        abort_unless(($entry['user_id'] ?? null) === $request->user()->id, 403);

        $entityLogoFile = (string) ($entry['entity']['logo'] ?? '');

        $pdf = Pdf::loadView('pdf.consulta', [
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
        ])->setPaper('a4');

        $filename = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($entry['filename'] ?? 'consulta'));

        return $pdf->download(($filename !== '' ? $filename : 'consulta').'.pdf');
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
