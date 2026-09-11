<?php

namespace App\Http\Controllers;

use App\Services\ConsultationExportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Retorna HTML print-friendly con el mismo formato del PDF, para que
 * el usuario pueda imprimir directamente desde el navegador.
 */
final class ConsultaPrintController extends Controller
{
    public function __invoke(Request $request, ConsultationExportService $exports): View
    {
        $data = $request->validate([
            'token' => ['required', 'uuid'],
        ]);

        $entry = $exports->findOwned($data['token'], $request->user());

        return view('print.consulta', $exports->viewData($entry));
    }
}
