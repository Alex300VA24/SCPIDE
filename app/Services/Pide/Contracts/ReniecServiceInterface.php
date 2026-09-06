<?php

namespace App\Services\Pide\Contracts;

interface ReniecServiceInterface
{
    public function consultarDNI(string $dni): array;

    public function obtenerDatosRENIEC(string $dni): array;
}
