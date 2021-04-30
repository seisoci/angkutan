<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceLdo extends Model
{
    use HasFactory;
    protected $appends = ['num_invoice'];

  protected $fillable = [
    'prefix',
    'num_bill',
    'another_expedition_id',
    'grandtotal',
    'description',
    'memo',
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }

  public function anotherexpedition(){
    return $this->belongsTo(AnotherExpedition::class, 'another_expedition_id');
  }

  public function joborders(){
    return $this->hasMany(JobOrder::class);
  }

  public function getNumInvoiceAttribute()
  {
    return ($this->prefix ."-". $this->num_bill);
  }
}
