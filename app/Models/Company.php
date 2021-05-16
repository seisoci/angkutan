<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperCompany
 */
class Company extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
      return $date->format('Y-m-d H:i:s');
  }
}
