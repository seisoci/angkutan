<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperInvoiceKasbon
 */
class InvoiceKasbon extends Model
{
  use HasFactory, Notifiable, LogsActivity;

  protected $appends = ['num_invoice'];
  protected static $logName = 'Invoice Kasbon Supir';
  protected static $logFillable = true;
  protected static $logAttributes = ['driver.name'];
  protected static $logAttributesToIgnore = ['driver_id'];

  protected $fillable = [
    'num_bill',
    'prefix',
    'driver_id',
    'total_kasbon',
    'total_payment',
    'rest_payment',
    'memo',
  ];

  public function driver()
  {
    return $this->belongsTo(Driver::class, 'driver_id');
  }

  public function paymentkasbons()
  {
    return $this->hasMany(PaymentKasbon::class, 'invoice_kasbon_id');
  }

  public function kasbons()
  {
    return $this->hasMany(Kasbon::class, 'invoice_kasbon_id');
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
