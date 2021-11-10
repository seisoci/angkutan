<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('invoice_purchases', function (Blueprint $table) {
      $table->unsignedBigInteger('complete_purchase_order_id')->after('supplier_sparepart_id')->nullable();
      $table->foreign('complete_purchase_order_id', 'cpo_invoice_purchase_foreign_id')
        ->references('id')
        ->on('complete_purchase_orders')
        ->onUpdate('cascade')
        ->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('complete_purchase_orders', function (Blueprint $table) {
      $table->dropForeign('dkmp_ahli_waris_no_bpjs_foreign');
    });
  }
};
