<?php

namespace Tests\Feature;

use App\Livewire\ConsultaConadis;
use App\Models\Usuario;
use App\Services\Pide\Contracts\ConadisServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class ConsultaConadisTest extends TestCase
{
    use RefreshDatabase;

    public function test_result_fields_are_visible_and_empty_before_search(): void
    {
        $this->actingAs(Usuario::factory()->create());

        Livewire::test(ConsultaConadis::class)
            ->assertSee('Datos del registro')
            ->assertSee('Nombres')
            ->assertSee('Apellido paterno')
            ->assertSee('Gravedad registrada')
            ->assertSee('Pendiente de consulta')
            ->assertSet('result', [])
            ->assertSet('searched', false);
    }

    public function test_consulta_requires_exactly_eight_dni_digits(): void
    {
        $this->actingAs(Usuario::factory()->create());

        Livewire::test(ConsultaConadis::class)
            ->set('numeroDocumento', '123')
            ->call('consultar')
            ->assertHasErrors(['numeroDocumento']);
    }

    public function test_consulta_uses_demo_service_without_external_credentials(): void
    {
        $usuario = Usuario::factory()->create(['username' => 'operador-conadis']);
        $this->actingAs($usuario);
        $service = Mockery::mock(ConadisServiceInterface::class);
        $service->shouldReceive('consultarPersona')
            ->once()
            ->with('74251836')
            ->andReturn([
                'success' => true,
                'message' => 'Persona inscrita.',
                'data' => [
                    'nombre' => 'MARÍA',
                    'apellidoPaterno' => 'QUISPE',
                    'apellidoMaterno' => 'RAMOS',
                    'fallecido' => false,
                    'gravedad' => 2,
                    'gravedadDescripcion' => 'Moderado',
                    'estado' => 1,
                    'estadoDescripcion' => 'Inscrito',
                ],
            ]);
        $this->app->instance(ConadisServiceInterface::class, $service);

        Livewire::test(ConsultaConadis::class)
            ->set('numeroDocumento', '74251836')
            ->call('consultar')
            ->assertHasNoErrors()
            ->assertSet('searched', true)
            ->assertSee('MARÍA')
            ->assertSee('Moderado')
            ->assertSee('Inscrito');
    }

}
