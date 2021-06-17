<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
/**
 * @mixin IdeHelperInvoiceReturPurchase
 */
class InvoiceReturPurchase extends Model
{
  use HasFactory, Notifiable, LogsActivity;
  protected $appends = ['num_invoice'];
  protected static $logName = 'Invoice Pembelian Barang';
  protected static $logFillable = true;
  protected static $logAttributes = ['supplier.name'];
  protected static $logAttributesToIgnore = ['supplier_sparepart_id'];

  protected $fillable = [
    'prefix',
    'num_bill',
    'supplier_sparepart_id',
    'invoice_purchase_id',
    'invoice_date',
    'discount',
    'total_payment',
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }

  public function supplier(){
    return $this->belongsTo(SupplierSparepart::class, 'supplier_sparepart_id');
  }

  public function returpurchases(){
    return $this->hasMany(ReturPurchase::class);
  }

  public function getNumInvoiceAttribute()
  {
    return ($this->prefix ."-". $this->num_bill);
  }
}
