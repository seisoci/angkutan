<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSparepartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spareparts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_sparepart_id')->constrained('supplier_spareparts');
            $table->foreignId('brand_id')->constrained('brands')->nullable();
            $table->string('photo')->nullable();
            $table->string('name')->nullable();
            $table->string('qty')->nullable();
            $table->decimal('price', 15, 0)->nullable();
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
        Schema::dropIfExists('spareparts');
    }
}
