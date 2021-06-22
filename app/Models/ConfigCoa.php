<?php

namespace App\Models;

use DateTimeInterface;
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

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }

  public function coa()
  {
    return $this->belongsToMany(Coa::class);
  }
}
