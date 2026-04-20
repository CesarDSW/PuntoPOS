<?php

namespace App\Providers;

use App\Support\CompanyPreference;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
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
        View::composer('*', function($view) {
            $companyId = Auth::check() ? (int) Auth::user()->company_idfk : null;

            $view->with('uiPreferences', CompanyPreference::all($companyId));
        });
    }
}
