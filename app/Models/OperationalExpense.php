<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
class OperationalExpense extends Model
{
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'Tambahan Biaya Operasional';
  protected static $logFillable = true;
  protected $fillable = [
    'job_order_id',
    'expense_id',
    'amount',
    'description',
  ];

  public function expense(){
    return $this->belongsTo(Expense::class, 'expense_id')->select(['id','name']);
  }
}
