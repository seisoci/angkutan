<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationalExpense extends Model
{
    use HasFactory;

  protected $fillable = [
    'job_order_id',
    'expense_id',
    'amount',
    'description',
  ];

  public function expense(){
    return $this->belongsTo(Expense::class, 'expense_id');
  }
}
