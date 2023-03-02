<?php

namespace App\Console\Commands;

use App\Models\JobOrder;
use App\Models\TypeCapacity;
use App\Services\JobOrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class JobOrderCron extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'joborder';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Generate Kalkulasi JO';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle()
  {
    try {
      $jobOrderService = new JobOrderService();

      $data = JobOrder::all();
      $typeCapacity = TypeCapacity::all()->keyBy('name');

      foreach ($data ?? [] as $item):
        $calculate = $jobOrderService->calculate($item);
        $jo = JobOrder::find($item['id']);
        $typeCapacityJO = !is_numeric($item['type_capacity']) ? $typeCapacity[$item['type_capacity']]['id'] : $item['type_capacity'];
        $prefix = $item['prefix']."-".$item['num_bill'];
        $jo->update(array_merge([
          'type_capacity' =>  $typeCapacityJO,
          'num_bill' =>  $prefix
        ],$calculate));
      endforeach;
    }catch(\Throwable $throw){
      Log::error($throw);
    }
  }
}
