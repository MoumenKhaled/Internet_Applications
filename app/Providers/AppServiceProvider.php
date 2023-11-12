<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Go\Core\AspectKernel;
use Go\Core\AspectContainer;
use Go\Aop\Framework\ClassProxy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(AspectContainer::class, function ($app) {
            $aspectKernel = $app->make(AspectKernel::class);
            $aspectKernel->init([
                'debug' => config('app.debug'),
                'appDir' => base_path(),
                'cacheDir' => storage_path('app/aop'),
                'includePaths' => [base_path('app')],
                'excludePaths' => [base_path('vendor')],
                'aspects' => [
                    \App\Aspects\LogRequestsAndResponses::class,
                ],
            ]);
            return $aspectKernel->getContainer();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
