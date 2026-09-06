@props([
    'token' => '',
    'canPdf' => false,
    'canPrint' => false,
])

{{-- Acciones de exportación de una consulta PIDE (PDF con formato + impresión). --}}
<div class="consulta-result-actions consulta-export-actions">
    <form method="POST" action="{{ route('consulta.pdf') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <button type="submit" class="consulta-button consulta-button-pdf" @disabled(! ($canPdf && $token))>
            <x-icon name="pdf" /> Exportar PDF
        </button>
    </form>
    <button type="button" class="consulta-button consulta-button-print" onclick="window.print()" @disabled(! $canPrint)>
        <x-icon name="print" /> Imprimir
    </button>
</div>
