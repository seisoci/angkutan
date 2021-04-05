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
    'cargo',
    'road_engkel',
    'road_tronton',
    'invoice',
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
}
