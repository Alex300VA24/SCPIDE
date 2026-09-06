<?php

namespace Tests\Feature;

use App\Livewire\ConsultaMtc;
use App\Services\Pide\Contracts\MtcServiceInterface;
use Livewire\Livewire;
use Tests\TestCase;

class ConsultaMtcTest extends TestCase
{
    public function test_one_demo_search_shows_all_three_results(): void
    {
        $service = \Mockery::mock(MtcServiceInterface::class);
        $service->shouldReceive('consultarPapeletas')->once()->andReturn([
            'success' => true, 'message' => 'Sin papeletas demo.', 'data' => [],
        ]);
        $service->shouldReceive('consultarUltimaLicencia')->once()->andReturn([
            'success' => true, 'message' => 'Licencia demo',
            'data' => ['numLicencia' => 'Q07836030', 'categoria' => 'A I', 'estado' => 'Vigente'],
        ]);
        $service->shouldReceive('consultarUltimasSanciones')->once()->andReturn([
            'success' => true, 'message' => 'Sin sanciones demo.', 'data' => [],
        ]);
        $this->app->instance(MtcServiceInterface::class, $service);

        Livewire::test(ConsultaMtc::class)
            ->set('numeroDocumento', '07836030')
            ->call('consultar')
            ->assertHasNoErrors()
            ->assertSet('searched', true)
            ->assertSet('results.licencia.numLicencia', 'Q07836030')
            ->assertSeeHtml('role="tablist"');
    }
}
