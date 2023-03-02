<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperJobOrder
 */
class JobOrder extends Model
{
  use HasFactory, LogsActivity;

  protected static $logName = 'Job Order';
  protected static $logFillable = true;

  protected $fillable = [
    'num_bill',
    'prefix',
    'no_sj',
    'no_shipment',
    'date_begin',
    'date_end',
    'invoice_salary_id',
    'invoice_ldo_id',
    'invoice_costumer_id',
    'another_expedition_id',
    'driver_id',
    'transport_id',
    'costumer_id',
    'cargo_id',
    'route_from',
    'route_to',
    'coa_id',
    'type_capacity',
    'type_payload',
    'payload',
    'basic_price',
    'basic_price_ldo',
    'road_money',
    'road_money_prev',
    'road_money_extra',
    'cut_sparepart_percent',
    'salary_percent',
    'tax_percent',
    'fee_thanks',
    'invoice_bill',
    'status_salary',
    'salary_coa_id',
    'status_cargo',
    'status_payment_ldo',
    'status_payment',
    'status_document',
    'status_tax',
    'type',
    'km',
    'description',
    'total_basic_price',
    'total_basic_price_after_tax',
    'total_basic_price_after_thanks',
    'total_basic_price_ldo',
    'total_operational',
    'total_sparepart',
    'type_salary',
    'total_salary',
    'total_netto_ldo',
    'total_clean_summary',
    'tax_amount',
  ];

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }

  public function getStatusSalaryAttribute($value)
  {
    if ($this->type == 'self') {
      return $value;
    }
    return NULL;
  }

  public function routeto()
  {
    return $this->belongsTo(Route::class, 'route_to');
  }

  public function piutangklaimcustomer()
  {
    return $this->hasMany(PiutangKlaim::class)->where('invoice_type', 'customer');
  }

  public function piutangklaimldo()
  {
    return $this->hasMany(PiutangKlaim::class)->where('invoice_type', 'ldo');
  }

  public function routefrom()
  {
    return $this->belongsTo(Route::class, 'route_from');
  }

  public function driver()
  {
    return $this->belongsTo(Driver::class);
  }

  public function transport()
  {
    return $this->belongsTo(Transport::class, 'transport_id');
  }

  public function cargo()
  {
    return $this->belongsTo(Cargo::class);
  }

  public function costumer()
  {
    return $this->belongsTo(Costumer::class);
  }

  public function anotherexpedition()
  {
    return $this->belongsTo(AnotherExpedition::class, 'another_expedition_id');
  }

  public function operationalexpense()
  {
    return $this->hasMany(OperationalExpense::class)->where('type', 'operational')->where('approved', '1');
  }

  public function roadmoneydetail()
  {
    return $this->hasMany(OperationalExpense::class)->where('type', 'roadmoney')->where('approved', '1');
  }

  public function roadmoneyreal()
  {
    return $this->hasMany(OperationalExpense::class)->where('type', 'roadmoney')->where('approved', '1');
  }

  public function coaldo()
  {
    return $this->belongsTo(Coa::class, 'payment_ldo_coa_id');
  }
}
