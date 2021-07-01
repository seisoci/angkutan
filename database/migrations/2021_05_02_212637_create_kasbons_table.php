<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKasbonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kasbons', function (Blueprint $table) {
          $table->id();
          $table->foreignId('invoice_kasbon_id')->nullable()->references('id')->on('invoice_kasbons')->cascadeOnUpdate()->cascadeOnDelete();
          $table->foreignId('driver_id')->references('id')->on('drivers')->cascadeOnUpdate();
          $table->foreignId('coa_id')->references('id')->on('coas')->cascadeOnUpdate();
          $table->decimal('amount', 15, 2);
          $table->enum('status', [0,1])->default(0);
          $table->text('memo')->nullable();
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
        Schema::dropIfExists('kasbons');
    }
}
