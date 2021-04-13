<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_orders', function (Blueprint $table) {
            $table->id();
            $table->string('num_bill')->unique();
            $table->string('prefix');
            $table->foreignId('invoice_purchase_id')->constrained('invoice_purchases')->nullable();
            $table->foreignId('another_expedition_id')->constrained('another_expeditions')->nullable();
            $table->foreignId('driver_id')->constrained('drivers');
            $table->foreignId('transport_id')->constrained('transports');
            $table->foreignId('costumer_id')->constrained('costumers');
            $table->foreignId('cargo_id')->constrained('cargos');
            $table->foreignId('route_from')->constrained('routes');
            $table->foreignId('route_to')->constrained('routes');
            $table->decimal('road_money',15,0);
            $table->decimal('salary',15,0);
            $table->decimal('invoice_bill',15,0);
            $table->string('description');
            $table->enum('status_salary', [1, 0]);
            $table->enum('status_cargo', ['berangkat', 'muat', 'bongkar', 'selesai']);
            $table->enum('status_payment', [1, 0]);
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
        Schema::dropIfExists('job_orders');
    }
}
