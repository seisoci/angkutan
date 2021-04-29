<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\JobOrder;
use DataTables;
use Illuminate\Http\Request;

class ReportCostumerController extends Controller
{
  public function index(Request $request)
    {
      $config['page_title']       = "List Invoice Pelanggan";
      $config['page_description'] = "Daftar List Invoice Pelanggan";
      $page_breadcrumbs = [
        ['page' => '#','title' => "List Invoice Pelanggan"],
      ];
      if ($request->ajax()) {
        $another_expedition_id = $request->another_expedition_id;
        $driver_id    = $request->driver_id;
        $transport_id = $request->transport_id;
        $costumer_id  = $request->costumer_id;
        $cargo_id     = $request->cargo_id;
        $route_from   = $request->route_from;
        $route_to     = $request->route_to;
        $date_begin   = $request->date_begin;
        $date_end     = $request->date_end;
        $status_cargo = $request->status_cargo;
        $status_salary = $request->status_salary;

        $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])
        ->when($another_expedition_id, function ($query, $another_expedition_id) {
          return $query->where('another_expedition_id', $another_expedition_id);
        })
        ->when($driver_id, function ($query, $driver_id) {
          return $query->where('driver_id', $driver_id);
        })
        ->when($transport_id, function ($query, $transport_id) {
          return $query->where('transport_id', $transport_id);
        })
        ->when($costumer_id, function ($query, $costumer_id) {
          return $query->where('costumer_id', $costumer_id);
        })
        ->when($cargo_id, function ($query, $cargo_id) {
          return $query->where('cargo_id', $cargo_id);
        })
        ->when($route_from, function ($query, $route_from) {
          return $query->where('route_from', $route_from);
        })
        ->when($route_to, function ($query, $route_to) {
          return $query->where('route_to', $route_to);
        })
        ->when($date_begin, function ($query, $date_begin) {
          return $query->where('date_begin', $date_begin);
        })
        ->when($date_end, function ($query, $date_end) {
          return $query->where('date_end', $date_end);
        })
        ->when($status_cargo, function ($query, $status_cargo) {
          return $query->where('status_cargo', $status_cargo);
        })
        ->when($status_salary, function ($query, $status_salary) {
          return $query->where('status_salary', $status_salary);
        });
        return DataTables::of($data)
        ->addIndexColumn()
        ->editColumn('num_bill', function(JobOrder $jobOrder){
          return '<a target="_blank" href="/backend/joborders/'.$jobOrder->id.'">'.$jobOrder->num_bill.'</a>';
          })
        ->rawColumns(['num_bill'])
        ->make(true);

      }
      return view('backend.report.reportcostumers.index', compact('config', 'page_breadcrumbs'));
    }
}
