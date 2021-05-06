<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
class Expense extends Model
{
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'Master Biaya';
  protected static $logFillable = true;

  protected $fillable = [
    'name',
    'cost',
  ];


  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }
}
