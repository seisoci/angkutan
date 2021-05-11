<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
class Costumer extends Model
{
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'Master Pelanggan';
  protected static $logFillable = true;

  protected $fillable = [
    'name',
    'emergency_name',
    'emergency_phone',
    'phone',
    'address',
    'description',
    'cooperation',
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }

  public function roadmoney(){
    return $this->hasMany(RoadMoney::class);
  }

  public function getNameAttribute($value){
    return ucwords($value);
  }
}
