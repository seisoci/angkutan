<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use DateTimeInterface;
/**
 * @mixin IdeHelperPaymentCompletePurchaseOrder
 */
class PaymentCompletePurchaseOrder extends Model
{
  use HasFactory, LogsActivity;

  protected static $logName = 'Pembayaran Pelunasan Pembelian Barang';
  protected static $logFillable = true;

  protected $fillable = [
    'complete_purchase_order_id',
    'coa_id',
    'date_payment',
    'payment',
    'description'
  ];

  public function coa(){
    return $this->belongsTo(Coa::class);
  }

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }

}
