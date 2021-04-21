<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transport extends Model
{
  use HasFactory;

  protected $fillable = [
    'another_expedition_id',
    'num_pol',
    'merk',
    'type',
    'type_car',
    'year',
    'max_weight',
    'expired_stnk',
    'expired_kir',
    'description',
    'photo',
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }
}
