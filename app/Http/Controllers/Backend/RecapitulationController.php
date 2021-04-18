<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\JobOrder;
use App\Models\Setting;
use App\Models\Transport;
use Illuminate\Http\Request;
use Validator;

class RecapitulationController extends Controller
{
    public function index(Request $request)
    {
      $validator = Validator::make($request->all(), [
        'transport_id'  => 'integer|nullable',
        'driver_id'     => 'integer|nullable',
        'date_begin'    => 'date_format:Y-m-d',
        'date_end'      => 'date_format:Y-m-d',
      ]);

      $config['page_title']       = "Laporan Rekapitulasi";
      $config['page_description'] = "Laporan Rekapitulasi";
      $page_breadcrumbs = [
        ['page' => '#','title' => "Laporan Rekapitulasi"],
      ];
      $collection = Setting::all();
      $profile = collect($collection)->mapWithKeys(function ($item) {
        return [$item['name'] => $item['value']];
      });
      $data         = array();
      $transport    = NULL;
      $driver       = NULL;
      if($validator->passes()){
        $driver_id    = $request->driver_id;
        $transport_id = $request->transport_id;
        $date_begin   = $request->date_begin;
        $date_end     = $request->date_end;

        if($request->all() != NULL){
          $transport = isset($transport_id) || !empty($transport_id) ? Transport::findOrFail($transport_id) : 'Semua Mobil';
          $driver = isset($driver_id) || !empty($driver_id) ? Driver::findOrFail($driver_id)->select('id', 'num_pol') : 'Semua Supir';
          $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name', 'operationalexpense.expense'])
          ->withSum('operationalexpense','amount')
          ->where('type', 'self')
          ->when($driver_id, function ($query, $driver_id) {
            return isset($driver_id) ? $query->where('driver_id', $driver_id) : NULL;
          })
          ->when($transport_id, function ($query, $transport_id) {
            return isset($transport_id) ? $query->where('transport_id', $transport_id) : NULL;
          })
          ->whereBetween('date_begin', [$date_begin, $date_end])
          ->get();
        }
      }else{
        return redirect()->back()->withErrors($validator->errors());
      }

      return view('backend.operational.recapitulation.index', compact('config', 'page_breadcrumbs', 'data', 'profile', 'date_begin', 'date_end', 'transport_id', 'driver_id', 'driver', 'transport'));
    }
}
