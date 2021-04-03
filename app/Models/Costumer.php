<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Costumer extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'emergency_name',
    'emergency_phone',
    'phone',
    'address',
    'description',
  ];

  protected $casts = [
    'created_at' => 'date:Y-m-d H:i:s',
  ];
}
