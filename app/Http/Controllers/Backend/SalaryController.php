<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\JobOrder;
use Illuminate\Http\Request;
use DataTables;

class SalaryController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:salaries-list|salaries-create|salaries-edit|salaries-delete', ['only' => ['index']]);
    $this->middleware('permission:salaries-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:salaries-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:salaries-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Gaji Supir";
    $config['page_description'] = "Daftar Gaji Supir";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Gaji Supir"],
    ];

    $another_expedition_id = $request->another_expedition_id;
    $driver_id = $request->driver_id;
    $transport_id = $request->transport_id;
    $costumer_id = $request->costumer_id;
    $cargo_id = $request->cargo_id;
    $route_from = $request->route_from;
    $route_to = $request->route_to;
    $date_begin = $request->date_begin;
    $date_end = $request->date_end;
    $status_cargo = $request->status_cargo;
    $status_salary = $request->status_salary;
    if ($request->ajax()) {
      $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])
        ->withSum('operationalexpense', 'amount')
        ->where('type', 'self')
        ->where('status_cargo', '=', 'selesai')
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
          if ($status_salary === 'sudah') {
            return $query->where('invoice_salary_id', '<>', NULL);
          } elseif ($status_salary === 'belum') {
            return $query->where('invoice_salary_id', NULL);
          }
          return $query;
        });
      return DataTables::of($data)
        ->editColumn('num_bill', function (JobOrder $jobOrder) {
          return '<a target="_blank" href="/backend/joborders/' . $jobOrder->id . '">' . $jobOrder->num_bill . '</a>';
        })
        ->rawColumns(['num_bill'])
        ->make(true);
    }
    return view('backend.operational.salaries.index', compact('config', 'page_breadcrumbs'));
  }
}
