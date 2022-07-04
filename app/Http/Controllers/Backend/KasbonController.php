<?php


namespace App\Http\Controllers\Backend;

use App\Helpers\ContinousPaper;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\ConfigCoa;
use App\Models\Cooperation;
use App\Models\Driver;
use App\Models\Journal;
use App\Models\Kasbon;
use App\Models\PaymentKasbon;
use App\Traits\CarbonTrait;
use Carbon\Carbon;
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

    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'kasbon')->sole();

    $saldoGroup = collect($selectCoa->coa)->map(function ($coa) {
      return [
        'name' => $coa->name ?? NULL,
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
      $data = Kasbon::selectRaw("
      `kasbons`.*,
      `drivers`.`name` as `nama_supir`,
      `drivers`.`id` as `driver_id`
      ")
        ->leftJoin('drivers', 'drivers.id', '=', 'kasbons.driver_id');

      return DataTables::of($data)
        ->addColumn('action', function ($row) {
          return '
              <div class="dropdown">
                  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fas fa-eye"></i>
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a href="kasbon/' . $row->driver_id . '" class="dropdown-item">Detail Seluruh Kasbon</a>
                  </div>
              </div>
            ';
        })
        ->addIndexColumn()
        ->make(true);
    }

    return view('backend.invoice.kasbon.index', compact('config', 'page_breadcrumbs', 'selectCoa', 'saldoGroup'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'driver_id' => 'required|integer',
      'coa_id' => 'required|integer',
      'amount' => 'required|integer',
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

        $data = Kasbon::firstOrNew([
          'driver_id' => $request['driver_id']
        ]);

        $paymentKasbon = PaymentKasbon::create([
          'coa_id' => $request['coa_id'],
          'driver_id' => $request['driver_id'],
          'date_payment' => $request['date_payment'],
          'type' => $request['type'],
          'payment' => $request['amount'],
          'description' => $request['description'],
        ]);

        if ($request['type'] == 'hutang') {
          $data->amount = ($data->exists ? $data['amount'] : 0) + $request['amount'];
          if (($checksaldo->saldo ?? FALSE) && $request->amount <= $checksaldo->saldo) {
            Journal::create([
              'coa_id' => 7,
              'date_journal' => $request['date_payment'],
              'debit' => $request['amount'],
              'kredit' => 0,
              'table_ref' => 'kasbon',
              'code_ref' => $paymentKasbon->id,
              'description' => "Supir $driver->name melakukan kasbon dengan $coa->name"
            ]);
            Journal::create([
              'coa_id' => $request->input('coa_id'),
              'date_journal' => $request['date_payment'],
              'debit' => 0,
              'kredit' => $request['amount'],
              'table_ref' => 'kasbon',
              'code_ref' => $paymentKasbon->id,
              'description' => "Pengeluaran untuk kasbon $driver->name"
            ]);
            $response = response()->json([
              'status' => 'success',
              'message' => 'Data has been saved',
            ]);
            $data->save();
            DB::commit();
          } else {
            DB::rollBack();
            $response = response()->json([
              'status' => 'errors',
              'message' => "Saldo $coa->name tidak ada/kurang",
            ]);
          }
        } else {
          if ((($data->exists ? $data['amount'] : 0) - $request['amount']) >= 0) {
            $data->amount = ($data->exists ? $data['amount'] : 0) - $request['amount'];

            Journal::create([
              'coa_id' => $request['coa_id'],
              'date_journal' => $request['date_payment'],
              'debit' => $request['amount'],
              'kredit' => 0,
              'table_ref' => 'kasbon',
              'code_ref' => $paymentKasbon->id,
              'description' => "Penambahan saldo dari kasbon supir $driver->name"
            ]);

            Journal::create([
              'coa_id' => 7,
              'date_journal' => $request['date_payment'],
              'debit' => 0,
              'kredit' => $request['amount'],
              'table_ref' => 'kasbon',
              'code_ref' => $paymentKasbon->id,
              'description' => "Pembayaran kasbon supir $driver->name ke $coa->name"
            ]);

            $response = response()->json([
              'status' => 'success',
              'message' => 'Data has been saved',
            ]);
            $data->save();
            DB::commit();

          } else {
            return response()->json([
              'status' => 'errors',
              'message' => "Pembayaran melebihi hutang",
            ]);
          }
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

  public function show(Request $request, $id)
  {
    $config['page_title'] = "Detail Kasbon";
    $config['page_description'] = "Detail List Kasbon";
    $config['print_url'] = route('backend.kasbon.print', $id);
    $config['print_dotmatrix_url'] = route('backend.kasbon.print-dotmatrix', $id);

    $page_breadcrumbs = [
      ['page' => '/backend/kasbon', 'title' => "Kasbon"],
      ['page' => '#', 'title' => "Detail Kasbon"],
    ];
    $data = Kasbon::with('driver')
      ->where('driver_id', $id)
      ->sole();

    return view('backend.invoice.kasbon.show', compact('config', 'page_breadcrumbs', 'data', 'id'));
  }

  public function print($id)
  {
    $config['page_title'] = "Detail Kasbon";
    $page_breadcrumbs = [
      ['page' => '/backend/kasbon', 'title' => "Kasbon"],
      ['page' => '#', 'title' => "Detail Kasbon"],
    ];
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $item = PaymentKasbon::with('driver')->findOrFail($id);

    $data[] = $item;
    $driverName = $item['driver']['name'];
    $totalKasbon = $item['payment'];

    return view('backend.invoice.kasbon.print', compact('config', 'page_breadcrumbs', 'data', 'driverName', 'cooperationDefault', 'totalKasbon'));
  }

  public function printMultiple(Request $request)
  {
    $config['page_title'] = "Detail Kasbon";
    $page_breadcrumbs = [
      ['page' => '/backend/kasbon', 'title' => "Kasbon"],
      ['page' => '#', 'title' => "Detail Kasbon"],
    ];

    $cooperationDefault = Cooperation::where('default', '1')->first();
    if (!$request['payment_kasbon_id']) {
      return response()->json([
        'status' => 'error',
        'message' => 'Pilih Data Kasbon Terlebih Dahulu',
      ]);
    }
    $split = explode(",", $request['payment_kasbon_id']);
    $data = PaymentKasbon::with('driver')
      ->whereIn('id', $split)
      ->orderBy('date_payment', 'asc')
      ->get();

    $totalKasbon = 0;
    foreach ($data as $item):
      if ($item['type'] == 'hutang') {
        $totalKasbon += $item['payment'];
      } else {
        $totalKasbon -= $item['payment'];
      }
    endforeach;

    $driverName = $data[0]['driver']['name'];

    return view('backend.invoice.kasbon.print', compact('config', 'page_breadcrumbs', 'data', 'cooperationDefault', 'driverName', 'totalKasbon'));
  }

  public function printDotMatrix($id)
  {
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = PaymentKasbon::with('driver')->findOrFail($id);
    $result = '';
    $item[] = [
      'no' => 1,
      'nama' => $data->description,
      'nominal' => number_format($data->payment, 0, '.', ',')
    ];

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
          'Nama: ' . $data->driver->name,
          'Tgl Kasbon: ' . $data->date_payment,
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
    $paper['footer'][] = [
      'align' => 'center', 'data' => [str_pad('_', strlen(Auth::user()->name) + 2, '_', STR_PAD_RIGHT), str_pad('_', strlen($data->driver->name) + 2, '_', STR_PAD_RIGHT)]
    ];
    $printed = new ContinousPaper($paper);
    $result .= $printed->output() . "\n";
    return response($result, 200)->header('Content-Type', 'text/plain');
  }

  public function printDotMatrixMultiple(Request $request)
  {
    $cooperationDefault = Cooperation::where('default', '1')->first();
    if (!$request['data']) {
      return response()->json([
        'status' => 'error',
        'message' => 'Pilih Data Kasbon Terlebih Dahulu',
      ]);
    }
    $data = PaymentKasbon::with('driver')
      ->whereIn('id', $request['data'])
      ->orderBy('date_payment', 'asc')
      ->get();
    $result = '';
    foreach ($data as $key => $itemKasbon):
      $tgl = Carbon::parse($itemKasbon->date_payment)->isoFormat('DD MMM YYYY');
      $item[] = ['no' => ($key + 1), 'nama' => ucfirst($itemKasbon->type) . " " . $tgl, 'nominal' => number_format($itemKasbon->payment, 0, '.', ',')];
    endforeach;
    $paper = array(
      'panjang' => 35,
      'baris' => 31,
      'spasi' => 2,
      'column_width' => [
        'header' => [35, 0],
        'table' => [2, 23, 10],
        'footer' => [18, 17]
      ],
      'header' => [
        'left' => [
          strtoupper($cooperationDefault['nickname']),
          $cooperationDefault['address'],
          'KASBON SUPIR',
          'Nama: ' . $data[0]->driver->name,
        ],
      ],
      'footer' => [
        ['align' => 'center', 'data' => ['Mengetahui', 'Mengetahui']],
        ['align' => 'center', 'data' => ['', '']],
        ['align' => 'center', 'data' => ['', '']],
        ['align' => 'center', 'data' => [Auth::user()->name, $data[0]->driver->name]],
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
  }

  public function datatableShow($id)
  {
    $data = PaymentKasbon::leftJoin('drivers', 'drivers.id', '=', 'payment_kasbons.driver_id')
      ->where('payment_kasbons.driver_id', $id)
      ->selectRaw("`payment_kasbons`.*, `drivers`.`name` as `nama_supir`");
    return DataTables::of($data)
      ->addColumn('action', function ($row) use ($id) {
        return '
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-eye"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                  <a href="#" class="dropdown-item btnPrint" data-id="' . $row->id . '">Print DotMatrix</a>
                  <a target="_blank" href="' . route('backend.kasbon.print', $row->id) . '" class="dropdown-item">Print Biasa</a>
                  <a href="#" data-toggle="modal" data-target="#modalDelete" data-id="' . $row->id . '" class="dropdown-item">Delete</a>
                </div>
            </div>
          ';
      })
      ->addIndexColumn()
      ->make(true);
  }

  public function destroy($id)
  {
    try {
      DB::beginTransaction();
      $data = PaymentKasbon::find($id);

      $kasbon = Kasbon::firstOrNew([
        'driver_id' => $data['driver_id']
      ]);

      if ($data['type'] == 'hutang') {
        $kasbon->amount = ($kasbon->exists ? $kasbon['amount'] : 0) - $data['payment'];
        if ($kasbon->amount < 0) {
          DB::rollBack();
          return response()->json([
            'status' => 'error',
            'message' => 'Kasbon tidak boleh negative',
          ]);
        }
      } else {
        $kasbon->amount = ($kasbon->exists ? $kasbon['amount'] : 0) + $data['payment'];
      }
      $kasbon->save();
      $data->delete();
      $journal = Journal::where('table_ref', 'kasbon')->where('code_ref', $id)->delete();;
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been deleted',
      ]);

      DB::commit();
    } catch (\Throwable $throw) {
      DB::rollBack();
      $response = response()->json([
        'status' => 'error',
        'message' => 'Data cannot be deleted',
      ]);
    }

    return $response;
  }

}
