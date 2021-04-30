<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceUsageItem extends Model
{
    use HasFactory;

  protected $fillable = [
    'num_bill',
    'prefix',
    'driver_id',
    'transport_id',
    'type',
    'total_payment',
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }

  public function usageitem(){
    return $this->hasMany(UsageItem::class);
  }

  public function driver(){
    return $this->belongsTo(Driver::class);
  }

  public function transport(){
    return $this->belongsTo(Transport::class);
  }

  public function getNumInvoiceAttribute()
  {
    return ($this->prefix ."-". $this->num_bill);
  }
}
