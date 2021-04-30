<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceSalary extends Model
{
    use HasFactory;
  protected $appends = ['num_invoice'];

  protected $fillable = [
    'prefix',
    'num_bill',
    'driver_id',
    'transport_id',
    'grandtotal',
    'description',
    'memo',
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }

  public function transport(){
    return $this->belongsTo(Transport::class, 'transport_id');
  }

  public function joborders(){
    return $this->hasMany(JobOrder::class, 'invoice_salary_id');
  }

  public function driver(){
    return $this->belongsTo(Driver::class, 'driver_id');
  }

  public function getNumInvoiceAttribute()
  {
      return ($this->prefix ."-". $this->num_bill);
  }
}
