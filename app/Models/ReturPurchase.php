<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
/**
 * @mixin IdeHelperReturPurchase
 */
class ReturPurchase extends Model
{
  protected $appends = ['total_price'];
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'Pengembalian Barang';
  protected static $logFillable = true;
  protected static $logAttributes = ['sparepart.name', 'invoiceretur'];
  protected static $logAttributesToIgnore = ['invoice_retur_purchase_id', 'sparepart_id'];

    protected $fillable = [
      'invoice_retur_purchase_id',
      'sparepart_id',
      'supplier_sparepart_id',
      'qty',
      'price',
    ];

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }


    public function sparepart(){
      return $this->belongsTo(Sparepart::class);
    }

    public function invoiceretur(){
      return $this->belongsTo(InvoiceReturPurchase::class);
    }

    public function getTotalPriceAttribute()
    {
      return ($this->price * $this->qty);
    }
}
