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
          `transports`.`num_pol` AS `transport_name`,
           COALESCE((SUM(`job_orders`.`total_basic_price`) - SUM(`road_money`) - SUM(`total_operational`)), 0) AS `total_kotor`
        ')
        ->leftJoin('transports', 'transports.id', '=', 'job_orders.transport_id')
        ->groupBy('transport_id');


      if($request->filled('transport_id')){
        $data->where('transport_id', $request['transport_id']);
      }
      if($request->filled('date_begin')){
        $data->where('invoice_date', '>=', $request['date_begin']);
      }
      if($request->filled('date_end')){
        $data->where('invoice_date', '<=', $request['date_end']);
      }

      return DataTables::of($data)
        ->addIndexColumn()
        ->make();
    }

    return view('backend.report.laporan-pendapatan-kotor-mobil.index', compact('config', 'page_breadcrumbs'));
  }
}
