<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonthlySalaryDetailEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monthly_salary_detail_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_salary_detail_id')->references('id')->on('monthly_salary_details')->onDelete('cascade');
            $table->foreignId('employee_master_id')->references('id')->on('employee_masters');
            $table->decimal('amount',15,2);
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
        Schema::dropIfExists('monthly_salary_detail_employees');
    }
}
