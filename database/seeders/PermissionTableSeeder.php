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

      $permission = [
        'user-list',
        'user-create',
        'user-edit',
        'user-delete',
        'role-list',
        'role-create',
        'role-edit',
        'role-delete',
      ];

      $master_operational = [
          'costumer-list',
          'costumer-create',
          'costumer-edit',
          'costumer-delete',
          'driver-list',
          'driver-create',
          'driver-edit',
          'driver-delete',
          'transport-list',
          'transport-create',
          'transport-edit',
          'transport-delete',
          'expense-list',
          'expense-create',
          'expense-edit',
          'expense-delete',
          'route-list',
          'route-create',
          'route-edit',
          'route-delete',
          'cargo-list',
          'cargo-create',
          'cargo-edit',
          'cargo-delete',
          'roadmoney-list',
          'roadmoney-create',
          'roadmoney-edit',
          'roadmoney-delete',
          'anotherexpedition-list',
          'anotherexpedition-create',
          'anotherexpedition-edit',
          'anotherexpedition-delete',
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

      foreach ($permissions as $permission) {
        Permission::create(['name' => $permission]);
      }
    }
}
