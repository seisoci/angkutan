<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperCoa
 */
class Coa extends Model
{
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'Master Akun COA';
  protected static $logFillable = true;
  protected static $logAttributesToIgnore = ['parent'];
  public $timestamps = false;
  public  $appends = [
    'kode_akun'
  ];

  protected $fillable = [
    'name',
    'code',
    'parent_id',
    'type',
    'normal_balance',
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d');
  }

  public function parent()
  {
    return $this->belongsTo(Coa::class,'parent_id')->whereNull('parent_id')->with('parent_id');
  }

  public function children(){
    return $this->hasMany(Coa::class, 'parent_id')->with('children');
  }

  public function getKodeAkunAttribute()
  {
    return $this->code ." - ". $this->name;
  }
}
