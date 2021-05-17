<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceLdosTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('invoice_ldos', function (Blueprint $table) {
      $table->id();
      $table->string('num_bill')->unique();
      $table->string('prefix');
      $table->foreignId('another_expedition_id')->constrained('another_expeditions');
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
    Schema::dropIfExists('invoice_ldos');
  }
}
