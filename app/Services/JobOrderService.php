<?php

namespace App\Services;


use App\Models\OperationalExpense;
use App\Models\RoadMoney;

class JobOrderService
{

  public function calculate($jobOrder)
  {
    $operationalExpense = OperationalExpense::selectRaw('
      COALESCE(SUM(`amount`),0) AS total
    ')
      ->where('job_order_id', $jobOrder['id'])
      ->where([
        ['type', 'operational'],
        ['approved', '1'],
      ])
      ->first();

    $operationalLdo = OperationalExpense::selectRaw('
      COALESCE(SUM(`amount`),0) AS total
    ')
      ->where('job_order_id', $jobOrder['id'])
      ->where([
        ['type', 'roadmoney'],
        ['approved', '1'],
      ])
      ->first();

    $roadMoney = RoadMoney::where('costumer_id', $jobOrder['costumer_id'])
      ->where('route_from', $jobOrder['route_from'])
      ->where('route_to', $jobOrder['route_to'])
      ->where('cargo_id', $jobOrder['cargo_id'])
      ->first();

    $totalBasicPrice = $jobOrder['basic_price'] * $jobOrder['payload'];
    $totalBasicPriceLdo = $jobOrder['basic_price_ldo'] * $jobOrder['payload'];
    $totalBasicPriceAfterTax = $totalBasicPrice - ($totalBasicPrice * ($jobOrder['tax_percent'] / 100));
    $totalBasicPriceAfterThanks = $totalBasicPriceAfterTax - $jobOrder['fee_thanks'];
    $totalOperational = $operationalExpense['total'] + $jobOrder['road_money'];
    $totalSparePart = ($totalBasicPriceAfterThanks - $totalOperational) * ($jobOrder['cut_sparepart_percent'] / 100);
    $totalSalary = $jobOrder['type_salary'] == 'dynamic' ? ($totalBasicPriceAfterThanks - $totalOperational - $totalSparePart) * ($jobOrder['salary_percent'] / 100) : $roadMoney['salary_amount'];
    $totalNettoLdo = $totalBasicPriceLdo - $operationalLdo['total'];
    $totalCleanSummary = $totalBasicPriceAfterThanks - $totalOperational - $totalSparePart - $totalSalary;
    $taxAmount = $totalBasicPrice * ($jobOrder['tax_percent'] / 100);

    return [
      'invoice_bill' => $totalBasicPrice,
      'total_basic_price' => $totalBasicPrice,
      'total_basic_price_ldo' => $totalBasicPriceLdo,
      'total_basic_price_after_tax' => $totalBasicPriceAfterTax,
      'total_basic_price_after_thanks' => $totalBasicPriceAfterThanks,
      'total_operational' => $totalOperational,
      'total_sparepart' => $totalSparePart,
      'total_salary' => $totalSalary,
      'total_netto_ldo' => $totalNettoLdo,
      'total_clean_summary' => $totalCleanSummary,
      'tax_amount' => $taxAmount,
    ];
  }
}
