<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
class Driver extends Model
{
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'Master Supir';
  protected static $logFillable = true;

  protected $fillable = [
    'another_expedition_id',
    'name',
    'bank_name',
    'no_card',
    'address',
    'phone',
    'ktp',
    'sim',
    'expired_sim',
    'status',
    'description',
    'photo',
    'photo_ktp',
    'photo_sim',
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }

  public function kasbon(){
    return $this->hasMany(Kasbon::class, 'driver_id');
  }
}
