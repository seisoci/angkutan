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
            $table->string('route_from');
            $table->string('route_to');
            $table->string('cargo');
            $table->decimal('road_engkel', 15, 2);
            $table->decimal('road_tronton', 15, 2);
            $table->decimal('invoice', 15, 2);
            $table->decimal('salary_engkel', 15, 2);
            $table->decimal('salary_tronton', 15 , 2);
            $table->decimal('amount', 15, 2);
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
