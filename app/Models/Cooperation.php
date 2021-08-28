<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use DateTimeInterface;

/**
 * @mixin IdeHelperCooperation
 */
class Cooperation extends Model
{
  use HasFactory, Notifiable, LogsActivity;

  protected static $logName = 'Master Kerjasama';
  protected static $logFillable = true;

  protected $fillable = [
    'image',
    'name',
    'nickname',
    'owner',
    'email',
    'phone',
    'fax',
    'address',
    'default',
  ];

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }
}
