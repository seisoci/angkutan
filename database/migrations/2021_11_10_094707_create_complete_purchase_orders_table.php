<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompletePurchaseOrdersTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('complete_purchase_orders', function (Blueprint $table) {
      $table->id();
      $table->string('num_bill')->unique();
      $table->string('prefix');
      $table->foreignId('invoice_purchase_id')->constrained('invoice_purchases')->onUpdate('cascade')->onDelete('cascade');
      $table->foreignId('supplier_sparepart_id')->constrained('supplier_spareparts')->onUpdate('cascade')->onDelete('cascade');
      $table->date('invoice_date');
      $table->decimal('total_bill', 15, 2);
      $table->decimal('total_payment', 15, 2);
      $table->decimal('rest_payment', 15, 2);
      $table->string('memo')->nullable();
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
    Schema::dropIfExists('complete_purchase_orders');
  }
}
