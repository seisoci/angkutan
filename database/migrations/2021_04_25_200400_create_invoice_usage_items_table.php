<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceUsageItemsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('invoice_usage_items', function (Blueprint $table) {
      $table->id();
      $table->string('num_bill')->unique();
      $table->string('prefix');
      $table->date('invoice_date');
      $table->foreignId('driver_id')->constrained('drivers');
      $table->foreignId('transport_id')->constrained('transports');
      $table->enum('type', ['self', 'outside'])->default('self');
      $table->decimal('total_payment', 15, 2)->nullable();
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
    Schema::dropIfExists('invoice_usage_items');
  }
}
