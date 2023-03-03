<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cooperation;
use App\Models\Driver;
use App\Models\JobOrder;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class LaporanRekapGajiBulananController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:laporan-rekap-gaji-bulanan-list|laporan-rekap-gaji-bulanan-create|laporan-rekap-gaji-bulanan-edit|laporan-rekap-gaji-bulanan-delete', ['only' => ['index']]);
    $this->middleware('permission:laporan-rekap-gaji-bulanan-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:laporan-rekap-gaji-bulanan-edit', ['only' => ['edit', 'update']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Rekap Gaji Supir Bulanan (Dokumen Sudah Kembali)";
    $config['page_description'] = "Daftar List Laporan Rekap Gaji Supir Bulanan (Dokumen Sudah Kembali)";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Laporan Rekap Gaji Bulanan (Dokumen Sudah Kembali)"],
    ];
    $config['excel_url'] = 'laporan-rekap-gaji-bulanan/document?type=EXCEL';
    $config['pdf_url'] = 'laporan-rekap-gaji-bulanan/document?type=PDF';
    $config['print_url'] = 'laporan-rekap-gaji-bulanan/print';

    if($request->ajax()){
      $data = JobOrder::selectRaw('
          `drivers`.`name` AS `driver_name`,
           COALESCE(COUNT(`job_orders`.`id`), 0) AS `total_count`,
          `rf`.`name` AS `route_from`,
          `rf`.`name` AS `route_to`,
          `job_orders`.`date_begin`,
          `job_orders`.`date_end`,
           COALESCE(SUM(`job_orders`.`total_salary`), 0) AS `total_salary`
      ')
      ->leftJoin('costumers', 'costumers.id', '=', 'job_orders.costumer_id')
      ->leftJoin('drivers', 'drivers.id', '=', 'job_orders.driver_id')
      ->leftJoin('routes AS rf', 'rf.id', '=', 'job_orders.route_from')
      ->leftJoin('routes AS rt', 'rt.id', '=', 'job_orders.route_to')
      ->where('status_document', 1)
      ->groupBy('driver_id');

      if($request->filled('driver_id')){
        $data->where('driver_id', $request['driver_id']);
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

      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }

    return view('backend.report.laporan-rekap-gaji-bulanan.index', compact('config', 'page_breadcrumbs'));
  }

  public function print(Request $request)
  {
    $config['page_title'] = "Laporan Gaji Supir Bulanan";
    $config['page_description'] = "Gaji Supir Bulanan";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Gaji Supir Bulanan"],
    ];

    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = JobOrder::selectRaw('
          `drivers`.`name` AS `driver_name`,
           COALESCE(COUNT(`job_orders`.`id`), 0) AS `total_count`,
          `rf`.`name` AS `route_from`,
          `rf`.`name` AS `route_to`,
          `job_orders`.`date_begin`,
          `job_orders`.`date_end`,
           COALESCE(SUM(`job_orders`.`total_salary`), 0) AS `total_salary`
      ')
      ->leftJoin('costumers', 'costumers.id', '=', 'job_orders.costumer_id')
      ->leftJoin('drivers', 'drivers.id', '=', 'job_orders.driver_id')
      ->leftJoin('routes AS rf', 'rf.id', '=', 'job_orders.route_from')
      ->leftJoin('routes AS rt', 'rt.id', '=', 'job_orders.route_to')
      ->where('status_document', 1)
      ->groupBy('driver_id');

    if($request->filled('driver_id')){
      $data->where('driver_id', $request['driver_id']);
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

    $data = $data->get();

    return view('backend.report.laporan-rekap-gaji-bulanan.print', compact('config', 'page_breadcrumbs', 'cooperationDefault', 'data'));
  }

}
