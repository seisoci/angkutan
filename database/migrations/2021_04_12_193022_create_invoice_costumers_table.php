<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceCostumersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_costumers', function (Blueprint $table) {
            $table->id();
            $table->string('num_bill')->unique();
            $table->string('prefix');
            $table->foreignId('costumer_id')->constrained('costumers');
            $table->decimal('grandtotal',15, 2);
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
        Schema::dropIfExists('invoice_costumers');
    }
}
