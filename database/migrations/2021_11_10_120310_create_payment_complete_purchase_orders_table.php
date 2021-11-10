<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentCompletePurchaseOrdersTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('payment_complete_purchase_orders', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('complete_purchase_order_id');
      $table->foreign('complete_purchase_order_id', 'cpo_foreign_id')
        ->references('id')
        ->on('complete_purchase_orders')
        ->onUpdate('cascade')
        ->onDelete('cascade');
      $table->unsignedBigInteger('coa_id');
      $table->foreign('coa_id', 'coa_foreign_id')
        ->references('id')
        ->on('coas')
        ->cascadeOnUpdate()
        ->cascadeOnDelete();
      $table->date('date_payment');
      $table->decimal('payment', 15, 2);
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
    Schema::dropIfExists('payment_complete_purchase_orders');
  }
}
