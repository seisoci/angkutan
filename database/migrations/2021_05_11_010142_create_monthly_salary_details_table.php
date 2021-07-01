<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonthlySalaryDetailsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('monthly_salary_details', function (Blueprint $table) {
      $table->id();
      $table->foreignId('monthly_salary_id')->references('id')->on('monthly_salaries')->cascadeOnUpdate()->cascadeOnDelete();
      $table->foreignId('employee_id')->references('id')->on('employees')->cascadeOnDelete();
      $table->foreignId('coa_id')->constrained('coa')->cascadeOnUpdate();
      $table->enum('status', [0, 1]);
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
    Schema::dropIfExists('monthly_salary_details');
  }
}
