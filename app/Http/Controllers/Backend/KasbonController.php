<?php


namespace App\Http\Controllers\Backend;

use App\Helpers\ContinousPaper;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\ConfigCoa;
use App\Models\Driver;
use App\Models\Journal;
use App\Models\Kasbon;
use App\Models\Opname;
use App\Models\Setting;
use App\Traits\CarbonTrait;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class KasbonController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:kasbon-list|kasbon-create|kasbon-edit|kasbon-delete', ['only' => ['index']]);
    $this->middleware('permission:kasbon-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:kasbon-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:kasbon-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "List Kasbon";
    $config['page_description'] = "Daftar List Kasbon";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Kasbon"],
    ];

    if ($request->ajax()) {
      $data = Kasbon::with('driver:id,name');
      return DataTables::of($data)
        ->addColumn('action', function ($row) {
          return '
              <div class="dropdown">
                  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fas fa-eye"></i>
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a href="kasbon/' . $row->id . '" class="dropdown-item">Detail Kasbon</a>
                  </div>
              </div>
            ';
        })
        ->addIndexColumn()
        ->make(true);
    }
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'kasbon')->sole();
    return view('backend.invoice.kasbon.index', compact('config', 'page_breadcrumbs', 'selectCoa'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'driver_id' => 'required|integer',
      'coa_id' => 'required|integer',
      'amount' => 'required|integer',
      'memo' => 'required|string',
    ]);

    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $driver = Driver::findOrFail($request->driver_id);
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
          $kasbon = Kasbon::create([
            'driver_id' => $request->input('driver_id'),
            'coa_id' => $request->input('coa_id'),
            'amount' => $request->input('amount'),
            'memo' => $request->input('memo'),
          ]);
          Journal::create([
            'coa_id' => 7,
            'date_journal' => $this->dateNow(),
            'debit' => $request->input('amount'),
            'kredit' => 0,
            'table_ref' => 'kasbon',
            'code_ref' => $kasbon->id,
            'description' => "Supir $driver->name melakukan kasbon dengan $coa->name"
          ]);
          Journal::create([
            'coa_id' => $request->input('coa_id'),
            'date_journal' => $this->dateNow(),
            'debit' => 0,
            'kredit' => $request->input('amount'),
            'table_ref' => 'kasbon',
            'code_ref' => $kasbon->id,
            'description' => "Pengeluaran untuk kasbon $driver->name"
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
    $config['page_title'] = "Detail Kasbon";
    $config['print_url'] = "/backend/kasbon/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/kasbon', 'title' => "Kasbon"],
      ['page' => '#', 'title' => "Detail Kasbon"],
    ];
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    $data = Kasbon::with('driver')->findOrFail($id);
    return view('backend.invoice.kasbon.show', compact('config', 'page_breadcrumbs', 'data', 'profile'));
  }

  public function print($id){
    $config['page_title'] = "Detail Kasbon";
    $config['print_url'] = "/backend/kasbon/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/kasbon', 'title' => "Kasbon"],
      ['page' => '#', 'title' => "Detail Kasbon"],
    ];
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    $data = Kasbon::with('driver')->findOrFail($id);
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
          'KASBON SUPIR',
          'Nama: '. $data->driver->name,
          'Tgl Kasbon: '. $this->convertToDate($data->created_at),
        ],
      ],
      'footer' => [
        ['align' => 'center', 'data' => ['Mengetahui', 'Mengetahui']],
        ['align' => 'center', 'data' => ['', '']],
        ['align' => 'center', 'data' => ['', '']],
        ['align' => 'center', 'data' => [Auth::user()->name, $data->driver->name]],
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
//      'align' => 'center', 'data' => [str_pad('_', strlen(Auth::user()->name) + 2, '_', STR_PAD_RIGHT), str_pad('_', strlen($data->driver->name) + 2, '_', STR_PAD_RIGHT)]
//    ];
    $printed = new ContinousPaper($paper);
    $result .= $printed->output() . "\n";
    return response($result, 200)->header('Content-Type', 'text/plain');
//    return view('backend.invoice.kasbon.print', compact('config', 'page_breadcrumbs', 'data', 'profile'));
  }

}
