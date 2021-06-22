<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperPaymentLdo
 */
class PaymentLdo extends Model
{
  use HasFactory, Notifiable, LogsActivity;

  protected static $logName = 'Rincian Pembayaran LDO';
  protected static $logFillable = true;
  protected static $logAttributes = ['invoiceldo.num_bill'];
  protected static $logAttributesToIgnore = ['invoice_ldo_id'];

  protected $fillable = [
    'invoice_ldo_id',
    'coa_id',
    'date_payment',
    'payment',
    'description'
  ];

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }


  public function invoiceldo()
  {
    return $this->belongsTo(InvoiceLdo::class, 'invoice_ldo_id');
  }

  public function coa()
  {
    return $this->belongsTo(Coa::class, 'coa_id');
  }
}
