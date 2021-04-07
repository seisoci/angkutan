<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierSparepart extends Model
{
    use HasFactory;
  protected $fillable = [
    'name',
    'address',
    'phone'
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }

  public function spareparts(){
    return $this->hasMany(Sparepart::class);
  }
}
