<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperJournal
 */
class Journal extends Model
{
  use HasFactory, Notifiable, LogsActivity;
  protected static $logName = 'Jurnal Transaksi';
  protected static $logFillable = true;
  protected static $logAttributes = ['coa.name'];
  protected static $logAttributesToIgnore = ['coa_id'];
  public $timestamps = false;

  protected $fillable = [
    'coa_id',
    'date_journal',
    'debit',
    'kredit',
    'table_ref',
    'code_ref',
    'description',
    'can_delete',
  ];

  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d');
  }

  public function coa()
  {
    return $this->belongsTo(Coa::class,'coa_id');
  }

}
