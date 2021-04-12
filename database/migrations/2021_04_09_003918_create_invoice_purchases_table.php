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
          $table->foreignId('supplier_sparepart_id')->constrained('supplier_spareparts');
          $table->string('prefix');
          $table->string('num_bill')->unique();
          $table->decimal('grandtotal',15,0);
          $table->text('keterangan')->nullable();
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
        Schema::dropIfExists('invoice_purchases');
    }
}
