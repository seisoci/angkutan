<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpnameDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opname_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opname_id')->constrained('opnames')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('sparepart_id')->constrained('spareparts')->cascadeOnUpdate();
            $table->integer('qty');
            $table->integer('qty_system');
            $table->integer('qty_difference');
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
        Schema::dropIfExists('opname_details');
    }
}
