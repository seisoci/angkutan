<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
/**
 * @mixin IdeHelperOperationalExpense
 */
class OperationalExpense extends Model
{
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'Tambahan Biaya Operasional';
  protected static $logFillable = true;
  protected static $logAttributes = ['expense.name', 'joborder'];
  protected static $logAttributesToIgnore = ['expense_id', 'job_order_id'];

  protected $fillable = [
    'job_order_id',
    'expense_id',
    'amount',
    'description',
  ];

  public function expense(){
    return $this->belongsTo(Expense::class, 'expense_id');
  }

  public function joborder(){
    return $this->belongsTo(joborder::class, 'job_order_id');
  }
}
