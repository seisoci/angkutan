<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperPiutangKlaim
 */
class PiutangKlaim extends Model
{
  use HasFactory, Notifiable, LogsActivity;

  protected static $logName = 'Piutang Klaim Job Order';
  protected static $logFillable = true;

  protected $fillable = [
    'job_order_id',
    'amount',
    'description',
    'type',
  ];

  public function joborders()
  {
    return $this->hasMany(JobOrder::class, 'job_order_id');
  }


  protected function serializeDate(\DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }

}
