<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperEmployee
 */
class Employee extends Model
{
  use HasFactory, Notifiable, LogsActivity;

  protected static $logName = 'Master Karyawaan';
  protected static $logFillable = true;

  protected $fillable = [
    'name',
    'position',
    'no_card',
    'photo',
    'photo_ktp',
    'status',
  ];

  public function getCreatedAtAttribute($value)
  {
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }

  public function salaries()
  {
    return $this->belongsToMany(EmployeeMaster::class)->withPivot('amount');
  }

  public function monthlysalarydetail(){
    return $this->hasOne(MonthlySalaryDetail::class);
  }


}
