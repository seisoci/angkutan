<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends \Spatie\Activitylog\Models\Activity
{
    use HasFactory;
ww
    public function user(){
      return $this->belongsTo(User::class, 'causer_id');
    }
}
