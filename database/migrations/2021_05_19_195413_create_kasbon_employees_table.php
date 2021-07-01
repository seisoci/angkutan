<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKasbonEmployeesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('kasbon_employees', function (Blueprint $table) {
      $table->id();
      $table->foreignId('invoice_kasbon_employee_id')->nullable()->references('id')->on('invoice_kasbon_employees')->cascadeOnUpdate()->cascadeOnDelete();
      $table->foreignId('employee_id')->references('id')->on('employees')->cascadeOnUpdate();
      $table->foreignId('coa_id')->constrained('coa')->cascadeOnUpdate();
      $table->decimal('amount', 15, 2);
      $table->enum('status', [0, 1])->default(0);
      $table->text('memo')->nullable();
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
    Schema::dropIfExists('kasbon_employees');
  }
}
