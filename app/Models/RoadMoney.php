<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperRoadMoney
 */
class RoadMoney extends Model
{
  use HasFactory, Notifiable, LogsActivity;

  protected static $logName = 'Master Uang Jalan';
  protected static $logFillable = true;

  protected $fillable = [
    'costumer_id',
    'route_from',
    'route_to',
    'cargo_id',
    'fee_thanks',
    'tax_pph',
    'road_engkel',
    'road_tronton',
  ];

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }

  public function costumers()
  {
    return $this->belongsTo(Costumer::class, 'costumer_id');
  }

  public function routefrom()
  {
    return $this->belongsTo(Route::class, 'route_from')->orderBy('name', 'asc');
  }

  public function routeto()
  {
    return $this->belongsTo(Route::class, 'route_to')->orderBy('name', 'asc');
  }

  public function cargo()
  {
    return $this->belongsTo(Cargo::class);
  }

  public function typecapacities()
  {
    return $this->belongsToMany(TypeCapacity::class, 'roadmoney_typecapacity')->withPivot(['road_engkel', 'road_tronton', 'type', 'expense']);
  }
}
