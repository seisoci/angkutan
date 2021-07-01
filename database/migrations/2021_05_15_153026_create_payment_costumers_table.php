<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentCostumersTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('payment_costumers', function (Blueprint $table) {
      $table->id();
      $table->foreignId('invoice_costumer_id')->references('id')->on('invoice_costumers')->cascadeOnDelete();
      $table->foreignId('coa_id')->references('coas')->on('coas')->cascadeOnUpdate();
      $table->date('date_payment');
      $table->decimal('payment', 15, 2);
      $table->string('description')->nullable();
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
    Schema::dropIfExists('payment_costumers');
  }
}
