<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoadMoney extends Model
{
  use HasFactory;

  protected $fillable = [
    'costumer_id',
    'route_from',
    'route_to',
    'cargo_id',
    'road_engkel',
    'road_tronton',
    'salary_engkel',
    'salary_tronton',
    'amount',
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }

  public function costumers(){
    return $this->belongsTo(Costumer::class, 'costumer_id');
  }

  public function routefrom(){
    return $this->belongsTo(Route::class, 'route_from');
  }

  public function routeto(){
    return $this->belongsTo(Route::class, 'route_to');
  }

  public function cargo(){
    return $this->belongsTo(Cargo::class);
  }
}
