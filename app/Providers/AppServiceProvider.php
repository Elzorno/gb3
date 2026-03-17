<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
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
        View::composer('layouts.admin', function ($view) {
            $familyName = DB::table('settings')
                ->where('key', 'family_name')
                ->value('value') ?? 'Family';
            $view->with('familyName', $familyName);
        });
    }
}
