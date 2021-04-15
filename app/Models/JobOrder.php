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
    'status_salary',
    'status_payment',
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
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
}
