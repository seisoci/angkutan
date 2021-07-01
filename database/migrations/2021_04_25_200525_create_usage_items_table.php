<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsageItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usage_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_usage_item_id')->nullable()->references('id')->on('invoice_usage_items')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('invoice_purchase_id')->nullable()->references('id')->on('invoice_purchases')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('sparepart_id')->nullable()->references('id')->on('spareparts')->cascadeOnUpdate();
            $table->foreignId('coa_id')->nullable()->references('id')->on('coas')->cascadeOnUpdate();
            $table->string('name')->nullable();
            $table->bigInteger('qty');
            $table->decimal('price', 15, 2)->nullable();
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
        Schema::dropIfExists('usage_items');
    }
}
