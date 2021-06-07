<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
/**
 * @mixin IdeHelperTransport
 */
class Transport extends Model
{
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'Master Kendaraan';
  protected static $logFillable = true;
  protected static $logAttributes = ['anotherexpedition.name'];

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

  public function anotherexpedition(){
    return $this->belongsTo(AnotherExpedition::class, 'another_expedition_id');
  }
}
