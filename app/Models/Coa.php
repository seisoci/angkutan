<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
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
  public $appends = [
    'kode_akun',
  ];

  protected $fillable = [
    'name',
    'code',
    'parent_id',
    'type',
    'normal_balance',
  ];

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }

  public function parent()
  {
    return $this->belongsTo(Coa::class, 'parent_id')->whereNull('parent_id')->with('parent_id');
  }

  public function children()
  {
    return $this->hasMany(Coa::class, 'parent_id');
  }

  public function journal()
  {
    return $this->hasMany(Journal::class, 'coa_id');
  }

  public function getKodeAkunAttribute()
  {
    return $this->code . " - " . $this->name;
  }

  public function configcoa()
  {
    return $this->belongsToMany(ConfigCoa::class);
  }
}
