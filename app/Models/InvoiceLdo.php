<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperInvoiceLdo
 */
class InvoiceLdo extends Model
{
  use HasFactory, Notifiable, LogsActivity;

  protected $appends = ['num_invoice'];
  protected static $logName = 'Invoice LDO';
  protected static $logFillable = true;
  protected static $logAttributes = ['anotherexpedition.name'];
  protected static $logAttributesToIgnore = ['another_expedition_id'];

  protected $fillable = [
    'prefix',
    'num_bill',
    'another_expedition_id',
    'invoice_date',
    'due_date',
    'total_bill',
    'total_cut',
    'total_payment',
    'rest_payment',
    'memo',
  ];

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }


  public function anotherexpedition()
  {
    return $this->belongsTo(AnotherExpedition::class, 'another_expedition_id');
  }

  public function joborders()
  {
    return $this->hasMany(JobOrder::class);
  }

  public function getNumInvoiceAttribute()
  {
    return ($this->prefix . "-" . $this->num_bill);
  }

  public function paymentldos()
  {
    return $this->hasMany(PaymentLdo::class);
  }
}
