<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->resizeOperacion(50);
    }

    public function down(): void
    {
        $this->resizeOperacion(10);
    }

    private function resizeOperacion(int $length): void
    {
        $driver = DB::connection()->getDriverName();

        match ($driver) {
            'pgsql' => DB::unprepared(
                "ALTER TABLE historial_auditoria ALTER COLUMN operacion TYPE VARCHAR({$length})"
            ),
            'sqlsrv' => DB::unprepared(
                "ALTER TABLE historial_auditoria ALTER COLUMN operacion NVARCHAR({$length}) NOT NULL"
            ),
            'mysql', 'mariadb' => DB::unprepared(
                "ALTER TABLE historial_auditoria MODIFY operacion VARCHAR({$length}) NOT NULL"
            ),
            // SQLite no necesita esta ampliación en su tipado dinámico.
            'sqlite' => null,
            default => throw new RuntimeException("Motor de base de datos no soportado: {$driver}"),
        };
    }
};
