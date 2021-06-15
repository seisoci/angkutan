<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\ConfigCoa;
use App\Models\Driver;
use App\Models\Journal;
use App\Models\KasbonEmployee;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class KasbonEmployeeController extends Controller
{
  use CarbonTrait;

  public function index(Request $request)
  {
    $config['page_title'] = "List Kasbon Karyawaan";
    $config['page_description'] = "Daftar List Kasbon Karyawaan";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Kasbon Karyawaan"],
    ];

    if ($request->ajax()) {
      $data = KasbonEmployee::with('employee:id,name');
      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'kasbonemployees')->sole();

    return view('backend.accounting.kasbonemployee.index', compact('config', 'page_breadcrumbs', 'selectCoa'));
  }

  public function create(Request $request)
  {
    $config['page_title'] = "Create Invoice Kasbon Supir";
    $config['page_description'] = "Create Invoice Kasbon Supir";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Create Invoice Kasbon Supir"],
    ];
    $employee_id = $request->employee_id;
    if ($request->ajax()) {
      $data = KasbonEmployee::with(['employee:id,name'])
        ->when($employee_id, function ($query, $employee_id) {
          return $query->where('employee_id', $employee_id);
        });
      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    return view('backend.invoice.invoicekasbons.create', compact('config', 'page_breadcrumbs'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'employee_id' => 'required|integer',
      'coa_id' => 'required|integer',
      'amount' => 'required|integer',
      'memo' => 'required|string',
    ]);


    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $employee = Driver::findOrFail($request->employee_id);
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
        if (($checksaldo->saldo ?? FALSE) && $request->amount <= $checksaldo->saldo) {
          KasbonEmployee::create([
            'employee_id' => $request->input('employee_id'),
            'coa_id' => $request->input('coa_id'),
            'amount' => $request->input('amount'),
            'memo' => $request->input('memo'),
          ]);
          Journal::create([
            'coa_id' => 8,
            'date_journal' => $this->dateNow(),
            'debit' => $request->input('amount'),
            'kredit' => 0,
            'table_ref' => 'kasbon',
            'description' => "Karyawaan $employee->name melakukan kasbon dengan $coa->name"
          ]);
          Journal::create([
            'coa_id' => $request->input('coa_id'),
            'date_journal' => $this->dateNow(),
            'debit' => 0,
            'kredit' => $request->input('amount'),
            'table_ref' => 'kasbonemployees',
            'description' => "Pengeluaran untuk kasbon $employee->name"
          ]);
          $response = response()->json([
            'status' => 'success',
            'message' => 'Data has been saved',
          ]);
          DB::commit();
        } else {
          DB::rollBack();
          $response = response()->json([
            'status' => 'errors',
            'message' => "Saldo $coa->name tidak ada/kurang",
          ]);
        }
      } catch (\Throwable $throw) {
        DB::rollBack();
        $response = $throw;
      }
    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }
}
