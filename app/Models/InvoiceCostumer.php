<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
class InvoiceCostumer extends Model
{
  use HasFactory, Notifiable, LogsActivity;

  protected $appends = ['num_invoice'];
  protected static $logName = 'Invoice Pelanggan';
  protected static $logFillable = true;
  protected static $logAttributes = ['costumer.name'];
  protected static $logAttributesToIgnore = ['costumer_id'];

  protected $fillable = [
    'prefix',
    'num_bill',
    'costumer_id',
    'grandtotal',
    'description',
    'memo',
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }

  public function costumer(){
    return $this->belongsTo(Costumer::class);
  }

  public function getNumInvoiceAttribute()
  {
      return ($this->prefix ."-". $this->num_bill);
  }
}
