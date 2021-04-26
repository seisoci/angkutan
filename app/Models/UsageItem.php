<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsageItem extends Model
{
    use HasFactory;

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
    return $this->belongsTo(InvoiceUsageItem::class);
  }
}
