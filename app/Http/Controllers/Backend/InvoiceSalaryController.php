<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ContinousPaperLong;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\ConfigCoa;
use App\Models\Cooperation;
use App\Models\Driver;
use App\Models\InvoiceSalary;
use App\Models\JobOrder;
use App\Models\Journal;
use App\Models\Prefix;
use App\Traits\CarbonTrait;
use Carbon\Carbon;
use DataTables;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class InvoiceSalaryController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:invoicesalaries-list|invoicesalaries-create|invoicesalaries-edit|invoicesalaries-delete', ['only' => ['index']]);
    $this->middleware('permission:invoicesalaries-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:invoicesalaries-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:invoicesalaries-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "List Invoice Gaji Supir";
    $config['page_description'] = "Daftar List Invoice Gaji Supir";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Invoice Gaji Supir"],
    ];
    if ($request->ajax()) {
      $date = $request['date'];
      $driverId = $request['driver_id'];
      $data = InvoiceSalary::with(['transport:id,num_pol', 'driver:id,name'])
        ->when($date, function ($query, $date) {
          $date_begin = $date."-01";
          $date_end = Carbon::createFromFormat('Y-m', $date)->endOfMonth()->toDateString();
          return $query->whereBetween('invoice_date', [$date_begin, $date_end]);
        })
        ->when($driverId, function ($query, $driverId) {
          return $query->where('driver_id', $driverId);
        });

      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('details_url', function (InvoiceSalary $invoiceSalary) {
          return route('backend.invoicesalaries.datatabledetail', $invoiceSalary->id);
        })
        ->addColumn('action', function ($row) {
          $actionBtn = '
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-eye"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                  <a href="invoicesalaries/' . $row->id . '" class="dropdown-item">Invoice Detail</a>
                </div>
            </div>
          ';
          return $actionBtn;
        })
        ->make(true);

    }
    return view('backend.invoice.invoicesalaries.index', compact('config', 'page_breadcrumbs'));
  }

  public function create(Request $request)
  {
    $config['page_title'] = "Create Invoice Gaji Supir";
    $config['page_description'] = "Create Invoice Gaji Supir";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Create Invoice Gaji Supir"],
    ];
    $driver_id = $request->driver_id;
    $transport_id = $request->transport_id;
    if ($request->ajax()) {
      $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])->withSum('operationalexpense', 'amount')
        ->where('type', 'self')
        ->where('status_salary', '0')
        ->where('status_cargo', 'selesai')
        ->where('status_document', '1')
        ->when($driver_id, function ($query, $driver_id) {
          return $query->where('driver_id', $driver_id);
        })
        ->when($transport_id, function ($query, $transport_id) {
          return $query->where('transport_id', $transport_id);
        });
      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'invoicesalaries')->sole();
    return view('backend.invoice.invoicesalaries.create', compact('config', 'page_breadcrumbs', 'selectCoa'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'job_order_id' => 'required|array',
      'job_order_id.*' => 'required|integer',
      'num_bill' => 'required|integer',
      'driver_id' => 'required|integer',
      'coa_id' => 'required|integer',
      'invoice_date' => 'required|date_format:Y-m-d',
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
        if (($checksaldo->saldo ?? FALSE) && $request->grand_total <= $checksaldo->saldo) {
          $data = InvoiceSalary::create([
            'prefix' => 'GAJI',
            'num_bill' => $request->input('num_bill'),
            'driver_id' => $request->input('driver_id'),
            'invoice_date' => $request->input('invoice_date')." ".Carbon::now()->format('H:i:s'),
            'grandtotal' => $request->input('grand_total'),
            'description' => $request->input('description'),
            'memo' => $request->input('memo'),
          ]);

          foreach ($request->job_order_id as $item):
            JobOrder::where('id', $item)->update([
              'invoice_salary_id' => $data->id,
              'status_salary' => '1',
              'salary_coa_id' => $request->input('coa_id')
            ]);
          endforeach;

          Journal::create([
            'coa_id' => $request->input('coa_id'),
            'date_journal' => $request->input('invoice_date')." ".Carbon::now()->format('H:i:s'),
            'debit' => 0,
            'kredit' => $request->input('grand_total'),
            'table_ref' => 'invoicesalaries',
            'code_ref' => $data->id,
            'description' => "Pembayaran gaji supir $driver->name"
          ]);

          Journal::create([
            'coa_id' => 37,
            'date_journal' => $request->input('invoice_date')." ".Carbon::now()->format('H:i:s'),
            'debit' => $request->input('grand_total'),
            'kredit' => 0,
            'table_ref' => 'invoicesalaries',
            'code_ref' => $data->id,
            'description' => "Beban gaji supir $driver->name dengan $coa->name"
          ]);

          DB::commit();
          $response = response()->json([
            'status' => 'success',
            'message' => 'Data has been saved',
            'redirect' => '/backend/invoicesalaries',
          ]);
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

  public function show($id)
  {
    $config['page_title'] = "Invoice Gaji Supir";
    $config['print_url'] = "/backend/invoicesalaries/$id/print";
    $config['print_dotMatrix_url'] = "/backend/invoicesalaries/$id/dotmatrix";
    $page_breadcrumbs = [
      ['page' => '/backend/invoicesalaries', 'title' => "List Invoice Gaji"],
      ['page' => '#', 'title' => "Invoice Gaji Supir"],
    ];
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = InvoiceSalary::with(['joborders.costumer:id,name', 'joborders.routefrom:id,name', 'joborders.routeto:id,name', 'transport:id,num_pol', 'driver:id,name', 'joborders' => function ($q) {
      $q->withSum('operationalexpense', 'amount');
    }])->findOrFail($id);

    return view('backend.invoice.invoicesalaries.show', compact('config', 'page_breadcrumbs', 'data', 'cooperationDefault'));
  }

  public function print($id)
  {
    $config['page_title'] = "Invoice Gaji Supir";
    $page_breadcrumbs = [
      ['page' => '/backend/invoicesalaries', 'title' => "List Invoice Gaji"],
      ['page' => '#', 'title' => "Invoice Gaji Supir"],
    ];
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = InvoiceSalary::with(['joborders.costumer.cooperation:id,nickname', 'joborders.cargo', 'joborders.costumer:id,name,cooperation_id', 'joborders.routefrom:id,name', 'joborders.routeto:id,name', 'transport:id,num_pol', 'driver:id,name', 'joborders' => function ($q) {
      $q->withSum('operationalexpense', 'amount');
    }])->findOrFail($id);

    return view('backend.invoice.invoicesalaries.print', compact('config', 'page_breadcrumbs', 'data', 'cooperationDefault'));
  }

  public function dotMatrix($id)
  {
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = InvoiceSalary::with(['joborders.costumer.cooperation:id,nickname', 'joborders.cargo', 'joborders.costumer:id,name,cooperation_id', 'joborders.routefrom:id,name', 'joborders.routeto:id,name', 'transport:id,num_pol', 'driver:id,name', 'joborders' => function ($q) {
      $q->withSum('operationalexpense', 'amount');
    }])->findOrFail($id);

    $result = '';
    $no = 1;
    foreach ($data->joborders as $val):
      $item[] = [
        'no' => $no++,
        'tgl' => $val->date_begin,
        'muatan' => $val->costumer->cooperation->nickname,
        'rute' => $val->routefrom->name . '-' . $val->routeto->name,
        'costumer' => $val->cargo->name,
        'Nominal' => number_format($val->total_salary, 0, '.', ',')
      ];
    endforeach;
    $item[] = ['no' => '--------------------------------------------------------------------------------'];
    $item[] = ['1' => '', '2' => '', '3' => '', '4' => '', 'name' => 'Total', 'nominal' => number_format($data->grandtotal, 0, '.', ',')];

    $paper = array(
      'panjang' => 80,
      'baris' => 29,
      'spasi' => 3,
      'column_width' => [
        'header' => [40, 40],
        'table' => [3, 10, 10, 31, 15, 11],
        'footer' => [40, 40]
      ],
      'header' => [
        'left' => [
          $cooperationDefault['nickname'],
          $cooperationDefault['address'],
          'Telp: ' . $cooperationDefault['phone'],
          'Fax: ' . $cooperationDefault['fax'],
          'INVOICE GAJI SUPIR',

        ],
        'right' => [
          'No. Gaji: ' . $data->num_invoice,
          'Supir: ' . $data->driver->name,
          'Tanggal: ' . $data->date_invoice,
        ]
      ],
      'footer' => [
        ['align' => 'center', 'data' => ['Mengetahui', 'Mengetahui']],
        ['align' => 'center', 'data' => ['', '']],
        ['align' => 'center', 'data' => ['', '']],
        ['align' => 'center', 'data' => [Auth::user()->name, $data->driver->name]],
      ],
      'table' => [
        'header' => ['No', 'Tgl. Muat', 'Muatan', 'Rute', 'Muatan', 'Nominal'],
        'produk' => $item,
        'footer' => array(
          'catatan' => ''
        )
      ]
    );
    $printed = new ContinousPaperLong($paper);
    $result .= $printed->output() . "\n";
    return response($result, 200)->header('Content-Type', 'text/plain');
  }

  public function findbypk(Request $request)
  {
    $data = json_decode($request->data);
    $response = NULL;
    if ($request->data) {
      $result = JobOrder::with(['anotherexpedition:id,name', 'cargo', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])->withSum('operationalexpense', 'amount')->whereIn('id', $data)->get();

      $response = response()->json([
        'data' => $result,
      ]);
    }
    return $response;
  }

  public function datatabledetail($id)
  {
    $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])->withSum('operationalexpense', 'amount')->where('invoice_salary_id', $id);

    return Datatables::of($data)->make(true);
  }

}
