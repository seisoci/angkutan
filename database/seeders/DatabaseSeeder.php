<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

      $settings = [
        [
          'name'  => 'name',
          'value' => '',
          'type'  => 'settings'
        ],
        [
          'name'  => 'address',
          'value' => '',
          'type'  => 'settings'
        ],
        [
          'name'  => 'telp',
          'value' => '',
          'type'  => 'settings'
        ],
        [
          'name'  => 'fax',
          'value' => '',
          'type'  => 'settings'
        ],
        [
          'name'  => 'email',
          'value' => '',
          'type'  => 'settings'
        ],
        [
          'name'  => 'logo_url',
          'value' => '',
          'type'  => 'image'
        ],
        [
          'name'  => 'favicon_url',
          'value' => '',
          'type'  => 'image'
        ],
        [
          'name'  => 'potongan sparepart',
          'value' => '20',
          'type'  => 'settings'
        ],
        [
          'name'  => 'gaji supir',
          'value' => '20',
          'type'  => 'settings'
        ],


      ];
      Setting::insert($settings);
    }
}
