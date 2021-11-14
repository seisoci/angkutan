<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperPaymentKasbon
 */
class PaymentKasbon extends Model
{
  use HasFactory, Notifiable, LogsActivity;

  protected static $logName = 'Rincian Pembayaran Kasbon Supir Detail';
  protected static $logFillable = true;
  protected static $logAttributes = ['invoicekasbon'];
  protected static $logAttributesToIgnore = ['invoice_kasbon_id'];

  protected $fillable = [
    'coa_id',
    'driver_id',
    'date_payment',
    'type',
    'payment',
    'description'
  ];

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }

  public function coa()
  {
    return $this->belongsTo(Coa::class, 'coa_id');
  }

  public function driver()
  {
    return $this->belongsTo(Driver::class, 'driver_id');
  }
}
