<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStocksTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('stocks', function (Blueprint $table) {
      $table->id();
      $table->foreignId('sparepart_id')->unique('sparepart_id')
        ->constrained('spareparts')
        ->onUpdate('cascade')
        ->onDelete('cascade');
      $table->foreignId('invoice_purchase_id')
        ->constrained('invoice_purchases')
        ->onUpdate('cascade')
        ->onDelete('cascade');
      $table->bigInteger('qty');
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
    Schema::dropIfExists('stocks');
  }
}
