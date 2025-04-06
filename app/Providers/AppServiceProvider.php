<?php

namespace App\Providers;

use App\Adapter\BankTransferValidatorAdapter;
use App\Adapter\Contracts\BankTranferValidatorAdapterInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->bind(
            BankTranferValidatorAdapterInterface::class,
            BankTransferValidatorAdapter::class
        );
    }
}
