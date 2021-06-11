<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoaConfigCoasTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('coa_config_coa', function (Blueprint $table) {
      $table->id();
      $table->foreignId('coa_id')->constrained('coas');
      $table->foreignId('config_coa_id')->constrained('config_coas')->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('coa_config_coas');
  }
}
