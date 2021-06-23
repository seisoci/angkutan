<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\ConfigCoa;
use App\Models\Employee;
use App\Models\Journal;
use App\Models\MonthlySalaryDetail;
use App\Models\MonthlySalaryDetailEmployee;
use App\Models\Setting;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class MonthlySalaryDetailController extends Controller
{
  use CarbonTrait;

  public function index($id, Request $request)
  {
    $config['page_title'] = "List Gaji Bulanan Karyawaan";
    $config['page_description'] = "List Gaji Bulanan Karyawaan";
    $page_breadcrumbs = [
      ['page' => '/backend/employeessalary', 'title' => "List Gaji Bulanan"],
      ['page' => '#', 'title' => "List Gaji Bulanan Karyawaan"],
    ];
    $data = MonthlySalaryDetail::with(['employee', 'monthlysalary', 'monthlysalarydetailemployees'])
      ->withSum('monthlysalarydetailemployees', 'amount')
      ->where('monthly_salary_id', $id);
    if ($request->ajax()) {
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('details_url', function (MonthlySalaryDetail $monthlySalaryDetail) {
          return route('backend.monthlysalarydetail.datatabledetail', $monthlySalaryDetail->id);
        })
        ->addColumn('action', function ($row) {
          $btnEditStatus = $row->status == '0' ? '<a href="#" data-toggle="modal" data-target="#modalEdit" data-id="' . $row->id . '" class="dropdown-item">Edit Status</a>' : NULL;
          $actionBtn = '
             <div class="dropdown">
                 <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                     <i class="fas fa-eye"></i>
                 </button>
                 <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                   <a href="' . $row->id . '/detail" class="dropdown-item">Detail Gaji</a>
                   ' . $btnEditStatus . '
                 </div>
             </div>
           ';
          return $actionBtn;
        })
        ->make(true);
    }
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'monthlysalarydetail')->sole();
    return view('backend.accounting.monthlysalarydetail.index', compact('config', 'page_breadcrumbs', 'selectCoa'));
  }

  public function show($id)
  {
    $config['page_title'] = "Detail Gaji Bulanan Karyawaan";
    $config['page_description'] = "Detail Gaji Bulanan Karyawaan";
    $config['print_url'] = "/backend/monthlysalarydetail/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/employeessalary', 'title' => "List Gaji Bulanan"],
      ['page' => '/backend/monthlysalarydetail', 'title' => "Detail Gaji Bulanan"],
      ['page' => '#', 'title' => "Detail Gaji Bulanan Karyawaan"],
    ];
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    $data = Employee::with(['monthlysalarydetail', 'monthlysalarydetail.employee:id,name', 'monthlysalarydetail.monthlysalarydetailemployees.employeemaster', 'monthlysalarydetail.monthlysalary'])
      ->whereHas('monthlysalarydetail', function ($q) use ($id) {
        $q->where('id', $id);
      })
      ->firstOrFail();

    return view('backend.accounting.monthlysalarydetail.show', compact('config', 'page_breadcrumbs', 'data', 'profile'));
  }

  public function print($id)
  {
    $config['page_title'] = "Detail Gaji Bulanan Karyawaan";
    $config['page_description'] = "Detail Gaji Bulanan Karyawaan";
    $page_breadcrumbs = [
      ['page' => '/backend/employeessalary', 'title' => "List Gaji Bulanan"],
      ['page' => '/backend/monthlysalarydetail', 'title' => "Detail Gaji Bulanan"],
      ['page' => '#', 'title' => "Detail Gaji Bulanan Karyawaan"],
    ];
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    $data = Employee::with(['monthlysalarydetail', 'monthlysalarydetail.employee:id,name', 'monthlysalarydetail.monthlysalarydetailemployees.employeemaster', 'monthlysalarydetail.monthlysalary'])
      ->whereHas('monthlysalarydetail', function ($q) use ($id) {
        $q->where('id', $id);
      })
      ->firstOrFail();

    return view('backend.accounting.monthlysalarydetail.print', compact('config', 'page_breadcrumbs', 'data', 'profile'));
  }

  public function update(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'coa_id' => 'required|integer',

    ]);
    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $data = MonthlySalaryDetail::with('monthlysalary')->withSum('monthlysalarydetailemployees', 'amount')->findOrFail($id);
        $coa = Coa::findOrFail($request->coa_id);
        $checksaldo = DB::table('journals')
          ->select(DB::raw('
          IF(`coas`.`normal_balance` = "Db", (SUM(`journals`.`debit`) - SUM(`journals`.`kredit`)),
          (SUM(`journals`.`kredit`) - SUM(`journals`.`debit`))) AS `saldo`
          '))
          ->leftJoin('coas', 'coas.id', '=', 'journals.coa_id')
          ->where('journals.coa_id', $request->coa_id)
          ->groupBy('journals.coa_id')
          ->first();
        $monthlySalaryDetail = MonthlySalaryDetail::findOrFail($id);
        $employee = Employee::findOrFail($monthlySalaryDetail->employee_id);
        if (($checksaldo->saldo ?? FALSE) && $data->monthlysalarydetailemployees_sum_amount <= $checksaldo->saldo) {
          Journal::create([
            'coa_id' => $request->coa_id,
            'date_journal' => $this->getEndOfMonthByMonthYear($data->monthlysalary->name),
            'debit' => 0,
            'kredit' => $data->monthlysalarydetailemployees_sum_amount,
            'table_ref' => 'monthlysalarydetail',
            'code_ref' => $id,
            'description' => "Pembayaran gaji karyawaan $employee->name bulan ". $data->monthlysalary->name
          ]);

          Journal::create([
            'coa_id' => 38,
            'date_journal' => $this->getEndOfMonthByMonthYear($data->monthlysalary->name),
            'debit' => $data->monthlysalarydetailemployees_sum_amount,
            'kredit' => 0,
            'table_ref' => 'monthlysalarydetail',
            'code_ref' => $id,
            'description' => "Beban gaji karyawaan $employee->name dengan $coa->name bulan ". $data->monthlysalary->name
          ]);

          $data->update([
            'status' => '1',
            'coa_id' => $request->coa_id
          ]);

          DB::commit();
          $response = response()->json([
            'status' => 'success',
            'message' => 'Data has been updated',
          ]);
        } else {
          DB::rollBack();
          $response = response()->json([
            'status' => 'errors',
            'message' => "Saldo $coa->name tidak ada/kurang",
          ]);
        }
      } catch
      (\Throwable $throw) {
        DB::rollBack();
        $response = $throw;
      }
    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }

    return $response;
  }

  public
  function datatabledetail($id)
  {
    $data = MonthlySalaryDetailEmployee::with(['employeemaster'])->where('monthly_salary_detail_id', $id);

    return Datatables::of($data)->make(true);
  }
}
