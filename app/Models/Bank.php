<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use DateTimeInterface;

/**
 * @mixin IdeHelperBank
 */
class Bank extends Model
{
  use HasFactory, Notifiable, LogsActivity;

  protected static $logName = 'Master Bank';
  protected static $logFillable = true;

  protected $fillable = [
    'name_bank',
    'name',
    'no_account',
    'branch',
    'expedition',
  ];

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }
}
