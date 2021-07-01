<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_purchases', function (Blueprint $table) {
          $table->id();
          $table->string('num_bill')->unique();
          $table->string('prefix');
          $table->foreignId('supplier_sparepart_id')
            ->constrained('supplier_spareparts')
            ->onUpdate('cascade')
            ->onDelete('cascade');
          $table->date('invoice_date');
          $table->date('due_date');
          $table->decimal('discount', 15, 2)->default(0);
          $table->decimal('total_bill', 15, 2);
          $table->decimal('total_payment', 15, 2);
          $table->decimal('rest_payment', 15, 2);
          $table->enum('method_payment', ['cash', 'credit'])->default('cash');
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
        Schema::dropIfExists('invoice_purchases');
    }
}
