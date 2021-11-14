<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePiutangKlaimsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('piutang_klaims', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('job_order_id');
      $table->foreign('job_order_id')
        ->references('id')
        ->on('job_orders')
        ->onUpdate('cascade')
        ->onDelete('cascade');
      $table->decimal('amount', 15, 2);
      $table->text('description')->nullable();
      $table->enum('type', ['tambah', 'kurang']);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('piutang_klaims');
  }
}
