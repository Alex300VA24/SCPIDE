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
    <form method="GET" action="{{ route('consulta.print') }}" target="_blank">
        <input type="hidden" name="token" value="{{ $token }}">
        <button type="submit" class="consulta-button consulta-button-print" @disabled(! ($canPrint && $token))>
            <x-icon name="print" /> Imprimir
        </button>
    </form>
</div>
