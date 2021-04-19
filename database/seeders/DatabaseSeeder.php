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
          'value' => 'Alusindo',
          'type'  => 'settings'
        ],
        [
          'name'  => 'address',
          'value' => 'Jl. Ikan Kakap No . 33-35 T. Betung, Bandar Lampung',
          'type'  => 'settings'
        ],
        [
          'name'  => 'telp',
          'value' => '0811993623',
          'type'  => 'settings'
        ],
        [
          'name'  => 'fax',
          'value' => '(0721)489924',
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
          'value' => '15',
          'type'  => 'settings'
        ],


      ];
      Setting::insert($settings);
    }
}
