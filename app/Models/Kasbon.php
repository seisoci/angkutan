<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperKasbon
 */
class Kasbon extends Model
{
  use HasFactory, Notifiable, LogsActivity;

  protected static $logName = 'Data Kasbon Supir';
  protected static $logFillable = true;
  protected static $logAttributes = ['driver.name'];
  protected static $logAttributesToIgnore = ['driver_id'];

  protected $fillable = [
    'driver_id',
    'amount',
  ];

  public function driver()
  {
    return $this->belongsTo(Driver::class, 'driver_id');
  }

  public function invoicekasbon()
  {
    return $this->belongsTo(InvoiceKasbon::class, 'invoice_kasbon_id');
  }

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }
}
