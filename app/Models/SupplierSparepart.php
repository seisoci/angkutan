<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use DateTimeInterface;
/**
 * @mixin IdeHelperSupplierSparepart
 */
class SupplierSparepart extends Model
{
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'Supplier Spare Part';
  protected static $logFillable = true;

  protected $fillable = [
    'name',
    'address',
    'phone'
  ];

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }

  public function spareparts(){
    return $this->hasMany(Sparepart::class);
  }

  public function getNameAttribute($value){
    return ucwords($value);
  }
}
