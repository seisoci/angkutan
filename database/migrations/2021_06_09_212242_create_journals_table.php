<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJournalsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('journals', function (Blueprint $table) {
      $table->id();
      $table->foreignId('coa_id')->references('id')->on('coas');
      $table->date('date_journal');
      $table->decimal('debit', 15, 2)->default(0);
      $table->decimal('kredit', 15, 2)->default(0);
      $table->string('table_ref')->nullable();
      $table->integer('code_ref')->nullable();
      $table->text('description');
      $table->enum('can_delete', [0, 1]);
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
    Schema::dropIfExists('journals');
  }
}
