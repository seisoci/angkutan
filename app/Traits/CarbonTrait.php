<?php
namespace App\Traits;

use Carbon\Carbon;

trait CarbonTrait {
  public function toDate($datetime){
    if(is_string($datetime)){
      $format = Carbon::createFromFormat('Y-m-d H:i:s', $datetime. '23:59:59', 'UTC')->setTimezone('America/Los_Angeles')->format('Y-m-d');
      return $format;
    }
    return NULL;
  }
}
