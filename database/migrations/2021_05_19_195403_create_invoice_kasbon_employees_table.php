<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceKasbonEmployeesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('invoice_kasbon_employees', function (Blueprint $table) {
      $table->id();
      $table->string('num_bill')->unique();
      $table->string('prefix');
      $table->foreignId('employee_id')->constrained('employees')->cascadeOnUpdate();
      $table->decimal('total_kasbon', 15, 2);
      $table->decimal('total_payment', 15, 2);
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
    Schema::dropIfExists('invoice_kasbon_employees');
  }
}
