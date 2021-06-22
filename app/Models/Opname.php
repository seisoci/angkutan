<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperOpname
 */
class Opname extends Model
{
  use HasFactory, Notifiable, LogsActivity;

  protected static $logName = 'Stok Opname';
  protected static $logFillable = true;

  protected $fillable = [
    'description',
  ];

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }

  public function opnamedetail()
  {
    return $this->hasMany(OpnameDetail::class);
  }

}
