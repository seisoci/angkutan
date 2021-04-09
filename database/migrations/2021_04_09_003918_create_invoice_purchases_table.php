<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_purchases', function (Blueprint $table) {
          $table->id();
          $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade');
          $table->foreignId('supplier_sparepart_id')->constrained('supplier_spareparts');
          $table->unique('num_bill');
          $table->text('keterangan');
          $table->string('memo');
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
        Schema::dropIfExists('invoice_purchases');
    }
}
