<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
/**
 * @mixin IdeHelperInvoiceCostumer
 */
class InvoiceCostumer extends Model
{
  use HasFactory, Notifiable, LogsActivity;

  protected $appends = ['num_invoice', 'total_netto'];
  protected static $logName = 'Invoice Pelanggan';
  protected static $logFillable = true;
  protected static $logAttributes = ['costumer.name'];
  protected static $logAttributesToIgnore = ['costumer_id'];

  protected $fillable = [
    'prefix',
    'num_bill',
    'costumer_id',
    'invoice_date',
    'due_date',
    'total_bill',
    'total_tax',
    'total_fee_thanks',
    'total_cut',
    'total_payment',
    'rest_payment',
    'memo',
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }

  public function costumer(){
    return $this->belongsTo(Costumer::class);
  }

  public function joborders(){
    return $this->hasMany(JobOrder::class);
  }

  public function paymentcostumers(){
    return $this->hasMany(PaymentCostumer::class);
  }

  public function getNumInvoiceAttribute()
  {
      return ($this->prefix ."-". $this->num_bill);
  }

  public function getTotalNettoAttribute()
  {
    return $this->total_bill - $this->total_tax - $this->total_fee_thanks - $this->total_cut;
  }
}
