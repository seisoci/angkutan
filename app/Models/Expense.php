<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use DateTimeInterface;
/**
 * @mixin IdeHelperExpense
 */
class Expense extends Model
{
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'Master Biaya';
  protected static $logFillable = true;

  protected $fillable = [
    'name',
    'cost',
  ];


  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }
}
