<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
/**
 * @mixin IdeHelperKasbon
 */
class Kasbon extends Model
{
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'Data Kasbon Supir';
  protected static $logFillable = true;
  protected static $logAttributes = ['driver.name', 'invoicekasbon'];
  protected static $logAttributesToIgnore = ['driver_id', 'invoice_kasbon_id'];

  protected $fillable = [
    'invoice_kasbon_id',
    'driver_id',
    'amount',
    'status',
    'memo'
  ];

  public function driver(){
    return $this->belongsTo(Driver::class, 'driver_id');
  }

  public function invoicekasbon(){
    return $this->belongsTo(InvoiceKasbon::class, 'invoice_kasbon_id');
  }

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }
}
