<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentKasbonEmployeesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('payment_kasbon_employees', function (Blueprint $table) {
      $table->id();
      $table->foreignId('invoice_kasbon_employee_id')->references('id')->on('invoice_kasbon_employees')->onDelete('cascade');
      $table->date('date_payment');
      $table->decimal('payment', 15, 2);
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
    Schema::dropIfExists('payment_kasbon_employees');
  }
}
