<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
/**
 * @mixin IdeHelperCostumer
 */
class Costumer extends Model
{
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'Master Pelanggan';
  protected static $logFillable = true;

  protected $fillable = [
    'cooperation_id',
    'name',
    'emergency_name',
    'emergency_phone',
    'phone',
    'address',
    'description',
  ];

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }

  public function roadmoney(){
    return $this->hasMany(RoadMoney::class);
  }

  public function cooperation(){
    return $this->belongsTo(Cooperation::class, 'cooperation_id');
  }

  public function getNameAttribute($value){
    return ucwords($value);
  }
}
