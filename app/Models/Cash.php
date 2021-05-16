<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperCash
 */
class Cash extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'type',
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
      return $date->format('Y-m-d H:i:s');
  }
}
