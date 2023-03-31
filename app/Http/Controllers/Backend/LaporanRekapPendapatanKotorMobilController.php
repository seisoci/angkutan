<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\JobOrder;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class LaporanRekapPendapatanKotorMobilController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:laporan-pendapatan-kotor-mobil-list|laporan-pendapatan-kotor-mobil-create|laporan-pendapatan-kotor-mobil-edit|laporan-pendapatan-kotor-mobil-delete', ['only' => ['index']]);
    $this->middleware('permission:laporan-pendapatan-kotor-mobil-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:laporan-pendapatan-kotor-mobil-edit', ['only' => ['edit', 'update']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Pendapatan Kotor Mobil";
    $config['page_description'] = "Daftar List Laporan Pendapatan Kotor Mobil";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Laporan Pendapatan Kotor Mobil"],
    ];

    if($request->ajax()){
      $data = JobOrder::selectRaw('
          `job_orders`.`type`,
          `transports`.`num_pol` AS `transport_name`,
           COALESCE((SUM(`job_orders`.`total_basic_price_after_tax`) - SUM(`total_operational`)), 0) AS `total_kotor`
        ')
        ->where('job_orders.type', 'self')
        ->leftJoin('transports', 'transports.id', '=', 'job_orders.transport_id')
        ->groupBy('transport_id');


      if($request->filled('transport_id')){
        $data->where('transport_id', $request['transport_id']);
      }
      if($request->filled('date_begin_start')){
        $data->where('date_begin', '>=', $request['date_begin_start']);
      }
      if($request->filled('date_begin_end')){
        $data->where('date_begin', '<=', $request['date_begin_end']);
      }
      if($request->filled('date_end_start')){
        $data->where('date_end', '>=', $request['date_end_start']);
      }
      if($request->filled('date_end_end')){
        $data->where('date_end', '<=', $request['date_end_end']);
      }
      if($request->filled('status_cargo')){
        $data->where('status_cargo', $request['status_cargo']);
      }
      if($request->filled('status_document')){
        $data->where('status_document', $request['status_document']);
      }

      return DataTables::of($data)
        ->addIndexColumn()
        ->make();
    }

    return view('backend.report.laporan-pendapatan-kotor-mobil.index', compact('config', 'page_breadcrumbs'));
  }
}
