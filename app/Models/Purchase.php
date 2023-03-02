<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use DateTimeInterface;

/**
 * @mixin IdeHelperPurchase
 */
class Purchase extends Model
{
  use HasFactory, Notifiable, LogsActivity;

  protected $appends = ['total'];
  protected static $logName = 'Pembelian Barang Detail';
  protected static $logFillable = true;
  protected static $logAttributes = ['sparepart.name', 'supplier.name', 'invoicepurchase'];
  protected static $logAttributesToIgnore = ['invoice_purchase_id', 'sparepart_id', 'supplier_sparepart_id'];

  protected $fillable = [
    'invoice_purchase_id',
    'sparepart_id',
    'supplier_sparepart_id',
    'qty',
    'price',
    'description',
  ];

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }

  public function invoicepurchase()
  {
    return $this->belongsTo(InvoicePurchase::class, 'invoice_purchase_id');
  }

  public function sparepart()
  {
    return $this->belongsTo(Sparepart::class);
  }

  public function supplier()
  {
    return $this->belongsTo(SupplierSparepart::class, 'supplier_sparepart_id');
  }

  public function getTotalAttribute()
  {
    return $this->qty * $this->price;
  }
}
