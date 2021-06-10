<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoasTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('coas', function (Blueprint $table) {
      $table->id();
      $table->string('name')->unique();
      $table->string('code')->unique();
      $table->integer('parent_id')->nullable();
      $table->enum('type', ['harta', 'kewajiban', 'modal', 'pendapatan', 'beban']);
      $table->enum('normal_balance', ['Db', 'Kr'])->nullable();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('coas');
  }
}
