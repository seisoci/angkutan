<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('transports', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('another_expedition_id')->nullable();
        $table->string('num_pol');
        $table->string('merk')->nullable();
        $table->string('type')->nullable();
        $table->string('type_car', ['engkel', 'tronton'])->default('engkel');
        $table->enum('dump', ['ya', 'tidak'])->default('tidak');
        $table->year('year')->nullable();
        $table->integer('max_weight')->nullable();
        $table->date('expired_stnk')->nullable();
        $table->text('description')->nullable();
        $table->string('photo')->nullable();
        $table->timestamps();
        $table->foreign('another_expedition_id')->references('id')->on('another_expeditions');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transports');
    }
}
