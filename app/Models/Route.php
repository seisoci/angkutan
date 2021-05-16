<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
/**
 * @mixin IdeHelperRoute
 */
class Route extends Model
{
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'Master Rute';
  protected static $logFillable = true;

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

  public function roadmoney(){
    return $this->hasMany(RoadMoney::class);
  }

}
