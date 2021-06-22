<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use DateTimeInterface;

/**
 * @mixin IdeHelperCargo
 */
class Cargo extends Model
{
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'Master Muatan';
  protected static $logFillable = true;

  protected $fillable = [
    'name',
  ];

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }

  public function roadmoney(){
    return $this->hasMany(RoadMoney::class);
  }

  public function getNameAttribute($value){
    return ucwords($value);
  }
}
