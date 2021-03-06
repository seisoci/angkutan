<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoadMoneyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('road_money', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('costumer_id');
            $table->foreignId('route_from')->constrained('routes');
            $table->foreignId('route_to')->constrained('routes');
            $table->foreignId('cargo_id')->constrained('cargos');
            $table->decimal('fee_thanks', 15, 2)->default(0);
            $table->decimal('tax_pph', 15, 2)->default(0);
            $table->decimal('road_engkel', 15, 2)->nullable();
            $table->decimal('road_tronton', 15, 2)->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->timestamps();
            $table->foreign('costumer_id')->references('id')->on('costumers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('road_money');
    }
}
