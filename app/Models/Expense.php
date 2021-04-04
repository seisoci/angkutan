<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
  use HasFactory;

  protected $fillable = [
    'name_amount',
    'amount',
  ];

  protected $casts = [
    'created_at' => 'date:Y-m-d H:i:s',
  ];
}
