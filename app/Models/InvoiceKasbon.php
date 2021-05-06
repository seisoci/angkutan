<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;

class InvoiceKasbon extends Model
{
  use HasFactory;
  protected $appends = ['num_invoice'];

  protected $fillable = [
    'num_bill',
    'prefix',
    'driver_id',
    'total_kasbon',
    'total_payment',
    'rest_payment',
    'method_payment',
    'memo',
  ];

  public function driver(){
    return $this->belongsTo(Driver::class, 'driver_id');
  }

  public function invoicekasbon(){
    return $this->belongsTo(InvoiceKasbon::class, 'invoice_kasbon_id');
  }

  public function paymentkasbons(){
    return $this->hasMany(PaymentKasbon::class, 'invoice_kasbon_id');
  }

  public function kasbons(){
    return $this->hasMany(Kasbon::class, 'invoice_kasbon_id');
  }

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d');
  }

  public function getNumInvoiceAttribute()
  {
    return ($this->prefix ."-". $this->num_bill);
  }
}
