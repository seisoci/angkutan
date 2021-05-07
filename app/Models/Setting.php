<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
class Setting extends Model
{

  use HasFactory, Notifiable, LogsActivity;
  protected $appends = ['num_invoice'];
  protected static $logName = 'Settings';
  protected static $logFillable = true;

  protected $fillable = [
    'name',
    'value',
  ];
}
