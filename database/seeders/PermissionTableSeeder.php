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
      ['name' => 'activitylog', 'title'=> 'BABI'],
      ['name' => 'anotherexpedition', 'title'=> 'BABI'],
      ['name' => 'bank', 'title'=> 'BABI'],
      ['name' => 'brand', 'title'=> 'BABI'],
      ['name' => 'cargo', 'title'=> 'BABI'],
      ['name' => 'cash', 'title'=> 'BABI'],
      ['name' => 'transport', 'title'=> 'BABI'],
      ['name' => 'expense', 'title'=> 'BABI'],
      ['name' => 'route', 'title'=> 'BABI'],
      ['name' => 'cargo', 'title'=> 'BABI'],
      ['name' => 'roadmoney', 'title'=> 'BABI'],
    ];

    foreach ($listData as $item):
      foreach ($appends as $append):
        Permission::create(['name' => $item['name'].'-'.$append, 'title'=> $item['title']]);
      endforeach;
    endforeach;

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


  }
}
