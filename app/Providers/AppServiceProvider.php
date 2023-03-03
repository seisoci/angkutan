<?php

namespace App\Providers;

use App\Models\JobOrder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   *
   * @return void
   */
  public function register()
  {
    //
  }

  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot()
  {
    Relation::morphMap([
      'users' => "App\Models\User",
    ]);
    View::composer('layout.base._aside', 'App\Http\View\Composers\PermissionComposer');
    View::composer('layout.base._aside', 'App\Http\View\Composers\RoleComposer');
  }
}
