<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceReturPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_retur_purchases', function (Blueprint $table) {
          $table->id();
          $table->string('num_bill')->unique();
          $table->string('prefix');
          $table->foreignId('invoice_purchase_id')->constrained('invoice_purchases');
          $table->foreignId('supplier_sparepart_id')->constrained('supplier_spareparts')->cascadeOnUpdate();
          $table->date('invoice_date')->nullable();
          $table->decimal('total_payment', 15, 2);
          $table->decimal('discount', 15, 2)->default('0');
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
        Schema::dropIfExists('invoice_retur_purchases');
    }
}
