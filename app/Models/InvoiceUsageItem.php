<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
/**
 * @mixin IdeHelperInvoiceUsageItem
 */
class InvoiceUsageItem extends Model
{
  protected $appends = ['num_invoice'];
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'Invoice Pemakaian Barang';
  protected static $logFillable = true;
  protected static $logAttributes = ['driver.name', 'transport.num_pol'];
  protected static $logAttributesToIgnore = ['driver_id', 'transport_id'];

  protected $fillable = [
    'num_bill',
    'invoice_date',
    'prefix',
    'driver_id',
    'transport_id',
    'type',
    'total_payment',
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }

  public function usageitem(){
    return $this->hasMany(UsageItem::class);
  }

  public function driver(){
    return $this->belongsTo(Driver::class);
  }

  public function transport(){
    return $this->belongsTo(Transport::class);
  }

  public function getNumInvoiceAttribute()
  {
    return ($this->prefix ."-". $this->num_bill);
  }

}
