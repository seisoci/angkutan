<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
class InvoicePurchase extends Model
{
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'Invoice Pembelian Barang';
  protected static $logFillable = true;
  protected $fillable = [
    'supplier_sparepart_id',
    'prefix',
    'num_bill',
    'invoice_date',
    'due_date',
    'discount',
    'total_bill',
    'total_payment',
    'rest_payment',
    'method_payment',
  ];

  protected $appends = ['total_net'];


  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }

  public function purchases(){
    return $this->hasMany(Purchase::class, 'invoice_purchase_id');
  }

  public function purchasepayments(){
    return $this->hasMany(PurchasePayment::class);
  }

  public function supplier(){
    return $this->belongsTo(SupplierSparepart::class, 'supplier_sparepart_id');
  }

  public function getTotalNetAttribute()
  {
      return $this->total_bill - $this->discount;
  }

  public function getNumInvoiceAttribute()
  {
    return ($this->prefix ."-". $this->num_bill);
  }
}
