<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperMonthlySalaryDetail
 */
class MonthlySalaryDetail extends Model
{
  use HasFactory;
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'List Gaji Bulanan Pegawai';
  protected static $logFillable = true;

  protected $fillable = [
    'monthly_salary_id',
    'employee_id',
    'status'
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }
}
