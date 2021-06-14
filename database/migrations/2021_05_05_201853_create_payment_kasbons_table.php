<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentKasbonsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('payment_kasbons', function (Blueprint $table) {
      $table->id();
      $table->foreignId('invoice_kasbon_id')->references('id')->on('invoice_kasbons')->onDelete('cascade');
      $table->foreignId('coa_id')->constrained('coa');
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
    Schema::dropIfExists('payment_kasbons');
  }
}
