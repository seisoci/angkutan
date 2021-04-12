<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoicePurchase extends Model
{
    use HasFactory;

  protected $fillable = [
    'supplier_sparepart_id',
    'prefix',
    'num_bill',
    'grandtotal',
    'keterangan',
    'memo',
  ];


  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }
}
