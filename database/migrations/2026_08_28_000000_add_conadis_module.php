<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega CONADIS a instalaciones existentes. En instalaciones nuevas,
     * PideProductionDataSeeder crea el mismo módulo y su permiso ADMIN.
     */
    public function up(): void
    {
        if (! Schema::hasTable('modulos') || ! Schema::hasTable('rol_modulo')) {
            return;
        }

        $sistemaId = 2;
        $consultas = DB::table('modulos')->where('sistema_id', $sistemaId)->where('codigo', 'CON')->first();

        if ($consultas === null) {
            return;
        }

        $modulo = DB::table('modulos')->where('sistema_id', $sistemaId)->where('codigo', 'CONADIS')->first();

        if ($modulo === null) {
            $moduloId = DB::table('modulos')->insertGetId([
                'sistema_id' => $sistemaId,
                'padre_id' => $consultas->id,
                'codigo' => 'CONADIS',
                'nombre' => 'CONADIS',
                'descripcion' => 'Consulta individual del Registro Nacional de Personas con Discapacidad',
                'url' => '/pide/consultas/conadis',
                'icono' => 'fa-solid fa-universal-access',
                'orden' => 7,
                'nivel' => 2,
                'es_menu' => 1,
                'activo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $moduloId = $modulo->id;
        }

        $adminId = DB::table('roles')->where('codigo', 'ADMIN')->value('id');

        if ($adminId !== null) {
            DB::table('rol_modulo')->updateOrInsert(
                ['rol_id' => $adminId, 'modulo_id' => $moduloId],
                ['sistema_id' => $sistemaId, 'fecha_asignacion' => now()]
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('modulos')) {
            return;
        }

        $moduloId = DB::table('modulos')->where('sistema_id', 2)->where('codigo', 'CONADIS')->value('id');

        if ($moduloId !== null) {
            DB::table('rol_modulo')->where('modulo_id', $moduloId)->delete();
            DB::table('modulos')->where('id', $moduloId)->delete();
        }
    }
};
