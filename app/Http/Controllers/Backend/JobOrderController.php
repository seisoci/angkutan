<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ContinousPaper;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\ConfigCoa;
use App\Models\Costumer;
use App\Models\Driver;
use App\Models\JobOrder;
use App\Models\Journal;
use App\Models\OperationalExpense;
use App\Models\Prefix;
use App\Models\RoadMoney;
use App\Models\Route;
use App\Models\Setting;
use App\Models\Transport;
use App\Models\TypeCapacity;
use Carbon\Carbon;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class JobOrderController extends Controller
{

  function __construct()
  {
    $this->middleware('permission:joborders-list|joborders-create|joborders-edit|joborders-delete', ['only' => ['index']]);
    $this->middleware('permission:joborders-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:joborders-edit', ['only' => ['edit', 'update']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Job Order";
    $config['page_description'] = "Daftar List Job Order";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Job Order"],
    ];

    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'joborders')->sole();

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
    $status_document = $request->status_document;
    if ($request->ajax()) {
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
        ->when($status_document, function ($query, $status_document) {
          if ($status_document === "zero") {
            return $query->where('status_document', "0");
          } else {
            return $query->where('status_document', $status_document);
          }
        })
        ->when($status_salary, function ($query, $status_salary) {
          return $query->where('status_salary', $status_salary);
        });
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('details_url', function (JobOrder $jobOrder) {
          return route('backend.joborders.datatabledetail', $jobOrder->id);
        })
        ->addColumn('action', function ($row) {
          $btnEdit = '';
          $btnEditDocument = '';
          $btnShowTonase = '';
          $btnTransfer = '';
          if ($row->status_cargo != 'selesai' && $row->status_cargo != 'batal') {
            $btnEdit = '
            <a href="#" data-toggle="modal" data-target="#modalEdit" data-id="' . $row->id . '" data-type_payload="' . $row->type_payload . '" data-payload="' . $row->payload . '"  data-status_cargo="' . $row->status_cargo . '" data-date_end="' . $row->date_end . '" class="edit dropdown-item">Edit</a>';
          }
          if ($row->status_document != 1 && $row->status_cargo === 'selesai') {
            $btnEditDocument = '
            <a href="#" data-toggle="modal" data-target="#modalEditDocument" data-id="' . $row->id . '" class="edit dropdown-item">Edit Dokumen</a>';
          }
          if ($row->status_cargo == 'selesai' && $row->status_document == 1 && !$row->invoice_costumer_id && in_array(Auth::user()->roles[0]->name, ['super-admin', 'admin', 'akunting'])) {
            $btnShowTonase = '
            <a href="#" data-toggle="modal" data-target="#modalEditTonase" data-id="' . $row->id . '" data-type_payload="' . $row->type_payload . '" data-payload="' . $row->payload . '" data-no_sj="' . $row->no_sj . '"   data-no_shipment="' . $row->no_shipment . '"  class="edit dropdown-item">Input Tonase</a>';
          }
          if ($row->status_cargo == 'transfer') {
            $btnTransfer = '
            <a href="#" data-toggle="modal" data-target="#modalEditRoadMoney" data-id="' . $row->id . '"  class="edit dropdown-item">Input Uang Jalan</a>';
          }
          $actionBtn = '<div class="btn-group-vertical" role="group" aria-label="Vertical button group">
            <div class="btn-group" role="group">
                <button id="btnGroupVerticalDrop1" type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="fas fa-edit"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="btnGroupVerticalDrop1">
              <a href="joborders/' . $row->id . '" class="dropdown-item">Show Detail</a>
              ' . $btnTransfer . $btnEdit . $btnEditDocument . $btnShowTonase . '
                </div>
            </div>
          </div>
          ';
          return $actionBtn;
        })
        ->make(true);
    }
    return view('backend.operational.joborders.index', compact('config', 'page_breadcrumbs', 'saldoGroup'));
  }

  public function create()
  {
    $config['page_title'] = "Create Job Order";
    $page_breadcrumbs = [
      ['page' => '/backend/joborders', 'title' => "List Job Order"],
      ['page' => '#', 'title' => "Create Job Order"],
    ];
    $sparepart = Setting::where('name', 'potongan sparepart')->first();
    $gaji = Setting::where('name', 'gaji supir')->first();
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'joborders')->sole();
    return view('backend.operational.joborders.create', compact('config', 'page_breadcrumbs', 'sparepart', 'gaji', 'selectCoa'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'prefix' => 'required|integer',
      'type' => 'required|in:self,ldo',
      'transport_id' => 'required',
      'driver_id' => 'required',
      'costumer_id' => 'required|integer',
      'route_from' => 'required|integer',
      'route_to' => 'required|integer',
      'cargo_id' => 'required|integer',
      'basic_price' => 'required|gt:0',
      'road_money' => 'required',
    ]);

    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $jo_date = Carbon::parse()->timezone('Asia/Jakarta')->format('Ymd');
        $invoice_db = JobOrder::select(DB::raw('MAX(SUBSTRING_INDEX(num_bill, "-", -1)+1) AS `num`'))->first();
        $jo_num = $invoice_db['num'] != NULL ? $invoice_db['num'] : 1;
        $type_capacity = TypeCapacity::findOrFail($request->type_capacity);
        $qsparepart = Setting::where('name', 'potongan sparepart')->first();
        $qsalary = Setting::where('name', 'gaji supir')->first();
        $prefix = Prefix::findOrFail($request->prefix);
        $costumer = Costumer::findOrFail($request->costumer_id);
        $routefrom = Route::findOrFail($request->route_from);
        $routeto = Route::findOrFail($request->route_to);
        $coa = Coa::findOrFail($request->coa_id);
        $roadMoney = 0;
        $checksaldo = DB::table('journals')
          ->select(DB::raw('
          IF(`coas`.`normal_balance` = "Db", (SUM(`journals`.`debit`) - SUM(`journals`.`kredit`)),
          (SUM(`journals`.`kredit`) - SUM(`journals`.`debit`))) AS `saldo`
          '))
          ->leftJoin('coas', 'coas.id', '=', 'journals.coa_id')
          ->where('journals.coa_id', $request->coa_id)
          ->groupBy('journals.coa_id')
          ->first();

        if (($checksaldo->saldo ?? FALSE) && $request->road_money <= $checksaldo->saldo) {
          $data = new JobOrder();
          if ($request->type === 'self') {
            //CALCULATE
            $basicPrice = $request->basic_price;
            $payload = $request->payload ?? 1;
            $sumPayload = $basicPrice * $payload;
            //MODEL DB
            $data->date_begin = $request->date_begin;
            $data->type = $request->type;
            $data->num_bill = $jo_date . "-" . $jo_num;
            $data->prefix = $prefix->name;
            $data->another_expedition_id = $request->another_expedition_id ?? NULL;
            $data->driver_id = $request->driver_id;
            $data->transport_id = $request->transport_id;
            $data->costumer_id = $request->costumer_id;
            $data->cargo_id = $request->cargo_id;
            $data->route_from = $request->route_from;
            $data->route_to = $request->route_to;
            $data->type_capacity = $type_capacity->name;
            $data->type_payload = $request->type_payload;
            $data->payload = $request->payload ?? 1;
            $data->basic_price = $request->basic_price;
            $data->road_money = $request->road_money;
            $data->cut_sparepart_percent = $qsparepart->value;
            $data->salary_percent = $qsalary->value;
            $data->tax_percent = $request->tax_percent ?? 0;
            $data->fee_thanks = $request->fee_thanks ?? 0;
            $data->invoice_bill = $sumPayload;
            $data->description = $request->description;
            $data->save();

          } elseif ($request->type == 'ldo') {
            if (is_numeric($request->driver_id)) {
              $driverId = Driver::findOrFail($request->driver_id)->id;
            } else {
              $driverId = Driver::create([
                'another_expedition_id' => $request->another_expedition_id,
                'name' => $request->driver_id,
                'status' => 'active'
              ])->id;
            }

            if (is_numeric($request->transport_id)) {
              $transportId = Transport::findOrFail($request->transport_id)->id;
            } else {
              $transportId = Transport::create([
                'another_expedition_id' => $request->another_expedition_id,
                'num_pol' => $request->transport_id,
                'type_car' => 'engkel'
              ])->id;
            }

            //CALCULATE
            $basicPrice = $request->basic_price;
            $payload = $request->payload ?? 1;
            $sumPayload = $basicPrice * $payload;
            //MODEL DB
            $data->date_begin = $request->date_begin;
            $data->type = $request->type;
            $data->num_bill = $jo_date . "-" . $jo_num;
            $data->prefix = $prefix->name;
            $data->another_expedition_id = $request->another_expedition_id ?? NULL;
            $data->driver_id = $driverId;
            $data->transport_id = $transportId;
            $data->costumer_id = $request->costumer_id;
            $data->cargo_id = $request->cargo_id;
            $data->route_from = $request->route_from;
            $data->route_to = $request->route_to;
            $data->type_capacity = $type_capacity->name;
            $data->type_payload = $request->type_payload;
            $data->payload = $request->payload ?? 1;
            $data->basic_price = $request->basic_price;
            $data->basic_price_ldo = $request->basic_price_ldo;
            $data->road_money = $request->road_money;
            $data->tax_percent = $request->tax_percent ?? 0;
            $data->fee_thanks = $request->fee_thanks ?? 0;
            $data->invoice_bill = $sumPayload;
            $data->description = $request->description;
            $data->save();
            $roadMoney = $request->road_money;
          }

          if (is_numeric($request->transport_id)) {
            $transportId = Transport::findOrFail($request->transport_id)->num_pol;
          } else {
            $transportId = $request->transport_id;
          }

          /*          Journal::create([
                      'coa_id' => $request->input('coa_id'),
                      'date_journal' => $request->input('date_begin'),
                      'debit' => 0,
                      'kredit' => $roadMoney,
                      'table_ref' => 'joborders',
                      'code_ref' => $data->id,
                      'description' => "Pengurangan saldo untuk uang jalan $costumer->name dari $routefrom->name ke $routeto->name dengan No. Pol: $transportId"
                    ]);

                    Journal::create([
                      'coa_id' => 50,
                      'date_journal' => $request->input('date_begin'),
                      'debit' => $roadMoney,
                      'kredit' => 0,
                      'table_ref' => 'joborders',
                      'code_ref' => $data->id,
                      'description' => "Beban operasional uang jalan $costumer->name dari $routefrom->name ke $routeto->name dengan No. Pol: $transportId"
                    ]);*/

          DB::commit();
          $response = response()->json([
            'status' => 'success',
            'message' => 'Data has been saved',
            'redirect' => '/backend/joborders',
          ]);
        } else {
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

  public function show($id)
  {
    $config['page_title'] = "Detail Job Order";
    $config['print_url'] = "/backend/joborders/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/drivers', 'title' => "List Job Order"],
      ['page' => '#', 'title' => "Detail Job Order"],
    ];
    $data = JobOrder::with(['anotherexpedition', 'driver', 'costumer.cooperation', 'cargo', 'transport', 'routefrom', 'routeto', 'operationalexpense.expense'])->findOrFail($id);
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'joborders')->sole();
    return view('backend.operational.joborders.show', compact('config', 'page_breadcrumbs', 'data', 'selectCoa'));
  }

  public function print($id)
  {
    $config['page_title'] = "Detail Job Order";
    $config['print_url'] = "/backend/joborders/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/joborders', 'title' => "List Job Order"],
      ['page' => '#', 'title' => "Detail Job Order"],
    ];
    $item = array();
    $data = JobOrder::with(['anotherexpedition', 'driver', 'costumer.cooperation', 'cargo', 'transport', 'routefrom', 'routeto', 'operationalexpense.expense'])
      ->withSum('operationalexpense', 'amount')
      ->findOrFail($id);
    $totalRoadMoney = ($data->road_money ?? 0) - ($data->road_money_prev) + ($data->operationalexpense_sum_amount ?? 0) + ($data->road_money_extra ?? 0);
    $item [] = [
      'no' => 1,
      'nama' => 'Uang jalan standart',
      'nominal' => number_format($data->road_money, 0, '.', ',')
    ];
    if ($data->road_money_prev > 0) {
      $item [] = [
        'no' => count($item) + 1,
        'nama' => 'Pot. uang jalan telah diambil sebelumnya',
        'nominal' => '-' . number_format($data->road_money_prev, 0, '.', ',')
      ];
    }
    if ($data->road_money_extra > 0) {
      $item [] = [
        'no' => count($item) + 1,
        'nama' => 'Uang Jalan Tambahan',
        'nominal' => number_format($data->road_money_extra, 0, '.', ',')
      ];
    }

    $operationalExpense = OperationalExpense::with('expense')->where('job_order_id', $id)->get();
    foreach ($operationalExpense as $val):
      $item[] = ['no' => count($item) + 1, 'nama' => $val->expense->name, 'nominal' => number_format($val->amount, 0, '.', ',')];
    endforeach;
    $item[] = ['no' => '------------------------------------'];
    $item[] = ['1' => '', 'name' => 'Total', 'nominal' => number_format($totalRoadMoney, 0, '.', ',')];
    $result = '';
    $paper = array(
      'panjang' => 35,
      'baris' => 31,
      'spasi' => 2,
      'column_width' => [
        'header' => [35, 0],
        'table' => [3, 22, 10],
        'footer' => [18, 17]
      ],
      'header' => [
        'left' => [
          strtoupper($data->costumer->cooperation->nickname),
          'JOB ORDER',
          'NO. JO: ' . $data->num_prefix,
          'KODE JOB ORDER : ' . $data->num_prefix,
          'TGL. MUAT: ' . $data->date_begin,
          'SUPPLIER : ' . $data->costumer->name,
          'DARI : ' . $data->routefrom->name,
          'TUJUAN : ' . $data->routeto->name,
        ],
      ],
      'footer' => [
        ['align' => 'center', 'data' => ['Mengetahui', 'Mengetahui']],
        ['align' => 'center', 'data' => ['', '']],
        ['align' => 'center', 'data' => ['', '']],
        ['align' => 'center', 'data' => [Auth::user()->name, $data->driver->name]],
        ['align' => 'center', 'data' => ['', '']],
      ],
      'table' => [
        'header' => ['No', 'Keterangan', 'Nominal'],
        'produk' => $item,
        'footer' => array(
          'asd' => 'KET: ' . $data->description ?? '',
          'catatan' => 'KET: ' . $data->description ?? '',
        )
      ]
    );
    $printed = new ContinousPaper($paper);
    $result .= $printed->output() . "\n";
    return response($result, 200)->header('Content-Type', 'text/plain');
  }

  public function update(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'status_cargo' => 'string',
      'date_end' => 'date_format:Y-m-d|required_if:status_cargo,selesai',
    ]);
    if ($validator->passes()) {
      $response = response()->json([
        'status' => 'error',
        'message' => 'Failed updated',
      ]);
      $data = JobOrder::with('transport', 'routeto', 'routeto', 'operationalexpense')->withSum('operationalexpense', 'amount')->find($id);

      if ($request->no_sj || $request->shipement) {
        $data->update($request->all());
        return response()->json([
          'status' => 'success',
          'message' => 'Data has been updated',
        ]);
      }

      switch ($request->status_cargo) {
        case 'transfer':
          $data->update(['status_cargo' => 'transfer']);
          $response = response()->json([
            'status' => 'success',
            'message' => 'Data has been updated',
          ]);
          break;
        case 'batal':
          $data->update($request->except('date_end'));
          foreach ($data->operationalexpense as $item):
            Journal::where('table_ref', 'operationalexpense')->where('code_ref', $item->id)->delete();
          endforeach;
          Journal::where('table_ref', 'joborders')->where('code_ref', $data->id)->delete();
          $response = response()->json([
            'status' => 'success',
            'message' => 'Data has been updated',
          ]);
          break;
        case 'selesai':
          Journal::create([
            'coa_id' => 43,
            'date_journal' => $data->date_begin,
            'debit' => $data->total_basic_price,
            'kredit' => 0,
            'table_ref' => 'joborders',
            'code_ref' => $data->id,
            'description' => "Penambahan piutang usaha dari joborder $data->prefix" . "-" . $data->num_bill . " dengan No. Pol: " . $data->transport->num_pol . " dari rute " . $data->routefrom->name . " tujuan " . $data->routeto->name,
          ]);

          Journal::create([
            'coa_id' => 52,
            'date_journal' => $data->date_begin,
            'debit' => 0,
            'kredit' => $data->total_basic_price,
            'table_ref' => 'joborders',
            'code_ref' => $data->id,
            'description' => "Penambahan Pendapatan joborder $data->prefix" . "-" . $data->num_bill . " dengan No. Pol: " . $data->transport->num_pol . " dari rute " . $data->routefrom->name . " tujuan " . $data->routeto->name,
          ]);
          $data->update($request->all());
          $response = response()->json([
            'status' => 'success',
            'message' => 'Data has been updated',
          ]);
          break;
      }

      if ($request->status_document) {
        $data->update(['status_document' => 1]);
        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been updated',
        ]);
      }

    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }

  public function select2costumers(Request $request)
  {
    $page = $request->page;
    $resultCount = 10;
    $offset = ($page - 1) * $resultCount;
    $q = $request->q;
    $data = DB::table('road_money')
      ->rightJoin('costumers', 'costumers.id', '=', 'road_money.costumer_id')
      ->where('costumers.name', 'LIKE', '%' . $q . '%')
      ->orderBy('costumers.name')
      ->skip($offset)
      ->take($resultCount)
      ->selectRaw('costumers.id AS id, costumers.name as text')
      ->groupBy('road_money.costumer_id')
      ->get();

    $count = DB::table('road_money')
      ->rightJoin('costumers', 'costumers.id', '=', 'road_money.costumer_id')
      ->where('costumers.name', 'LIKE', '%' . $q . '%')
      ->groupBy('road_money.costumer_id')
      ->get()
      ->count();

    $endCount = $offset + $resultCount;
    $morePages = $count > $endCount;

    $results = array(
      "results" => $data,
      "pagination" => array(
        "more" => $morePages
      )
    );

    return response()->json($results);
  }

  public function select2routefrom(Request $request)
  {
    $page = $request->page;
    $resultCount = 10;
    $offset = ($page - 1) * $resultCount;
    $costumer_id = !empty($request->costumer_id) || isset($request->costumer_id) ? $request->costumer_id : 0;
    $q = !empty($request->q) || isset($request->q) ? $request->q : NULL;
    $data = DB::table('road_money')
      ->rightJoin('routes', 'routes.id', '=', 'road_money.route_from')
      ->where('routes.name', 'LIKE', '%' . $q . '%')
      ->where('road_money.costumer_id', $costumer_id)
      ->orderBy('routes.name')
      ->skip($offset)
      ->take($resultCount)
      ->selectRaw('routes.id AS id, routes.name as text')
      ->groupBy('road_money.route_from')
      ->get();

    $count = DB::table('road_money')
      ->rightJoin('routes', 'routes.id', '=', 'road_money.route_from')
      ->where('routes.name', 'LIKE', '%' . $q . '%')
      ->where('road_money.costumer_id', $costumer_id)
      ->groupBy('road_money.route_from')
      ->get()
      ->count();

    $endCount = $offset + $resultCount;
    $morePages = $count > $endCount;

    $results = array(
      "results" => $data,
      "pagination" => array(
        "more" => $morePages
      )
    );

    return response()->json($results);
  }

  public function select2routeto(Request $request)
  {
    $page = $request->page;
    $resultCount = 10;
    $offset = ($page - 1) * $resultCount;
    $q = $request->q;
    $costumer_id = !empty($request->costumer_id) || isset($request->costumer_id) ? $request->costumer_id : 0;
    $route_from = !empty($request->route_from) || isset($request->route_from) ? $request->route_from : 0;
    $data = DB::table('road_money')
      ->rightJoin('routes', 'routes.id', '=', 'road_money.route_to')
      ->where('routes.name', 'LIKE', '%' . $q . '%')
      ->where('road_money.costumer_id', $costumer_id)
      ->where('road_money.route_from', $route_from)
      ->orderBy('routes.name')
      ->skip($offset)
      ->take($resultCount)
      ->selectRaw('routes.id AS id, routes.name as text')
      ->groupBy('road_money.route_to')
      ->get();

    $count = DB::table('road_money')
      ->rightJoin('routes', 'routes.id', '=', 'road_money.route_to')
      ->where('routes.name', 'LIKE', '%' . $q . '%')
      ->where('road_money.costumer_id', $costumer_id)
      ->where('road_money.route_from', $route_from)
      ->groupBy('road_money.route_to')
      ->get()
      ->count();

    $endCount = $offset + $resultCount;
    $morePages = $count > $endCount;

    $results = array(
      "results" => $data,
      "pagination" => array(
        "more" => $morePages
      )
    );

    return response()->json($results);
  }

  public function select2cargos(Request $request)
  {
    $page = $request->page;
    $resultCount = 10;
    $offset = ($page - 1) * $resultCount;
    $q = $request->q;
    $costumer_id = !empty($request->costumer_id) || isset($request->costumer_id) ? $request->costumer_id : 0;
    $route_from = !empty($request->route_from) || isset($request->route_from) ? $request->route_from : 0;
    $route_to = !empty($request->route_to) || isset($request->route_to) ? $request->route_to : 0;
    $data = DB::table('road_money')
      ->rightJoin('cargos', 'cargos.id', '=', 'road_money.cargo_id')
      ->where('cargos.name', 'LIKE', '%' . $q . '%')
      ->where('road_money.costumer_id', $costumer_id)
      ->where('road_money.route_from', $route_from)
      ->where('road_money.route_to', $route_to)
      ->orderBy('cargos.name')
      ->skip($offset)
      ->take($resultCount)
      ->selectRaw('cargos.id AS id, cargos.name as text')
      ->groupBy('road_money.cargo_id')
      ->get();

    $count = DB::table('road_money')
      ->rightJoin('cargos', 'cargos.id', '=', 'road_money.cargo_id')
      ->where('cargos.name', 'LIKE', '%' . $q . '%')
      ->where('road_money.costumer_id', $costumer_id)
      ->where('road_money.route_from', $route_from)
      ->where('road_money.route_to', $route_to)
      ->groupBy('road_money.cargo_id')
      ->get()
      ->count();

    $endCount = $offset + $resultCount;
    $morePages = $count > $endCount;

    $results = array(
      "results" => $data,
      "pagination" => array(
        "more" => $morePages
      )
    );

    return response()->json($results);
  }

  public function roadmoney(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'costumer_id' => 'integer',
      'route_from' => 'integer',
      'route_to' => 'integer',
      'cargo_id' => 'integer',
      'type_capacity_id' => 'integer',
      'transport_id' => 'nullable',
      'driver_id' => 'nullable',
    ]);
    if ($validator->passes()) {
      $transport = Transport::select('type_car')->firstOrFail($request->transport_id) ?? 0;
      $taxfee = RoadMoney::where('costumer_id', $request->costumer_id)
        ->where('route_from', $request->route_from)
        ->where('route_to', $request->route_to)
        ->where('cargo_id', $request->cargo_id)
        ->first();

      $data = RoadMoney::where('costumer_id', $request->costumer_id)
        ->where('route_from', $request->route_from)
        ->where('route_to', $request->route_to)
        ->where('cargo_id', $request->cargo_id)
        ->first()
        ->typecapacities()
        ->where('type_capacity_id', $request->type_capacity_id)
        ->where('type', $request->type)
        ->first();

      $response = response()->json([
        'data' => $data,
        'taxfee' => $taxfee,
        'type' => $transport,
      ]);
    }
    return $response;
  }

  public function datatabledetail($id)
  {
    $data = OperationalExpense::where([
      ['job_order_id', $id],
      ['type', 'roadmoney']
    ]);

    return Datatables::of($data)->make(true);
  }
}
