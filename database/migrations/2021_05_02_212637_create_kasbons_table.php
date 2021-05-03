<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKasbonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kasbons', function (Blueprint $table) {
          $table->id();
          $table->foreignId('invoice_salary_id')->nullable()->references('id')->on('invoice_salaries');
          $table->foreignId('driver_id')->references('id')->on('drivers');
          $table->decimal('amount', 15, 2);
          $table->enum('status', [0,1])->default(0);
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
        Schema::dropIfExists('kasbons');
    }
}
