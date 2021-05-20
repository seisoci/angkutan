<?php

namespace App\Models;

use Carbon\Carbon;
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
    'invoice_kasbon_id',
    'date_payment',
    'payment'
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }

  public function invoicekasbon(){
    return $this->belongsTo(InvoiceKasbon::class, 'invoice_kasbon_id');
  }
}