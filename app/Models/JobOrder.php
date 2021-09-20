<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperJobOrder
 */
class JobOrder extends Model
{
  use HasFactory;

  protected $fillable = [
    'no_sj',
    'no_shipment',
    'payload',
    'status_cargo',
    'status_document',
    'date_end',
    'status_salary',
    'status_payment',
    'status_payment_ldo',
    'road_money_prev',
    'road_money_extra',

  ];

  protected $appends = [
    'num_prefix',
    'total_basic_price',
    'total_basic_price_after_tax',
    'total_basic_price_after_thanks',
    'total_basic_price_ldo',
    'total_operational',
    'total_sparepart',
    'total_salary',
    'total_netto_ldo',
    'total_clean_summary',
    'tax_amount'
  ];

  protected function serializeDate(DateTimeInterface $date)
  {
    return $date->format('Y-m-d H:i:s');
  }

  public function getStatusSalaryAttribute($value)
  {
    if ("{$this->type}" == 'self') {
      return $value;
    } else {
      return NULL;
    }
  }

  public function routeto()
  {
    return $this->belongsTo(Route::class, 'route_to');
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

  public function getTotalBasicPriceAttribute()
  {
    return $this->basic_price * $this->payload;
  }

  public function getTotalBasicPriceLdoAttribute()
  {
    return $this->basic_price_ldo * $this->payload;
  }

  public function getTotalBasicPriceAfterTaxAttribute()
  {
    return $this->total_basic_price - ($this->total_basic_price * ($this->tax_percent / 100));
  }

  public function getTotalBasicPriceAfterThanksAttribute()
  {
    return $this->total_basic_price_after_tax - $this->fee_thanks;
  }

  public function getTotalOperationalAttribute()
  {
    return $this->operationalexpense_sum_amount + $this->road_money;
  }

  public function getTotalSparepartAttribute()
  {
    return ($this->total_basic_price_after_thanks - $this->total_operational) * ($this->cut_sparepart_percent / 100);
  }

  public function getTotalSalaryAttribute()
  {
    return ($this->total_basic_price_after_thanks - $this->total_operational - $this->total_sparepart) * ($this->salary_percent / 100);
  }

  public function getTotalNettoLdoAttribute()
  {
    return $this->total_basic_price_ldo - ($this->operationalexpense_sum_amount + $this->road_money);
  }

  public function getNumPrefixAttribute()
  {
    return ($this->prefix . "-" . $this->num_bill);
  }

  public function getTotalCleanSummaryAttribute()
  {
    return $this->total_basic_price_after_thanks - $this->total_operational - $this->total_sparepart - $this->total_salary;
  }

  public function getTaxAmountAttribute()
  {
    return $this->total_basic_price * ($this->tax_percent / 100);
  }

  public function coaldo()
  {
    return $this->belongsTo(Coa::class, 'payment_ldo_coa_id');
  }
}
