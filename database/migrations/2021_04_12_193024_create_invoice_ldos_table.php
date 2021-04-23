<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceLdosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_ldos', function (Blueprint $table) {
            $table->id();
            $table->string('num_bill')->unique();
            $table->string('prefix');
            $table->foreignId('another_expedition_id')->constrained('another_expeditions');
            $table->foreignId('driver_id')->constrained('drivers');
            $table->foreignId('transport_id')->constrained('transports');
            $table->decimal('grandtotal',15,2);
            $table->text('description')->nullable();
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
        Schema::dropIfExists('invoice_ldos');
    }
}
