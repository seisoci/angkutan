<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperMonthlySalaryDetailEmployee
 */
class MonthlySalaryDetailEmployee extends Model
{
  use HasFactory;
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'Detail Gaji Bulanan Pegawai';
  protected static $logFillable = true;

  protected $fillable = [
    'monthly_salary_detail_id',
    'employee_master_id',
    'amount'
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }

  public function employeemaster(){
    return $this->belongsTo(EmployeeMaster::class, 'employee_master_id');
  }

}
