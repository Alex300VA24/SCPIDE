<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    @include('pdf.partials.consulta-style', ['isPrint' => true])
</head>
<body>
    <table class="header" role="presentation">
        <tr>
            <td style="width: 72px;">
                @if($muniLogo)<img class="header-logo" src="{{ $muniLogo }}" alt="Municipalidad Distrital de La Esperanza">@endif
            </td>
            <td class="institution">
                <p class="institution-name">Municipalidad Distrital de La Esperanza</p>
                <p class="institution-subtitle">Plataforma de Interoperabilidad del Estado Peruano</p>
                <p class="doc-meta">Documento generado el {{ now()->format('d/m/Y H:i') }}</p>
            </td>
            <td class="header-entity" style="width: 84px;">
                @if($entityLogo)<img class="header-logo" src="{{ $entityLogo }}" alt="{{ $entityName }}">@endif
            </td>
        </tr>
    </table>

    <section class="title-block">
        <h1>{{ $title }}</h1>
        @if($subtitle)<p>{{ $subtitle }}</p>@endif
    </section>

    @if(! empty($meta))
        <p class="meta-line">
            @foreach($meta as $label => $value){{ $label }}: <strong>{{ $value }}</strong>@if(! $loop->last) &nbsp;·&nbsp; @endif @endforeach
        </p>
    @endif

    @if($photo)
        <div class="photo-wrap"><img class="photo" src="{{ $photo }}" alt="Fotografía de la persona consultada"></div>
    @endif

    @foreach($sections as $section)
        <h2 class="section-title">{{ $section['heading'] }}</h2>

        @if(($section['type'] ?? 'fields') === 'table')
            <table class="grid">
                <thead>
                    <tr>@foreach($section['columns'] ?? [] as $column)<th>{{ $column }}</th>@endforeach</tr>
                </thead>
                <tbody>
                    @foreach($section['rows'] as $row)
                        <tr>
                            @foreach($row as $cell)
                                <td class="{{ (string) $cell === '' ? 'is-empty' : '' }}">{{ (string) $cell !== '' ? $cell : '—' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @elseif(($section['type'] ?? 'fields') === 'images')
            @foreach($section['rows'] as $image)
                <div style="page-break-before: always; margin-top: 0; text-align: center;">
                    <img src="{{ $image }}" style="max-width: 100%; max-height: 600px; object-fit: contain;">
                </div>
            @endforeach
        @else
            <table class="details">
                @foreach($section['rows'] as $label => $value)
                    <tr>
                        <th scope="row">{{ $label }}</th>
                        <td class="{{ filled($value) ? '' : 'is-empty' }}">{{ filled($value) ? $value : '—' }}</td>
                    </tr>
                @endforeach
            </table>
        @endif
    @endforeach

    <div class="notice">Documento informativo generado a partir de una consulta a la Plataforma de Interoperabilidad del Estado (PIDE). Refleja los datos entregados por la entidad al momento de la consulta y no reemplaza un certificado oficial.</div>

    <footer class="footer">Sistema de Consultas PIDE · Municipalidad Distrital de La Esperanza</footer>

    <script>
        window.addEventListener('load', () => {
            window.print();
        });
    </script>
</body>
</html>
