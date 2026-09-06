@props([
    'entityLogo' => null,
    'entity' => '',
    'title' => '',
    'subtitle' => '',
])

{{-- Cabecera visible solo al imprimir: logo municipal + logo de la entidad. --}}
<div class="consulta-print-header consulta-print-only" aria-hidden="true">
    <div class="consulta-print-logos">
        <img src="{{ asset('assets/images/muni2.png') }}" alt="Municipalidad Distrital de La Esperanza">
        @if($entityLogo)
            <img src="{{ asset('assets/images/'.$entityLogo) }}" alt="{{ $entity }}">
        @endif
    </div>
    <div class="consulta-print-titles">
        <strong>Municipalidad Distrital de La Esperanza</strong>
        <span>{{ $title }}@if($subtitle) — {{ $subtitle }}@endif</span>
        <small>Documento generado el {{ now()->format('d/m/Y H:i') }}</small>
    </div>
</div>
