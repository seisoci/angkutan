<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperMonthlySalary
 */
class MonthlySalary extends Model
{
  use HasFactory;
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'Master Gaji Bulanan';
  protected static $logFillable = true;

  protected $fillable = [
    'name',
  ];

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }

  public function getNameAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('F Y');
  }

}
