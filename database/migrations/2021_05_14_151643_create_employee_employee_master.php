<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeEmployeeMaster extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('employee_employee_master', function (Blueprint $table) {
      $table->id();
      $table->foreignId('employee_id')->references('id')->on('employees')->cascadeOnDelete();
      $table->foreignId('employee_master_id')->references('id')->on('employee_masters')->cascadeOnDelete();
      $table->decimal('amount', 15, 2);
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('employee_employee_master');
  }
}
