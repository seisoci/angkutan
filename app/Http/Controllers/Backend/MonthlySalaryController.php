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
  function __construct()
  {
    $this->middleware('permission:employeessalary-list|employeessalary-create|employeessalary-edit|employeessalary-delete', ['only' => ['index']]);
    $this->middleware('permission:employeessalary-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:employeessalary-edit', ['only' => ['edit', 'update']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "List Gaji Bulanan";
    $config['page_description'] = "Daftar List Gaji Bulanan";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Gaji Bulanan"],
    ];
    if ($request->ajax()) {
      $data = MonthlySalary::query();
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          $actionBtn = '
             <div class="dropdown">
                 <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                     <i class="fas fa-eye"></i>
                 </button>
                 <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                   <a href="monthlysalarydetail/' . $row->id . '" class="dropdown-item">Show List Karyawaan</a>
                 </div>
             </div>
           ';
          return $actionBtn;
        })
        ->make(true);
    }
    return view('backend.accounting.monthlymaster.index', compact('config', 'page_breadcrumbs'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'monthyear' => 'required|date_format:M Y',
    ]);

    $date_format = Carbon::createFromFormat('d M Y H:i:s', '2 ' . $request->monthyear . '23:59:59', 'UTC')
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
        try {
          DB::beginTransaction();
          $monthsalary = MonthlySalary::where('name', $date_format)->firstOrFail();

          $employee = Employee::where('status', 'active')->get();
          foreach ($employee as $item):
//            dd($item);
            $monthsalarydetail = NULL;
            $deleted = MonthlySalaryDetail::where([
              ['monthly_salary_id', $monthsalary->id],
              ['employee_id', $item->id],
            ]);

            if ($deleted->count() < 1) {
              $monthsalarydetail = MonthlySalaryDetail::create([
                'monthly_salary_id' => $monthsalary->id,
                'employee_id' => $item->id
              ]);
            }
            if ($deleted->where('status', '0')->count() >= 1) {
              $deleted->where('status', '0')->delete();
              $monthsalarydetail = MonthlySalaryDetail::create([
                'monthly_salary_id' => $monthsalary->id,
                'employee_id' => $item->id
              ]);
            }


            $employeesalary = DB::table('employee_employee_master')
              ->where('employee_id', $item->id)->get();
            foreach ($employeesalary as $itemdetail):
              if ($monthsalarydetail) {
                MonthlySalaryDetailEmployee::where([
                  ['monthly_salary_detail_id', $monthsalarydetail->id],
                  ['employee_master_id', $itemdetail->employee_master_id]
                ]);
                MonthlySalaryDetailEmployee::create([
                  'monthly_salary_detail_id' => $monthsalarydetail->id,
                  'employee_master_id' => $itemdetail->employee_master_id,
                  'amount' => $itemdetail->amount
                ]);
              }
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
      }

    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }


}
