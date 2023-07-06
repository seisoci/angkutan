<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ContinousPaper;
use App\Http\Controllers\Controller;
use App\Models\ConfigCoa;
use App\Models\Driver;
use App\Models\InvoiceSalary;
use App\Models\JobOrder;
use App\Models\Journal;
use App\Models\OperationalExpense;
use App\Models\RoadMoney;
use App\Models\Setting;
use App\Models\Transport;
use App\Models\TypeCapacity;
use App\Services\JobOrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

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

    if ($request->ajax()) {
      $data = JobOrder::with([
        'anotherexpedition:id,name',
        'driver:id,name',
        'costumer:id,name',
        'cargo:id,name',
        'transport:id,num_pol',
        'routefrom:id,name',
        'routeto:id,name'
      ]);

      if($request->filled('another_expedition_id')){
        $data->where('another_expedition_id', $request['another_expedition_id']);
      }
      if($request->filled('driver_id')){
        $data->where('driver_id', $request['driver_id']);
      }
      if($request->filled('transport_id')){
        $data->where('transport_id', $request['transport_id']);
      }
      if($request->filled('costumer_id')){
        $data->where('costumer_id', $request['costumer_id']);
      }
      if($request->filled('cargo_id')){
        $data->where('cargo_id', $request['cargo_id']);
      }
      if($request->filled('route_from')){
        $data->where('route_from', $request['route_from']);
      }
      if($request->filled('route_to')){
        $data->where('route_to', $request['route_to']);
      }
      if($request->filled('date_begin')){
        $data->where('date_begin', '>=', $request['date_begin']);
      }
      if($request->filled('date_end')){
        $data->where('date_end', '<=', $request['date_end']);
      }
      if($request->filled('status_cargo')){
        $data->where('status_cargo', $request['status_cargo']);
      }
      if($request->filled('status_salary')){
        $data->where('status_salary', $request['status_salary']);
      }
      if($request->filled('status_document')){
        if ($request['status_document'] === "zero") {
          $data->where('status_document', "0");
        } else {
          $data->where('status_document', $request['status_document']);
        }
      }


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
          $btnEditDetail = '<a href="' . route('backend.joborders.edit', $row['id']) . '" class="edit dropdown-item">Edit Detail</a>';
          if ($row['status_cargo'] != 'selesai' && $row['status_cargo'] != 'batal') {
            $btnEdit = '
            <a href="#" data-toggle="modal" data-target="#modalEdit"
               data-id="' . $row['id'] . '"
               data-type_payload="' . $row['type_payload'] . '"
               data-payload="' . $row['payload'] . '"
               data-status_cargo="' . $row['status_cargo'] . '"
               data-date_end="' . $row['date_end'] . '"
               class="edit dropdown-item">Edit</a>
            ';
          }
          if ($row['status_document'] != 1 && $row['status_cargo'] === 'selesai') {
            $btnEditDocument = '
            <a href="#" data-toggle="modal" data-target="#modalEditDocument" data-id="' . $row['id'] . '" class="edit dropdown-item">Edit Dokumen</a>';
          }
          if ($row['status_cargo'] == 'selesai' && $row['status_document'] == 1 && !$row['invoice_costumer_id'] && in_array(Auth::user()->roles[0]->name, ['super-admin', 'admin', 'akunting', 'operasional'])) {
            $btnShowTonase = '
            <a href="#" data-toggle="modal" data-target="#modalEditTonase"
              data-id="' . $row['id'] . '"
              data-basic_price="' . $row['basic_price'] . '"
              data-type_payload="' . $row['type_payload'] . '"
              data-payload="' . $row['payload'] . '"
              data-no_sj="' . $row['no_sj'] . '"
              data-no_shipment="' . $row['no_shipment'] . '"
              data-type="' . $row['type'] . '"
              data-basic_price_ldo="' . $row['basic_price_ldo'] . '"  class="edit dropdown-item">Input Tonase</a>';
          }
          if ($row['status_cargo'] == 'transfer') {
            $btnTransfer = '
            <a href="#" data-toggle="modal" data-target="#modalEditRoadMoney" data-id="' . $row['id'] . '"  class="edit dropdown-item">Input Uang Jalan</a>';
          }

          $actionBtn = '<div class="btn-group-vertical" role="group" aria-label="Vertical button group">
            <div class="btn-group" role="group">
                <button id="btnGroupVerticalDrop1" type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="fas fa-edit"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="btnGroupVerticalDrop1">
              <a href="' . route('backend.joborders.show', $row['id']). '" class="dropdown-item">Show Detail</a>
              ' . $btnTransfer . $btnEdit . $btnEditDetail . $btnEditDocument . $btnShowTonase . '
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

  public function store(Request $request, JobOrderService $jobOrderService)
  {
    $validator = Validator::make($request->all(), [
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
        $qsparepart = Setting::where('name', 'potongan sparepart')->first();
        $qsalary = Setting::where('name', 'gaji supir')->first();

        $jobOrderPrev = JobOrder::where([
          ['driver_id', $request['driver_id']],
          ['transport_id', $request['transport_id']],
          ['status_cargo', 'selesai'],
        ])->orderBy('created_at', 'desc')->first();

        if ($request['type'] === 'self') {
          $jobOrder = JobOrder::create([
            'date_begin' => $request['date_begin'],
            'type' => $request['type'],
            'num_bill' => "JO{$jo_date}-{$jo_num}",
            'prefix' => 'JO',
            'another_expedition_id' => $request['another_expedition_id'],
            'driver_id' => $request['driver_id'],
            'transport_id' => $request['transport_id'],
            'costumer_id' => $request['costumer_id'],
            'cargo_id' => $request['cargo_id'],
            'route_from' => $request['route_from'],
            'route_to' => $request['route_to'],
            'type_capacity' => $request['type_capacity'],
            'type_payload' => $request['type_payload'],
            'payload' => $request['payload'] ?? 1,
            'basic_price' => $request['basic_price'],
            'road_money_prev' => $jobOrderPrev['road_money_extra'] ?? 0,
            'road_money' => $request['road_money'],
            'cut_sparepart_percent' => $qsparepart['value'],
            'type_salary' => $request['type_salary'],
            'salary_percent' => $qsalary['value'],
            'tax_percent' => $request['tax_percent'],
            'fee_thanks' => $request['fee_thanks'],
            'description' => $request['description'],
            'km' => $request['km'],
          ]);
        } elseif ($request['type'] == 'ldo') {
          if (is_numeric($request['driver_id'])) {
            $driverId = Driver::findOrFail($request['driver_id'])->id;
          } else {
            $driverId = Driver::create([
              'another_expedition_id' => $request['another_expedition_id'],
              'name' => $request['driver_id'],
              'status' => 'active'
            ])->id;
          }

          if (is_numeric($request['transport_id'])) {
            $transportId = Transport::findOrFail($request['transport_id'])->id;
          } else {
            $transportId = Transport::create([
              'another_expedition_id' => $request['another_expedition_id'],
              'num_pol' => $request['transport_id'],
              'type_car' => 'engkel'
            ])->id;
          }

          $jobOrder = JobOrder::create([
            'date_begin' => $request['date_begin'],
            'type' => $request['type'],
            'num_bill' => "LDO{$jo_date}-{$jo_num}",
            'prefix' => 'LDO',
            'another_expedition_id' => $request['another_expedition_id'],
            'driver_id' => $driverId,
            'transport_id' => $transportId,
            'costumer_id' => $request['costumer_id'],
            'cargo_id' => $request['cargo_id'],
            'route_from' => $request['route_from'],
            'route_to' => $request['route_to'],
            'type_capacity' => $request['type_capacity'],
            'type_payload' => $request['type_payload'],
            'payload' => $request['payload'] ?? 1,
            'basic_price' => $request['basic_price'],
            'basic_price_ldo' => $request['basic_price_ldo'],
            'road_money_prev' => $jobOrderPrev['road_money_extra'] ?? 0,
            'road_money' => 0,
            'cut_sparepart_percent' => $qsparepart['value'],
            'salary_percent' => $qsalary['value'],
            'type_salary' => $request['type_salary'],
            'tax_percent' => $request['tax_percent'],
            'fee_thanks' => $request['fee_thanks'],
            'description' => $request['description'],
            'km' => $request['km'],
            'invoice_bill' => $request['basic_price'] * $request['payload']
          ]);
        }

        $jobOrderCalculate = $jobOrderService->calculate($jobOrder);
        $jobOrder->update($jobOrderCalculate);

        DB::commit();
        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => route('backend.joborders.index'),
        ]);
      } catch (\Throwable $throw) {
        DB::rollBack();
        $response = $throw;
      }
    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }

  public function edit($id)
  {
    $config['page_title'] = "Edit Job Order";
    $page_breadcrumbs = [
      ['page' => '/backend/joborders', 'title' => "List Job Order"],
      ['page' => '#', 'title' => "Edit Job Order"],
    ];
    $sparepart = Setting::where('name', 'potongan sparepart')->first();
    $gaji = Setting::where('name', 'gaji supir')->first();
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'joborders')->sole();
    $data = JobOrder::with([
      'anotherexpedition:id,name',
      'driver:id,name',
      'costumer:id,name',
      'cargo:id,name',
      'transport:id,num_pol',
      'routefrom:id,name',
      'routeto:id,name'
    ])
      ->find($id);
    $typeCapacity = TypeCapacity::find($data['type_capacity']);
    return view('backend.operational.joborders.edit', compact('config', 'page_breadcrumbs', 'data', 'sparepart', 'gaji', 'selectCoa', 'typeCapacity'));
  }

  public function update($id, Request $request, JobOrderService $jobOrderService)
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
      $data = JobOrder::with('transport', 'routeto', 'routeto', 'operationalexpense')
        ->withSum('operationalexpense', 'amount')
        ->find($id);

      if ($request['no_sj'] || $request['shipement']) {
        $data->update($request->all());

        if ($data['status_cargo'] == 'selesai') {
          Journal::where([
            ['table_ref', 'joborders'],
            ['code_ref', $data['id']]
          ])->delete();

          Journal::create([
            'coa_id' => 43,
            'date_journal' => $data['date_begin'],
            'debit' => $data['total_basic_price'],
            'kredit' => 0,
            'table_ref' => 'joborders',
            'code_ref' => $data['id'],
            'description' => "Penambahan piutang usaha dari joborder" . $data->num_bill . " dengan No. Pol: " . $data->transport->num_pol . " dari rute " . $data->routefrom->name . " tujuan " . $data->routeto->name,
          ]);

          Journal::create([
            'coa_id' => 52,
            'date_journal' => $data['date_begin'],
            'debit' => 0,
            'kredit' => $data['total_basic_price'],
            'table_ref' => 'joborders',
            'code_ref' => $data['id'],
            'description' => "Penambahan Pendapatan joborder" . $data->num_bill . " dengan No. Pol: " . $data->transport->num_pol . " dari rute " . $data->routefrom->name . " tujuan " . $data->routeto->name,
          ]);
        }

        $jobOrderCalculate = $jobOrderService->calculate($data);
        $data->update($jobOrderCalculate);

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

          foreach ($data['operationalexpense'] as $item):
            Journal::where('table_ref', 'operationalexpense')->where('code_ref', $item->id)->delete();
          endforeach;

          foreach ($data['roadmoneydetail'] as $item):
            Journal::where('table_ref', 'operationalexpense')->where('code_ref', $item->id)->delete();
          endforeach;
          $response = response()->json([
            'status' => 'success',
            'message' => 'Data has been updated',
          ]);
          break;
        case 'selesai':

          Journal::create([
            'coa_id' => 43,
            'date_journal' => $data['date_begin'],
            'debit' => $data['total_basic_price'],
            'kredit' => 0,
            'table_ref' => 'joborders',
            'code_ref' => $data['id'],
            'description' => "Penambahan piutang usaha dari joborder" . $data->num_bill . " dengan No. Pol: " . $data->transport->num_pol . " dari rute " . $data->routefrom->name . " tujuan " . $data->routeto->name,
          ]);

          Journal::create([
            'coa_id' => 52,
            'date_journal' => $data['date_begin'],
            'debit' => 0,
            'kredit' => $data['total_basic_price'],
            'table_ref' => 'joborders',
            'code_ref' => $data['id'],
            'description' => "Penambahan Pendapatan joborder" . $data->num_bill . " dengan No. Pol: " . $data->transport->num_pol . " dari rute " . $data->routefrom->name . " tujuan " . $data->routeto->name,
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

  public function show($id)
  {
    $config['page_title'] = "Detail Job Order";
    $config['print_url'] = "/backend/joborders/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/drivers', 'title' => "List Job Order"],
      ['page' => '#', 'title' => "Detail Job Order"],
    ];
    $data = JobOrder::with(['anotherexpedition', 'driver', 'costumer.cooperation', 'cargo', 'transport', 'routefrom', 'routeto', 'operationalexpense.expense'])
      ->withSum('roadmoneyreal', 'amount')
      ->findOrFail($id);
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'joborders')->sole();
    return view('backend.operational.joborders.show', compact('config', 'page_breadcrumbs', 'data', 'selectCoa'));
  }

  public function updateJobOrder($id, Request $request, JobOrderService $jobOrderService)
  {
    $validator = Validator::make($request->all(), [
      'type' => 'required|in:self,ldo',
      'transport_id' => 'required',
      'driver_id' => 'required',
      'costumer_id' => 'required|integer',
      'route_from' => 'required|integer',
      'route_to' => 'required|integer',
      'cargo_id' => 'required|integer',
      'basic_price' => 'required|gt:0',
    ]);

    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $qsparepart = Setting::where('name', 'potongan sparepart')->first();
        $qsalary = Setting::where('name', 'gaji supir')->first();
        $jobOrder = JobOrder::find($id);
        if ($request->type === 'self') {
          $jobOrder->update([
            'date_begin' => $request['date_begin'],
            'type' => $request['type'],
            'prefix' => 'JO',
            'another_expedition_id' => $request['another_expedition_id'],
            'driver_id' => $request['driver_id'],
            'transport_id' => $request['transport_id'],
            'costumer_id' => $request['costumer_id'],
            'cargo_id' => $request['cargo_id'],
            'route_from' => $request['route_from'],
            'route_to' => $request['route_to'],
            'type_capacity' => $request['type_capacity'],
            'type_payload' => $request['type_payload'],
            'payload' => $request['payload'] ?? 1,
            'basic_price' => $request['basic_price'],
            'road_money_prev' => $jobOrderPrev['road_money_extra'] ?? 0,
            'road_money' => $request['road_money'],
            'cut_sparepart_percent' => $qsparepart['value'],
            'salary_percent' => $qsalary['value'],
            'tax_percent' => $request['tax_percent'],
            'fee_thanks' => $request['fee_thanks'],
            'description' => $request['description'],
            'km' => $request['km'],
          ]);
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

          $jobOrder->update([
            'date_begin' => $request['date_begin'],
            'type' => $request['type'],
            'prefix' => 'LDO',
            'another_expedition_id' => $request['another_expedition_id'],
            'driver_id' => $driverId,
            'transport_id' => $transportId,
            'costumer_id' => $request['costumer_id'],
            'cargo_id' => $request['cargo_id'],
            'route_from' => $request['route_from'],
            'route_to' => $request['route_to'],
            'type_capacity' => $request['type_capacity'],
            'type_payload' => $request['type_payload'],
            'payload' => $request['payload'] ?? 1,
            'basic_price' => $request['basic_price'],
            'basic_price_ldo' => $request['basic_price_ldo'],
            'road_money_prev' => $jobOrderPrev['road_money_extra'] ?? 0,
            'road_money' => 0,
            'cut_sparepart_percent' => $qsparepart['value'],
            'salary_percent' => $qsalary['value'],
            'tax_percent' => $request['tax_percent'],
            'fee_thanks' => $request['fee_thanks'],
            'description' => $request['description'],
            'km' => $request['km'],
            'invoice_bill' => $request['basic_price'] * $request['payload']
          ]);
        }

        if ($jobOrder['status_cargo'] == 'selesai') {
          Journal::where([
            ['table_ref', 'joborders'],
            ['code_ref', $jobOrder->id]
          ])->delete();

          Journal::create([
            'coa_id' => 43,
            'date_journal' => $jobOrder['date_begin'],
            'debit' => $jobOrder['total_basic_price'],
            'kredit' => 0,
            'table_ref' => 'joborders',
            'code_ref' => $jobOrder['id'],
            'description' => "Penambahan piutang usaha dari joborder {$jobOrder['$prefix']}" . "-" . $jobOrder->num_bill . " dengan No. Pol: " . $jobOrder->transport->num_pol . " dari rute " . $jobOrder->routefrom->name . " tujuan " . $jobOrder->routeto->name,
          ]);

          Journal::create([
            'coa_id' => 52,
            'date_journal' => $jobOrder['date_begin'],
            'debit' => 0,
            'kredit' => $jobOrder['total_basic_price'],
            'table_ref' => 'joborders',
            'code_ref' => $jobOrder['id'],
            'description' => "Penambahan Pendapatan joborder {$jobOrder['$prefix']}" . "-" . $jobOrder->num_bill . " dengan No. Pol: " . $jobOrder->transport->num_pol . " dari rute " . $jobOrder->routefrom->name . " tujuan " . $jobOrder->routeto->name,
          ]);
        }

        if ($jobOrder['invoice_salary_id']) {
          InvoiceSalary::find($jobOrder['invoice_salary_id'])->update([
            'grandtotal' => $jobOrder['total_salary']
          ]);
        }

        $jobOrderCalculate = $jobOrderService->calculate($jobOrder);
        $jobOrder->update($jobOrderCalculate);

        DB::commit();
        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => '/backend/joborders',
        ]);
      } catch (\Throwable $throw) {
        Log::error($throw);
        DB::rollBack();
        $response = $throw;
      }
    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }

  public function print($id)
  {
    $config['page_title'] = "Detail Job Order";
    $config['print_url'] = "/backend/joborders/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/joborders', 'title' => "List Job Order"],
      ['page' => '#', 'title' => "Detail Job Order"],
    ];
    $itemBody = array();
    $data = JobOrder::with([
      'anotherexpedition',
      'driver:id,name',
      'costumer.cooperation:id,name',
      'cargo:id,name',
      'transport:id,num_pol',
      'routefrom:id,name',
      'routeto:id,name',
      'operationalexpense.expense',
      'roadmoneydetail'
    ])
      ->withSum('operationalexpense', 'amount')
      ->withSum('roadmoneydetail', 'amount')
      ->findOrFail($id);


    $no = 1;
    $totalRoadMoney = 0;
    foreach ($data->roadmoneydetail as $item):
      $itemBody [] = [
        'no' => $no,
        'nama' => 'Uang Jalan Ke-' . $no,
        'nominal' => number_format($item->amount, 0, '.', ',')
      ];
      $no++;
      $totalRoadMoney += $item->amount;
    endforeach;

    foreach ($data->operationalexpense as $val):
      $itemBody[] = ['no' => $no++, 'nama' => $val->expense->name, 'nominal' => number_format($val->amount, 0, '.', ',')];
      $totalRoadMoney += $val->amount;
    endforeach;
    $itemBody[] = ['no' => '------------------------------------'];
    $itemBody[] = ['1' => '', 'name' => 'Total', 'nominal' => number_format($totalRoadMoney, 0, '.', ',')];
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
          'NO. JO: ' . $data->num_bill,
          'KODE JOB ORDER : ' . $data->num_bill,
          'TGL. MUAT: ' . $data['date_begin'],
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
        'produk' => $itemBody,
        'footer' => array(
          'catatan' => 'KET: ' . $data->description ?? '',
        )
      ]
    );
    $printed = new ContinousPaper($paper);
    $result .= $printed->output() . "\n";
    return response($result, 200)->header('Content-Type', 'text/plain');
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
      $transport = Transport::select('type_car')->firstOrFail($request['transport_id']) ?? 0;

      $taxfee = RoadMoney::where('costumer_id', $request['costumer_id'])
        ->where('route_from', $request['route_from'])
        ->where('route_to', $request['route_to'])
        ->where('cargo_id', $request['cargo_id'])
        ->first();

      $data = RoadMoney::where('costumer_id', $request['costumer_id'])
        ->where('route_from', $request['route_from'])
        ->where('route_to', $request['route_to'])
        ->where('cargo_id', $request['cargo_id'])
        ->first()
        ->typecapacities()
        ->where('type_capacity_id', $request['type_capacity_id'])
        ->where('type', $request['type'])
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
    ]);

    return Datatables::of($data)->make(true);
  }

  public function jo_calculate(){
    try {
      $jobOrderService = new JobOrderService();

      $data = JobOrder::whereDate('date_begin', '>', '2023-03-01')
        ->whereDate('date_begin', '<', '2023-03-31')->get();

      foreach ($data ?? [] as $item):
        $calculate = $jobOrderService->calculate($item);
        $item->update($calculate);
      endforeach;
      dd("success");

    }catch(\Throwable $throw){
      Log::error($throw);
    }
  }
}
