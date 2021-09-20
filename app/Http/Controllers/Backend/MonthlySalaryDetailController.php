<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ContinousPaper;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\ConfigCoa;
use App\Models\Cooperation;
use App\Models\Employee;
use App\Models\Journal;
use App\Models\MonthlySalaryDetail;
use App\Models\MonthlySalaryDetailEmployee;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class MonthlySalaryDetailController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:employeessalary-list|employeessalary-create|employeessalary-edit|employeessalary-delete', ['only' => ['index']]);
    $this->middleware('permission:employeessalary-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:employeessalary-edit', ['only' => ['edit', 'update']]);
  }

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
        ->addColumn('action', function ($row) use ($id) {
          $btnEditStatus = $row->status == '0' ? '<a href="#" data-toggle="modal" data-target="#modalEdit" data-id="' . $row->id . '" class="dropdown-item">Edit Status</a>' : NULL;
          $btnDelete = $row->status == '1' ? '<a href="#" data-toggle="modal" data-target="#modalDelete" data-id="' . $row->id . '" class="dropdown-item">Delete</a>' : NULL;
          $actionBtn = '
             <div class="dropdown">
                 <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                     <i class="fas fa-eye"></i>
                 </button>
                 <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                   <a href="' . $row->id . '/detail" class="dropdown-item">Detail Gaji</a>
                   ' . $btnEditStatus . $btnDelete . '
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
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = MonthlySalaryDetail::with(['monthlysalary', 'employee', 'monthlysalarydetailemployees.employeemaster'])->findOrFail($id);
    return view('backend.accounting.monthlysalarydetail.show', compact('config', 'page_breadcrumbs', 'data', 'cooperationDefault'));
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
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = MonthlySalaryDetail::with(['monthlysalary', 'employee', 'monthlysalarydetailemployees.employeemaster'])->findOrFail($id);
    $result = '';
    $no = 1;
    $total = 0;
    foreach ($data->monthlysalarydetailemployees as $val):
      $total += $val->amount;
      $item[] = ['no' => $no++, 'nama' => $val->employeemaster->name, 'nominal' => number_format($val->amount, 0, '.', ',')];
    endforeach;
    $paper = array(
      'panjang' => 35,
      'baris' => 31,
      'spasi' => 2,
      'column_width' => [
        'header' => [35, 0],
        'table' => [3, 21, 11],
        'footer' => [18, 17]
      ],
      'header' => [
        'left' => [
          strtoupper($cooperationDefault['nickname']),
          $cooperationDefault['address'],
          'KASBON SUPIR',
          'Nama: ' . $data->employee->name,
          'Gaji Bln: ' . $data->monthlysalary->name,
          'Tgl Kasbon: ' . $this->convertToDate($data->created_at),
        ],
      ],
      'footer' => [
        ['align' => 'right', 'data' => ['Total', number_format($total, 0, '.', ',')]],
        ['align' => 'center', 'data' => ['Mengetahui', 'Mengetahui']],
        ['align' => 'center', 'data' => ['', '']],
        ['align' => 'center', 'data' => ['', '']],
        ['align' => 'center', 'data' => ['', '']],
        ['align' => 'center', 'data' => [Auth::user()->name, $data->employee->name]],
      ],
      'table' => [
        'header' => ['No', 'Keterangan', 'Nominal'],
        'produk' => $item,
        'footer' => array(
          'catatan' => ''
        )
      ]
    );
    $paper['footer'][] = [
      'align' => 'center', 'data' => [
        str_pad('_', strlen(Auth::user()->name) + 2, '_', STR_PAD_RIGHT),
        str_pad('_', strlen($data->employee->name) + 2, '_', STR_PAD_RIGHT)
      ]
    ];
    $printed = new ContinousPaper($paper);
    $result .= $printed->output() . "\n";
    return response($result, 200)->header('Content-Type', 'text/plain');
//    return view('backend.accounting.monthlysalarydetail.print', compact('config', 'page_breadcrumbs', 'data', 'profile'));
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
            'description' => "Pembayaran gaji karyawaan $employee->name bulan " . $data->monthlysalary->name
          ]);

          Journal::create([
            'coa_id' => 38,
            'date_journal' => $this->getEndOfMonthByMonthYear($data->monthlysalary->name),
            'debit' => $data->monthlysalarydetailemployees_sum_amount,
            'kredit' => 0,
            'table_ref' => 'monthlysalarydetail',
            'code_ref' => $id,
            'description' => "Beban gaji karyawaan $employee->name dengan $coa->name bulan " . $data->monthlysalary->name
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

  public function destroy($id)
  {


    try {
      DB::transaction(function () use ($id) {
        $data = MonthlySalaryDetail::find($id);
        $data->update([
          'status' => '0',
          'coa_id' => NULL
        ]);

        Journal::where([
          ['table_ref', 'monthlysalarydetail'],
          ['code_ref', $data->id]
        ])->delete();
      });

      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been deleted',
      ]);
    } catch (\Throwable $throwable) {
      $response = response()->json([
        'status' => 'error',
        'message' => 'Data cannot be deleted',
      ]);
    }

    return $response;
  }

  public function datatabledetail($id)
  {
    $data = MonthlySalaryDetailEmployee::with(['employeemaster'])->where('monthly_salary_detail_id', $id);

    return Datatables::of($data)->make(true);
  }
}
