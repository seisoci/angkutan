<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeCapacity extends Model
{
    use HasFactory;

  protected $fillable = [
    'name',
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }

  public function getNameAttribute($value){
    return ucwords($value);
  }

  public function roadmonies(){
    return $this->belongsToMany(RoadMoney::class, 'roadmoney_typecapacity')->withPivot(['road_engkel', 'road_tronton']);
  }
}
