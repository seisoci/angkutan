<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobOrdersTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('job_orders', function (Blueprint $table) {
      $table->id();
      $table->string('num_bill')->unique();
      $table->string('prefix');
      $table->date('date_begin');
      $table->date('date_end')->nullable();
      $table->foreignId('invoice_salary_id')->nullable()->references('id')->on('invoice_salaries')->cascadeOnUpdate()->nullOnDelete();
      $table->foreignId('invoice_ldo_id')->nullable()->references('id')->on('invoice_ldos')->cascadeOnUpdate()->nullOnDelete();
      $table->foreignId('invoice_costumer_id')->nullable()->references('id')->on('invoice_costumers')->cascadeOnUpdate()->nullOnDelete();
      $table->foreignId('another_expedition_id')->nullable()->references('id')->on('another_expeditions')->cascadeOnUpdate()->nullOnDelete();
      $table->foreignId('coa_id')->nullable()->references('id')->on('coas')->cascadeOnUpdate()->nullOnDelete();
      $table->foreignId('transport_id')->constrained('transports');
      $table->foreignId('driver_id')->constrained('drivers')->cascadeOnUpdate();
      $table->foreignId('costumer_id')->constrained('costumers')->cascadeOnUpdate();
      $table->foreignId('cargo_id')->constrained('cargos')->cascadeOnUpdate();
      $table->foreignId('route_from')->constrained('routes')->cascadeOnUpdate();
      $table->foreignId('route_to')->constrained('routes')->cascadeOnUpdate();
      $table->foreignId('salary_coa_id')->nullable()->constrained('coas')->cascadeOnUpdate()->nullOnDelete();
      $table->string('type_capacity');
      $table->string('type_payload');
      $table->double('payload');
      $table->decimal('basic_price', 15, 2);
      $table->decimal('basic_price_ldo', 15, 2)->nullable();
      $table->decimal('road_money', 15, 2);
      $table->integer('cut_sparepart_percent')->nullable();;
      $table->integer('salary_percent')->nullable();
      $table->decimal('tax_percent', 15, 2)->nullable();
      $table->decimal('fee_thanks', 15, 2)->nullable();
      $table->decimal('invoice_bill', 15, 0);
      $table->enum('status_salary', [1, 0])->default(0);
      $table->enum('status_cargo', ['mulai', 'transfer', 'selesai', 'batal'])->default('mulai');
      $table->enum('status_payment_ldo', [1, 0])->default(0);
      $table->enum('status_payment', [1, 0])->default(0);
      $table->enum('status_document', [1, 0])->default(0);
      $table->enum('status_tax', [1, 0])->default(0);
      $table->string('type');
      $table->string('description')->nullable();
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
    Schema::dropIfExists('job_orders');
  }
}
