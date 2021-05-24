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

  public function toDateServer($date){
    return Carbon::createFromFormat('Y-m-d H:i:s', $date." 23:59:59", 'UTC')->setTimezone('America/Los_Angeles')->format('Y-m-d H:i:s');
  }
}
