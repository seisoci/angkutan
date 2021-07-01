<?php

namespace App\Http\View\Composers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PermissionComposer
{
  function compose(View $view)
  {
    $data = DB::table("role_has_permissions")
      ->select(DB::raw('SUBSTRING_INDEX(`permissions`.`name`, "-", 1) as name'))
      ->where("role_has_permissions.role_id", Auth::user()->roles()->firstOrFail()->id)
      ->leftJoin('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
      ->groupBy('permissions.title')
      ->pluck('name')
      ->all();
    $view->with('permissionUser', $data);
  }
}
