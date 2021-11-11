<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdditionalInvoiceCostumersTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('additional_invoice_costumers', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('invoice_costumer_id');
      $table->foreign('invoice_costumer_id')
        ->references('id')
        ->on('invoice_costumers')
        ->onUpdate('cascade')
        ->onDelete('cascade');
      $table->text('description')->nullable();
      $table->decimal('total', 15, 2);
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
    Schema::dropIfExists('additional_invoice_costumers');
  }
}
