<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoadmoneyTypecapacity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roadmoney_typecapacity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('road_money_id')->constrained('road_money')->onDelete('cascade');
            $table->foreignId('type_capacity_id')->constrained('type_capacities')->onDelete('cascade');
            $table->decimal('road_engkel', 15, 2)->nullable();
            $table->decimal('road_tronton', 15, 2)->nullable();
            $table->string('type')->nullable();
            $table->decimal('expense', 15, 2)->nullable();
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
        Schema::dropIfExists('roadmoney_typecapacity');
    }
}
