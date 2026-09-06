<?php

namespace App\Providers;

use App\Contracts\RoleManagementService;
use App\Contracts\UserManagementService;
use App\Services\EloquentRoleManagementService;
use App\Services\EloquentUserManagementService;
use App\Services\Pide\Contracts\CAmbientalesServiceInterface;
use App\Services\Pide\Contracts\CCoactivaServiceInterface;
use App\Services\Pide\Contracts\ConadisServiceInterface;
use App\Services\Pide\Contracts\MtcServiceInterface;
use App\Services\Pide\Contracts\ReniecServiceInterface;
use App\Services\Pide\Contracts\SunarpServiceInterface;
use App\Services\Pide\Contracts\SunatServiceInterface;
use App\Services\PideDemo\CAmbientalesDemoService;
use App\Services\PideDemo\CCoactivaDemoService;
use App\Services\PideDemo\ConadisDemoService;
use App\Services\PideDemo\MtcDemoService;
use App\Services\PideDemo\ReniecDemoService;
use App\Services\PideDemo\SunarpDemoService;
use App\Services\PideDemo\SunatDemoService;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserManagementService::class, EloquentUserManagementService::class);
        $this->app->bind(RoleManagementService::class, EloquentRoleManagementService::class);

            // Repositorio de demostración: sin lógica de conexión real a los
            // servicios externos (ver docs/README sobre app/Services/Pide/).
        $this->app->bind(ReniecServiceInterface::class, ReniecDemoService::class);
        $this->app->bind(SunatServiceInterface::class, SunatDemoService::class);
        $this->app->bind(SunarpServiceInterface::class, SunarpDemoService::class);
        $this->app->bind(CCoactivaServiceInterface::class, CCoactivaDemoService::class);
        $this->app->bind(CAmbientalesServiceInterface::class, CAmbientalesDemoService::class);
        $this->app->bind(MtcServiceInterface::class, MtcDemoService::class);
        $this->app->bind(ConadisServiceInterface::class, ConadisDemoService::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        $this->app->events->listen(RequestHandled::class, function ($handled) {
            $base = $handled->request->getBaseUrl();

            if ($base === '' || $base === '/') {
                return;
            }

            $response = $handled->response;

            if (! method_exists($response, 'getContent')) {
                return;
            }

            $content = $response->getContent();

            if (is_string($content) && str_contains($content, 'data-update-uri="/')) {
                $response->setContent(
                    str_replace('data-update-uri="/', 'data-update-uri="'.$base.'/', $content)
                );
            }
        }, -100);
    }
}
