<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperEmployeeMaster
 */
class EmployeeMaster extends Model
{
  use HasFactory, Notifiable, LogsActivity;

  protected static $logName = 'Master Tipe Gaji';
  protected static $logFillable = true;

  protected $fillable = [
    'name',
  ];

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }

  public function employees()
  {
    return $this->belongsToMany(Employee::class)->with('amount');
  }
}
