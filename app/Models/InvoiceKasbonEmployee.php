<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperInvoiceKasbonEmployee
 */
class InvoiceKasbonEmployee extends Model
{
  use HasFactory, Notifiable, LogsActivity;

  protected $appends = ['num_invoice'];
  protected static $logName = 'Invoice Kasbon Karyawaan';
  protected static $logFillable = true;
  protected static $logAttributes = ['employee.name'];
  protected static $logAttributesToIgnore = ['employee_id'];

  protected $fillable = [
    'num_bill',
    'prefix',
    'employee_id',
    'total_kasbon',
    'total_payment',
    'rest_payment',
    'memo',
  ];

  public function employee()
  {
    return $this->belongsTo(Employee::class, 'employee_id');
  }

  public function paymentkasbonemployes()
  {
    return $this->hasMany(PaymentKasbonEmployee::class, 'invoice_kasbon_employee_id');
  }

  public function kasbonemployees()
  {
    return $this->hasMany(KasbonEmployee::class, 'invoice_kasbon_employee_id');
  }

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }


  public function getNumInvoiceAttribute()
  {
    return ($this->prefix . "-" . $this->num_bill);
  }
}
