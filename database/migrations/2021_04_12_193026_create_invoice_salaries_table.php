<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceSalariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_salaries', function (Blueprint $table) {
            $table->id();
            $table->string('num_bill')->unique();
            $table->string('prefix');
            $table->foreignId('driver_id')->constrained('drivers');
            $table->foreignId('transport_id')->constrained('transports');
            $table->date('invoice_date');
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
        Schema::dropIfExists('invoice_salaries');
    }
}
