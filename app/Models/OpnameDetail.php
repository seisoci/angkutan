<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperOpnameDetail
 */
class OpnameDetail extends Model
{
  use HasFactory, Notifiable, LogsActivity;

  protected static $logName = 'Opname Detail';
  protected static $logFillable = true;
  protected static $logAttributes = ['sparepart.name', 'opname'];
  protected static $logAttributesToIgnore = ['opname_id', 'sparepart_id'];

  protected $fillable = [
    'opname_id',
    'sparepart_id',
    'qty',
    'qty_system',
    'qty_difference',
  ];

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }

  public function sparepart()
  {
    return $this->belongsTo(Sparepart::class);
  }

  public function opname()
  {
    return $this->belongsTo(Opname::class, 'opname_id');
  }

}
