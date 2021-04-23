<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceReturPurchase extends Model
{
    use HasFactory;

  protected $fillable = [
    'prefix',
    'num_bill',
    'supplier_sparepart_id',
    'note_date',
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
}
