<?php

namespace App\Services\PideDemo\Support;

/**
 * Generador determinístico de datos ficticios para el modo demo público.
 *
 * Ningún valor aquí proviene de una fuente real: se deriva del propio
 * documento consultado (hash) únicamente para que la demo luzca variada.
 */
class DemoDataFactory
{
    private const NOMBRES = [
        'Juan Carlos', 'María Elena', 'Luis Alberto', 'Rosa María',
        'Carlos Alberto', 'Ana Lucía', 'Pedro Pablo', 'Karen Sofía',
    ];

    private const APELLIDOS_PATERNOS = [
        'García', 'Rodríguez', 'Quispe', 'Mamani', 'Torres', 'Flores', 'Huamán', 'Chávez',
    ];

    private const APELLIDOS_MATERNOS = [
        'Pérez', 'López', 'Condori', 'Vargas', 'Rojas', 'Salazar', 'Paredes', 'Cruz',
    ];

    private const RAZONES_SOCIALES = [
        'INVERSIONES ANDINA DEMO S.A.C.',
        'COMERCIAL LOS ANDES DEMO E.I.R.L.',
        'SERVICIOS GENERALES PERU DEMO S.A.',
        'TEXTILES DEL SUR DEMO S.R.L.',
    ];

    private const UBIGEOS = [
        ['departamento' => 'LIMA', 'provincia' => 'LIMA', 'distrito' => 'MIRAFLORES', 'codigo' => '150122'],
        ['departamento' => 'AREQUIPA', 'provincia' => 'AREQUIPA', 'distrito' => 'YANAHUARA', 'codigo' => '040110'],
        ['departamento' => 'CUSCO', 'provincia' => 'CUSCO', 'distrito' => 'WANCHAQ', 'codigo' => '080110'],
        ['departamento' => 'LA LIBERTAD', 'provincia' => 'TRUJILLO', 'distrito' => 'TRUJILLO', 'codigo' => '130101'],
    ];

    public static function seed(string $valor): int
    {
        return (int) sprintf('%u', crc32($valor));
    }

    private static function pick(array $lista, string $seed, int $offset = 0): mixed
    {
        return $lista[(self::seed($seed) + $offset) % count($lista)];
    }

    public static function personaNatural(string $documento): array
    {
        return [
            'nombres' => self::pick(self::NOMBRES, $documento),
            'apellido_paterno' => self::pick(self::APELLIDOS_PATERNOS, $documento, 1),
            'apellido_materno' => self::pick(self::APELLIDOS_MATERNOS, $documento, 2),
        ];
    }

    public static function razonSocial(string $ruc): string
    {
        return self::pick(self::RAZONES_SOCIALES, $ruc);
    }

    public static function ubigeo(string $semilla): array
    {
        return self::pick(self::UBIGEOS, $semilla);
    }

    /**
     * Imagen PNG 1x1 transparente, usada como marcador visual de "imagen demo".
     */
    public static function imagenDemoBase64(): string
    {
        return 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
    }

    public static function avisoDemo(): string
    {
        return 'Resultado de demostración: datos ficticios generados localmente, sin conexión a un servicio real.';
    }
}
