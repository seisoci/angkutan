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
            $table->string('route_from')->nullable();
            $table->string('route_to')->nullable();
            $table->string('cargo')->nullable();
            $table->decimal('road_engkel', 15, 0)->nullable();
            $table->decimal('road_tronton', 15, 0)->nullable();
            $table->decimal('salary_engkel', 15, 0)->nullable();
            $table->decimal('salary_tronton', 15 , 0)->nullable();
            $table->decimal('amount', 15, 0)->nullable();
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
