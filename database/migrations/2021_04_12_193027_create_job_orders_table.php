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
            $table->date('date_begin');
            $table->date('date_end')->nullable();
            $table->foreignId('invoice_purchase_id')->nullable()->references('id')->on('invoice_purchases');
            $table->foreignId('another_expedition_id')->nullable()->references('id')->on('another_expeditions');
            $table->foreignId('driver_id')->constrained('drivers');
            $table->foreignId('transport_id')->constrained('transports');
            $table->foreignId('costumer_id')->constrained('costumers');
            $table->foreignId('cargo_id')->constrained('cargos');
            $table->foreignId('route_from')->constrained('routes');
            $table->foreignId('route_to')->constrained('routes');
            $table->string('type_capacity');
            $table->string('type_payload');
            $table->integer('payload');
            $table->decimal('basic_price',15,0);
            $table->decimal('basic_price_ldo',15,0)->nullable();
            $table->decimal('road_money',15,0);
            $table->integer('cut_sparepart_percent')->nullable();;
            $table->decimal('cut_sparepart',15,0)->nullable();;
            $table->decimal('salary',15,0)->nullable();
            $table->integer('salary_percent')->nullable();
            $table->decimal('grandtotalgross',15,0)->nullable();
            $table->decimal('grandtotalnetto',15,0);
            $table->decimal('grandtotalnettoldo',15,0)->nullable();
            $table->decimal('invoice_bill',15,0);
            $table->string('description')->nullable();
            $table->enum('status_salary', [1, 0])->default(0);
            $table->enum('status_cargo', ['mulai', 'muat', 'bongkar', 'selesai'])->default('mulai');
            $table->enum('status_payment', [1, 0])->nullable();
            $table->string('type');
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
