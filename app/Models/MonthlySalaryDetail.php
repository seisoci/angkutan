<?php

namespace App\Models;

use DateTimeInterface;
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
    'coa_id',
    'employee_id',
    'status'
  ];

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }


  public function coa()
  {
    $this->belongsTo(Coa::class, 'coa_id');
  }

  public function employee()
  {
    return $this->belongsTo(Employee::class, 'employee_id');
  }

  public function monthlysalary()
  {
    return $this->belongsTo(MonthlySalary::class, 'monthly_salary_id');
  }

  public function monthlysalarydetailemployees()
  {
    return $this->hasMany(MonthlySalaryDetailEmployee::class, 'monthly_salary_detail_id');
  }

}
