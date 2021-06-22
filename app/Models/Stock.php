<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use DateTimeInterface;
/**
 * @mixin IdeHelperStock
 */
class Stock extends Model
{
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'Stok Sparepart';
  protected static $logFillable = true;
  protected static $logAttributes = ['sparepart.name'];
  protected static $logAttributesToIgnore = ['sparepart_id'];

  protected $fillable = [
    'sparepart_id',
    'invoice_purchase_id',
    'qty',
  ];

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }

  public function sparepart(){
    return $this->belongsTo(Sparepart::class);
  }

  public function invoicepurchase(){
    return $this->belongsTo(InvoicePurchase::class, 'invoice_purchase_id');
  }
}
