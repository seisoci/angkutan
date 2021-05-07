<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
class UsageItem extends Model
{
  use HasFactory, Notifiable, LogsActivity;
  protected $appends = ['total_price'];
  protected static $logName = 'Pengambilan Barang';
  protected static $logFillable = true;
  protected static $logAttributes = ['sparepart.name', 'invoiceusage'];
  protected static $logAttributesToIgnore = ['invoice_usage_item_id', 'sparepart_id'];

  protected $fillable = [
    'invoice_usage_item_id',
    'sparepart_id',
    'name',
    'qty',
    'price',
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }

  public function sparepart(){
    return $this->belongsTo(Sparepart::class);
  }

  public function invoiceusage(){
    return $this->belongsTo(InvoiceUsageItem::class, 'invoice_usage_item_id');
  }

  public function getTotalPriceAttribute()
  {
    return ($this->price * $this->qty);
  }
}
