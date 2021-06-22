<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperPrefix
 */
class Prefix extends Model
{
  use HasFactory;
  use HasFactory, Notifiable, LogsActivity;

  protected static $logName = 'Prefix';
  protected static $logFillable = true;

  protected $fillable = [
    'name',
    'type',
  ];

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }

}
