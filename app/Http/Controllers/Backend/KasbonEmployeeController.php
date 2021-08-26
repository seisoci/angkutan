<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ContinousPaper;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\ConfigCoa;
use App\Models\Driver;
use App\Models\Journal;
use App\Models\Kasbon;
use App\Models\KasbonEmployee;
use App\Models\Setting;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class KasbonEmployeeController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:kasbonemployees-list|kasbonemployees-create|kasbonemployees-edit|kasbonemployees-delete', ['only' => ['index']]);
    $this->middleware('permission:kasbonemployees-create', ['only' => ['create', 'store']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "List Kasbon Karyawaan";
    $config['page_description'] = "Daftar List Kasbon Karyawaan";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Kasbon Karyawaan"],
    ];

    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'kasbonemployees')->sole();

    $saldoGroup = collect($selectCoa->coa)->map(function ($coa) {
      return [
        'name'  => $coa->name ?? NULL,
        'balance' => DB::table('journals')
            ->select(DB::raw('
          IF(`coas`.`normal_balance` = "Db", (SUM(`journals`.`debit`) - SUM(`journals`.`kredit`)),
          (SUM(`journals`.`kredit`) - SUM(`journals`.`debit`))) AS `saldo`
          '))
            ->leftJoin('coas', 'coas.id', '=', 'journals.coa_id')
            ->where('journals.coa_id', $coa->id)
            ->groupBy('journals.coa_id')
            ->first()->saldo ?? 0,
      ];
    });

    if ($request->ajax()) {
      $data = KasbonEmployee::with('employee:id,name');
      return DataTables::of($data)
        ->addColumn('action', function ($row) {
          return '
              <div class="dropdown">
                  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fas fa-eye"></i>
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a href="kasbonemployees/' . $row->id . '" class="dropdown-item">Detail Kasbon</a>
                  </div>
              </div>
            ';
        })
        ->addIndexColumn()
        ->make(true);
    }

    return view('backend.accounting.kasbonemployee.index', compact('config', 'page_breadcrumbs', 'selectCoa', 'saldoGroup'));
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
          $kasbon = KasbonEmployee::create([
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
            'table_ref' => 'kasbonemployees',
            'code_ref' => $kasbon->id,
            'description' => "Karyawaan $employee->name melakukan kasbon dengan $coa->name"
          ]);
          Journal::create([
            'coa_id' => $request->input('coa_id'),
            'date_journal' => $this->dateNow(),
            'debit' => 0,
            'kredit' => $request->input('amount'),
            'table_ref' => 'kasbonemployees',
            'code_ref' => $kasbon->id,
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

  public function show($id){
    $config['page_title'] = "Detail Kasbon Karyawaan";
    $config['print_url'] = "/backend/kasbonemployees/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/kasbonemployees', 'title' => "Kasbon Karyawaan"],
      ['page' => '#', 'title' => "Detail Kasbon Karyawaan"],
    ];
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    $data = KasbonEmployee::with('employee')->findOrFail($id);
    return view('backend.accounting.kasbonemployee.show', compact('config', 'page_breadcrumbs', 'data', 'profile'));
  }

  public function print($id){
    $config['page_title'] = "Detail Kasbon Karyawaan";
    $config['print_url'] = "/backend/kasbonemployees/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/kasbonemployees', 'title' => "Kasbon Karyawaan"],
      ['page' => '#', 'title' => "Detail Kasbon Karyawaan"],
    ];
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    $data = KasbonEmployee::with('employee')->findOrFail($id);
    $result = '';
    $item[] = ['no' => 1, 'nama' => $data->memo, 'nominal' => number_format($data->amount, 0, '.', ',')];
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
          strtoupper($profile['name']),
          $profile['address'],
          'KASBON KARYAWAAN',
          'Nama: '. $data->employee->name,
          'Tgl Kasbon: '. $this->convertToDate($data->created_at),
        ],
      ],
      'footer' => [
        ['align' => 'center', 'data' => ['Mengetahui', 'Mengetahui']],
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
//    $paper['footer'][] = [
//      'align' => 'center', 'data' => [str_pad('_', strlen(Auth::user()->name) + 2, '_', STR_PAD_RIGHT), str_pad('_', strlen($data->employee->name) + 2, '_', STR_PAD_RIGHT)]
//    ];
    $printed = new ContinousPaper($paper);
    $result .= $printed->output() . "\n";
    return response($result, 200)->header('Content-Type', 'text/plain');
//    return view('backend.accounting.kasbonemployee.print', compact('config', 'page_breadcrumbs', 'data', 'profile'));
  }

}
