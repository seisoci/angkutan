<?php

namespace App\Providers;

use App;
use App\Helpers\Terbilang;
use Illuminate\Support\ServiceProvider;

class TerbilangServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
      App::singleton('Terbilang',function() {
        return new Terbilang;
      });
    }
}
