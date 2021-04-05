<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Costumer extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'emergency_name',
    'emergency_phone',
    'phone',
    'address',
    'description',
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }

  public function roadmoney(){
    return $this->hasMany(RoadMoney::class);
  }
}
