<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sparepart extends Model
{
    use HasFactory;
  protected $fillable = [
    'supplier_sparepart_id',
    'brand_id',
    'name',
    'photo',
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }

  public function categories(){
    return $this->belongsToMany(Category::class);
  }

  public function brand(){
    return $this->belongsTo(Brand::class);
  }

  public function supplier(){
    return $this->belongsTo(SupplierSparepart::class, 'supplier_sparepart_id');
  }
}
