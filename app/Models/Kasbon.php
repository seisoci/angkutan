<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kasbon extends Model
{
  use HasFactory;

  protected $fillable = [
    'invoice_salary_id',
    'driver_id',
    'amount',
    'status'
  ];

  public function driver(){
    return $this->belongsTo(Driver::class);
  }

  public function invoicesalary(){
    return $this->belongsTo(invoicesalary::class, 'invoice_salary_id');
  }

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d');
  }
}
