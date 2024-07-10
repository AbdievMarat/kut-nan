<?php

namespace App\Providers;

use App\View\Components\Client\Forms\Input;
use App\View\Components\Admin\Forms\Input as AdminInput;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
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
        Paginator::useBootstrapFive();
        Model::preventLazyLoading(App::isLocal());

        Blade::component('client-forms-input', Input::class);
        Blade::component('admin-forms-input', AdminInput::class);
    }
}
