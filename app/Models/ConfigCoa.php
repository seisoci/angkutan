<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperConfigCoa
 */
class ConfigCoa extends Model
{
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'Config COA';
  protected static $logFillable = true;

  protected $fillable = [
    'name',
    'code',
    'parent_id',
    'type',
    'normal_balance',
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d');
  }

  public function coa(){
    return $this->belongsToMany(Coa::class);
  }
}
