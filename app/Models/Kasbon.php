<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
class Kasbon extends Model
{
  use HasFactory;

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
    return $date->format('Y-m-d');
  }
}
