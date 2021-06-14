<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
/**
 * @mixin IdeHelperKasbonEmployee
 */
class KasbonEmployee extends Model
{
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'Data Kasbon Karyawaan';
  protected static $logFillable = true;
  protected static $logAttributes = ['employee.name', 'invoicekasbonemployee'];
  protected static $logAttributesToIgnore = ['emplyee_id', 'invoice_kasbon_employee_id'];

  protected $fillable = [
    'invoice_kasbon_employee_id',
    'employee_id',
    'coa_id',
    'amount',
    'status',
    'memo'
  ];

  public function employee(){
    return $this->belongsTo(Employee::class, 'employee_id');
  }

  public function invoicekasbonemployee(){
    return $this->belongsTo(InvoiceKasbonEmployee::class, 'invoice_kasbon_employee_id');
  }

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }
}
