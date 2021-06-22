<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperTypeCapacity
 */
class TypeCapacity extends Model
{
  use HasFactory, Notifiable, LogsActivity;

  protected static $logName = 'Master Kapasistas';
  protected static $logFillable = true;

  protected $fillable = [
    'name',
  ];

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }


  public function getNameAttribute($value)
  {
    return ucwords($value);
  }

  public function roadmonies()
  {
    return $this->belongsToMany(RoadMoney::class, 'roadmoney_typecapacity')->withPivot(['road_engkel', 'road_tronton']);
  }
}
