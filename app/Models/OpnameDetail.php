<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpnameDetail extends Model
{
    use HasFactory;
  protected $fillable = [
    'opname_id',
    'sparepart_id',
    'qty',
    'qty_system',
    'qty_difference',
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }

  public function sparepart(){
    return $this->belongsTo(Sparepart::class);
  }

}
