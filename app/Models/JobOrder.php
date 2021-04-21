<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobOrder extends Model
{
  use HasFactory;
  protected $fillable = [
    'status_cargo',
    'status_document',
    'date_end',
    'status_salary',
    'status_payment',
  ];
  protected $appends = ['num_prefix', 'total_basic_price','total_operational', 'total_sparepart', 'total_salary', 'total_netto_ldo'];


  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }

  public function getStatusSalaryAttribute($value){
    if("{$this->type}" == 'self'){
      return $value;
    }else{
      return NULL;
    }
  }

  public function routeto(){
    return $this->belongsTo(Route::class, 'route_to');
  }

  public function routefrom(){
    return $this->belongsTo(Route::class, 'route_from');
  }

  public function driver(){
    return $this->belongsTo(Driver::class);
  }

  public function transport(){
    return $this->belongsTo(Transport::class, 'transport_id');
  }

  public function cargo(){
    return $this->belongsTo(Cargo::class);
  }

  public function costumer(){
    return $this->belongsTo(Costumer::class);
  }

  public function anotherexpedition(){
    return $this->belongsTo(AnotherExpedition::class, 'another_expedition_id');
  }

  public function operationalexpense(){
    return $this->hasMany(OperationalExpense::class);
  }

  public function getTotalBasicPriceAttribute()
  {
      return $this->basic_price * $this->payload;
  }

  public function getTotalOperationalAttribute()
  {
      return $this->operationalexpense_sum_amount + $this->road_money;
  }

  public function getTotalSparepartAttribute()
  {
      return ($this->total_basic_price - $this->total_operational) * ($this->cut_sparepart_percent /100);
  }

  public function getTotalSalaryAttribute()
  {
      return ($this->total_basic_price - $this->total_operational - $this->total_sparepart) * ($this->salary_percent /100);
  }

  public function getTotalNettoLdoAttribute()
  {
      return ($this->basic_price_ldo * $this->payload) - ($this->operationalexpense_sum_amount + $this->road_money);
  }

  public function getNumPrefixAttribute()
  {
      return ($this->prefix ."-". $this->num_bill);
  }
}
