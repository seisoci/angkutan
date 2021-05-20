<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('retur_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_retur_purchase_id')->constrained('invoice_retur_purchases')->onDelete('cascade');
            $table->foreignId('sparepart_id')->constrained('spareparts');
            $table->bigInteger('qty');
            $table->decimal('price', 15, 2);
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
        Schema::dropIfExists('retur_purchases');
    }
}
