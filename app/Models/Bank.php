<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperBank
 */
class Bank extends Model
{
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'Master Bank';
  protected static $logFillable = true;

  protected $fillable = [
    'name',
    'bank_code',
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
      return $date->format('Y-m-d H:i:s');
  }
}
