<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceCostumersTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('invoice_costumers', function (Blueprint $table) {
      $table->id();
      $table->string('num_bill')->unique();
      $table->string('prefix');
      $table->foreignId('costumer_id')
        ->constrained('costumers')
        ->onUpdate('cascade');
      $table->foreignId('tax_coa_id')
        ->constrained('coas')
        ->onUpdate('cascade');
      $table->foreignId('fee_coa_id')
        ->constrained('coas')
        ->onUpdate('cascade');
      $table->date('invoice_date');
      $table->date('due_date');
      $table->decimal('total_bill', 15, 2);
      $table->decimal('total_cut', 15, 2)->default(0);
      $table->decimal('total_payment', 15, 2)->default(0);
      $table->decimal('rest_payment', 15, 2);
      $table->string('memo')->nullable();
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
    Schema::dropIfExists('invoice_costumers');
  }
}
