<?php

namespace App\Http\Controllers;

use App\Services\ConsultationExportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
    public function __invoke(Request $request, ConsultationExportService $exports): Response
    {
        $data = $request->validate([
            'token' => ['required', 'uuid'],
        ]);

        $entry = $exports->findOwned($data['token'], $request->user());
        $pdf = Pdf::loadView('pdf.consulta', $exports->viewData($entry))->setPaper('a4');

        return $pdf->download($exports->filename($entry));
    }
}
