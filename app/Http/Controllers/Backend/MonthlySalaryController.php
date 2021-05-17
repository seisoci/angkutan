<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\MonthlySalary;
use App\Models\MonthlySalaryDetail;
use App\Models\MonthlySalaryDetailEmployee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class MonthlySalaryController extends Controller
{
  public function index(Request $request)
  {
    $config['page_title'] = "List Prefix";
    $config['page_description'] = "Daftar List Prefix";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Prefix"],
    ];
    if ($request->ajax()) {
      $data = MonthlySalary::query();
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          $actionBtn = '
            <a href="#" data-toggle="modal" data-target="#modalEdit" data-id="' . $row->id . '" data-name="' .
            $row->name . '" data-type="' . $row->type . '" class="edit btn btn-warning btn-sm">Update Data</a>
            <a href="#" data-toggle="modal" data-target="#modalDelete" data-id="' . $row->id . '" class="delete btn btn-danger btn-sm">Delete</a>';
          return $actionBtn;
        })->make(true);
    }
    return view('backend.accounting.monthlymaster.index', compact('config', 'page_breadcrumbs'));
  }


  public function create()
  {

  }


  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'monthyear' => 'required|date_format:M Y',
    ]);

    $date_format = Carbon::createFromFormat('d M Y', '2 ' . $request->monthyear, 'UTC')
      ->setTimezone('America/Los_Angeles')->format('Y-m-d');

    if ($validator->passes()) {
      if (MonthlySalary::where('name', $date_format)->count() < 1) {
        try {
          DB::beginTransaction();
          $monthsalary = MonthlySalary::create([
            'name' => $date_format,
          ]);

          $employee = Employee::where('status', 'active')
            ->with('salaries')->get();

          foreach ($employee as $item):
            $monthsalarydetail = MonthlySalaryDetail::create([
              'monthly_salary_id' => $monthsalary->id,
              'employee_id' => $item->id,
            ]);
            $employeesalary = DB::table('employee_employee_master')
              ->where('employee_id', $item->id)->get();
            foreach ($employeesalary as $itemdetail):
              MonthlySalaryDetailEmployee::create([
                'monthly_salary_detail_id' => $monthsalarydetail->id,
                'employee_master_id' => $itemdetail->employee_master_id,
                'amount' => $itemdetail->amount,
              ]);
            endforeach;
          endforeach;

          DB::commit();
          $response = response()->json([
            'status' => 'success',
            'message' => 'Data has been saved',
          ]);
        } catch (\Throwable $throw) {
          DB::rollBack();
          $response = $throw;
        }
      } else {
        $response = response()->json([
          'status' => 'error',
          'message' => 'Data already exist',
        ]);
      }

    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }


  public function show(MonthlySalary $monthlySalary)
  {
    //
  }


  public function edit(MonthlySalary $monthlySalary)
  {
    //
  }


  public function update(Request $request, MonthlySalary $monthlySalary)
  {
    //
  }


  public function destroy(MonthlySalary $monthlySalary)
  {
    //
  }
}
