<?php

namespace App\Models;

use Carbon\Carbon;
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
    'date_payment',
    'payment',
    'description'
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }

  public function invoiceldo(){
    return $this->belongsTo(InvoiceLdo::class, 'invoice_ldo_id');
  }
}
