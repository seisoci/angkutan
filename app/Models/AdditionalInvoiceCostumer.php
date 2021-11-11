<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperAdditionalInvoiceCostumer
 */
class AdditionalInvoiceCostumer extends Model
{
    use HasFactory, Notifiable, LogsActivity;

  protected $appends = ['num_invoice', 'total_netto'];
  protected static $logName = 'Tambahan Biaya Invoice Tagihan Pelanggan';
  protected static $logFillable = true;

  protected $fillable = [
    'invoice_costumer_id',
    'description',
    'total'
  ];
}
