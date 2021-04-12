<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_purchase_id')->constrained('invoice_purchases')->onDelete('cascade');
            $table->foreignId('sparepart_id')->constrained('spareparts');
            $table->foreignId('supplier_sparepart_id')->constrained('supplier_spareparts');
            $table->bigInteger('qty');
            $table->decimal('price', 15, 0);
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
        Schema::dropIfExists('purchases');
    }
}
