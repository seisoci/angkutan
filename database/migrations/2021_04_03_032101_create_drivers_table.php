<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('another_expedition_id')->nullable();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('ktp')->nullable();
            $table->string('sim')->nullable();
            $table->enum('status', ['active', 'inactive']);
            $table->text('description')->nullable();
            $table->string('photo')->nullable();
            $table->string('photo_ktp')->nullable();
            $table->string('photo_sim')->nullable();
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
        Schema::dropIfExists('drivers');
    }
}
