<?php

namespace App\Http\View\Composers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RoleComposer
{
  function compose(View $view)
  {

    $view->with('roleUser', Auth::user()->roles()->firstOrFail()->name);
  }
}
