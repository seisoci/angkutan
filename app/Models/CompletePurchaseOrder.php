<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use DateTimeInterface;

/**
 * @mixin IdeHelperCompletePurchaseOrder
 */
class CompletePurchaseOrder extends Model
{
  use HasFactory, LogsActivity;

  protected $appends = ['num_invoice'];
  protected static $logName = 'Invoice Pelunasan Pembelian Barang';
  protected static $logFillable = true;
  protected static $logAttributes = ['supplier.name'];

  protected $fillable = [
    'num_bill',
    'prefix',
    'supplier_sparepart_id',
    'invoice_date',
    'total_bill',
    'total_payment',
    'rest_payment',
    'memo'
  ];

  public function invoice_purchase()
  {
    return $this->hasMany(InvoicePurchase::class);
  }

  public function payment_complete()
  {
    return $this->hasMany(PaymentCompletePurchaseOrder::class);
  }

  public function supplier()
  {
    return $this->belongsTo(SupplierSparepart::class, 'supplier_sparepart_id');
  }

  public function getNumInvoiceAttribute()
  {
    return ($this->prefix . "-" . $this->num_bill);
  }

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }

}
