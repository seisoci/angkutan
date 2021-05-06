<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
class Opname extends Model
{
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'Stok Opname';
  protected static $logFillable = true;

  protected $fillable = [
    'description',
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }

  public function opnamedetail(){
    return $this->hasMany(OpnameDetail::class);
  }

}
