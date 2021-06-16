<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
/**
 * @mixin IdeHelperPurchasePayment
 */
class PurchasePayment extends Model
{
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'Pembayaran Pembelian Barang';
  protected static $logFillable = true;
  protected static $logAttributes = ['invoicepurchase'];
  protected static $logAttributesToIgnore = ['invoice_purchase_id'];

  protected $fillable = [
    'invoice_purchase_id',
    'date_payment',
    'coa_id',
    'payment',
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }

  public function invoicepurchase(){
    return $this->belongsTo(InvoicePurchase::class);
  }

  public function coa(){
    return $this->belongsTo(Coa::class, 'coa_id');
  }

}
