<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use DateTimeInterface;
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
    'approved',
    'approved_by',
    'approved_date',
    'created_by',
    'type',
  ];

  protected $appends = [
    'status'
  ];

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }

  public function expense(){
    return $this->belongsTo(Expense::class, 'expense_id');
  }

  public function joborder(){
    return $this->belongsTo(JobOrder::class, 'job_order_id');
  }

  public function getStatusAttribute()
  {
    if($this->approved == NULL){
      $data = 'Pending';
    }else if($this->approved == '0'){
      $data = 'Di Tolak';
    }else if($this->approved == '1'){
      $data = 'Di Setujui';
    }
    return $data;
  }
}
