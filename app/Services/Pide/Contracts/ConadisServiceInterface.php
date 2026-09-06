<?php

namespace App\Services\Pide\Contracts;

interface ConadisServiceInterface
{
    public function consultarPersona(string $docNumber): array;
}
