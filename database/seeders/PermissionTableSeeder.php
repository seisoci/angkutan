<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {

    $permissions = [
      'user-list',
      'user-create',
      'user-edit',
      'user-delete',
      'role-list',
      'role-create',
      'role-edit',
      'role-delete',
    ];

    $appends = [
      'list',
      'create',
      'edit',
      'delete'
    ];

    $listData = [
      'activitylog',
      'anotherexpedition',
      'bank',
      'brand',
      'cargo',
      'cash',
      'transport',
      'expense',
      'route',
      'cargo',
      'roadmoney',
    ];

    $master_spare_part = [
      'suppliersparepart-list',
      'suppliersparepart-create',
      'suppliersparepart-edit',
      'suppliersparepart-delete',
      'brand-list',
      'brand-create',
      'brand-edit',
      'brand-delete',
      'category-list',
      'category-create',
      'category-edit',
      'category-delete',
      'service-list',
      'service-create',
      'service-edit',
      'service-delete',
      'sparepart-list',
      'sparepart-create',
      'sparepart-edit',
      'sparepart-delete',
    ];

    $master_account = [
      'cash-list',
      'cash-create',
      'cash-edit',
      'cash-delete',
      'bank-list',
      'bank-create',
      'bank-edit',
      'bank-delete',
      'company-list',
      'company-create',
      'company-edit',
      'company-delete',
    ];

//    foreach ($permissions as $permission) {
//      Permission::create(['name' => $permission]);
//    }

    foreach ($listData as $item):
      foreach ($appends as $append):
        Permission::create(['name' => $item.'-'.$append]);
        endforeach;
    endforeach;
  }
}
