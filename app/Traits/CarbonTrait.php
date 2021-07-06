<?php
namespace App\Traits;

use Carbon\Carbon;

trait CarbonTrait {
  public function toDate($datetime): ?string
  {
    if(is_string($datetime)){
      return Carbon::createFromFormat('Y-m-d H:i:s', $datetime. '23:59:59', 'UTC')->setTimezone('America/Los_Angeles')->format('Y-m-d');
    }
    return NULL;
  }

  public function dateTimeNow(){
    return Carbon::now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s');
  }

  public function dateNow(){
    return Carbon::now()->setTimezone('Asia/Jakarta')->format('Y-m-d');
  }

  public function toDateServerStart($date){
    return Carbon::createFromFormat('Y-m-d H:i:s', $date." 00:00:00", 'UTC')->setTimezone('America/Los_Angeles')->format('Y-m-d H:i:s');
  }

  public function toDateServerEnd($date){
    return Carbon::createFromFormat('Y-m-d H:i:s', $date." 23:59:59", 'UTC')->setTimezone('America/Los_Angeles')->format('Y-m-d H:i:s');
  }


  public function getEndOfMonthByMonthYear($date){
    return Carbon::createFromFormat('M Y', $date)->endOfMonth()->format('Y-m-d');
  }

  public function getStartOfMonthByMonthYear($date){
    return Carbon::createFromFormat('M Y', $date)->startOfMonth()->format('Y-m-d');
  }

  public function getStartOfMonthByNow(){
    return Carbon::now()->startOfMonth()->format('Y-m-d');
  }

  public function convertToMonthYear($date){
    return Carbon::parse($date)->timezone('Asia/Jakarta')->format('Y M');
  }

  public function convertToYear($date){
    return Carbon::parse($date)->timezone('Asia/Jakarta')->format('Y');
  }

  public function convertToMonth($date){
    return Carbon::parse($date)->timezone('Asia/Jakarta')->format('m');
  }

  public function convertToDate($date){
    return Carbon::parse($date)->timezone('Asia/Jakarta')->format('Y-m-d');
  }
}
