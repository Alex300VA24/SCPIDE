{{--
    Hoja de estilo compartida por pdf/consulta.blade.php (DomPDF) y print/consulta.blade.php
    (impresión desde el navegador). $isPrint distingue las reglas que cada motor necesita:
    DomPDF requiere @page y un footer con position:fixed; el navegador usa @media print
    y un footer en flujo normal.
--}}
<style>
    * { box-sizing: border-box; }
    body { margin: 0; color: #172033; font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.45; }
    .header { width: 100%; border-bottom: 3px solid #173f74; padding-bottom: 12px; border-collapse: collapse; @if($isPrint) margin-bottom: 20px; @endif }
    .header td { vertical-align: middle; }
    .header-logo { max-width: 64px; max-height: 64px; object-fit: contain; }
    .header-entity { text-align: right; }
    .institution { padding: 0 14px; }
    .institution-name { margin: 0; color: #173f74; font-size: 14px; font-weight: bold; text-transform: uppercase; }
    .institution-subtitle { margin: 2px 0 0; color: #52627a; font-size: 8.5px; letter-spacing: .5px; text-transform: uppercase; }
    .doc-meta { margin: 6px 0 0; color: #68778d; font-size: 8.5px; }
    .title-block { margin: 20px 0 6px; text-align: center; }
    .title-block h1 { margin: 0; color: #172033; font-size: 16px; }
    .title-block p { margin: 4px 0 0; color: #68778d; font-size: 9.5px; }
    .meta-line { margin: 2px 0 4px; text-align: center; color: #52627a; font-size: 9px; }
    .photo-wrap { width: 112px; height: 134px; border: 1px solid #c6d1df; background: #fff; text-align: center; margin: 12px auto 0; }
    .photo { width: 110px; height: 132px; object-fit: cover; }
    .section-title { margin: 18px 0 8px; padding-bottom: 5px; border-bottom: 1px solid #d7e0eb; color: #173f74; font-size: 11px; font-weight: bold; text-transform: uppercase; }
    .details { width: 100%; border: 1px solid #d7e0eb; border-collapse: collapse; }
    .details th, .details td { padding: 7px 11px; border-bottom: 1px solid #e2e8f0; text-align: left; vertical-align: top; }
    .details tr:last-child th, .details tr:last-child td { border-bottom: 0; }
    .details th { width: 32%; background: #f6f8fb; color: #52627a; font-size: 8px; letter-spacing: .5px; text-transform: uppercase; }
    .details td { color: #172033; font-size: 10.5px; font-weight: bold; overflow-wrap: anywhere; }
    table.grid { width: 100%; border: 1px solid #d7e0eb; border-collapse: collapse; }
    table.grid th { background: #173f74; color: #fff; font-size: 7.5px; letter-spacing: .4px; text-transform: uppercase; padding: 7px 8px; text-align: left; }
    table.grid td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; font-size: 9.5px; overflow-wrap: anywhere; }
    table.grid tr:nth-child(even) td { background: #f6f8fb; }
    .is-empty { color: #8b98a9; font-weight: normal; }
    .notice { margin-top: 15px; padding: 8px 11px; border-left: 3px solid #d39b24; background: #fff8e7; color: #66501e; font-size: 8.5px; }
    @if($isPrint)
        .footer { border-top: 1px solid #d7e0eb; padding-top: 6px; margin-top: 30px; color: #76859a; font-size: 8px; text-align: center; }

        @media print {
            body { margin: 0; padding: 20px; }
            .header { margin-bottom: 15px; }
            .title-block { margin: 15px 0 4px; }
            .section-title { margin: 15px 0 6px; page-break-inside: avoid; }
            .details, table.grid { page-break-inside: avoid; }
            .notice { page-break-inside: avoid; }
            img { max-width: 100%; }
        }
    @else
        .footer { position: fixed; right: 0; bottom: -24px; left: 0; padding-top: 6px; border-top: 1px solid #d7e0eb; color: #76859a; font-size: 8px; text-align: center; }
    @endif
</style>
