<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ContinousPaper;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\ConfigCoa;
use App\Models\Cooperation;
use App\Models\Driver;
use App\Models\InvoiceKasbon;
use App\Models\Journal;
use App\Models\Kasbon;
use App\Models\PaymentKasbon;
use App\Models\Prefix;
use App\Traits\CarbonTrait;
use DataTables;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class InvoiceKasbonController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:invoicekasbons-list|invoicekasbons-create|invoicekasbons-edit|invoicekasbons-delete', ['only' => ['index']]);
    $this->middleware('permission:invoicekasbons-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:invoicekasbons-edit', ['only' => ['edit', 'update']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "List Invoice Kasbon Supir";
    $config['page_description'] = "Daftar List Invoice Kasbon Supir";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Invoice Kasbon Supir"],
    ];
    if ($request->ajax()) {
      $data = InvoiceKasbon::with(['driver:id,name']);
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('details_url', function (InvoiceKasbon $invoiceKasbon) {
          return route('backend.invoicekasbons.datatabledetail', $invoiceKasbon->id);
        })
        ->addColumn('action', function ($row) {
          $restPayment = $row->rest_payment != 0 ? '<a href="invoicekasbons/' . $row->id . '/edit" class="dropdown-item">Bayar Sisa</a>' : NULL;
          $actionBtn = '
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-eye"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                  ' . $restPayment . '
                  <a href="invoicekasbons/' . $row->id . '" class="dropdown-item">Invoice Detail</a>
                </div>
            </div>
          ';
          return $actionBtn;
        })
        ->make(true);

    }
    return view('backend.invoice.invoicekasbons.index', compact('config', 'page_breadcrumbs'));
  }

  public function create(Request $request)
  {
    $config['page_title'] = "Create Invoice Kasbon Supir";
    $config['page_description'] = "Create Invoice Kasbon Supir";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Create Invoice Kasbon Supir"],
    ];
    $driver_id = $request->driver_id;
    $transport_id = $request->transport_id;
    if ($request->ajax()) {
      $data = Kasbon::with(['driver:id,name'])
        ->where('invoice_kasbon_id', '=', NULL)
        ->where('kasbons.status', '0')
        ->when($driver_id, function ($query, $driver_id) {
          return $query->where('driver_id', $driver_id);
        });
      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'invoicekasbons')->sole();
    return view('backend.invoice.invoicekasbons.create', compact('config', 'page_breadcrumbs', 'selectCoa'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'job_order_id' => 'required|array',
      'job_order_id.*' => 'required|integer',
      'prefix' => 'required|integer',
      'num_bill' => 'required|integer',
      'driver_id' => 'required|integer',
      'total_kasbon' => 'required|integer',
      'memo' => 'string',
      'payment.date' => 'required|array',
      'payment.date.*' => 'required|date_format:Y-m-d',
      'payment.payment' => 'required|array',
      'payment.payment.*' => 'required|integer',
      'payment.coa_id.*' => 'required|integer',
      'payment.coa_id..*' => 'required|integer',
    ]);

    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $prefix = Prefix::findOrFail($request->prefix);
        $totalPayment = 0;
        $payments = $request->payment;
        $statusPayment = 1;
        $driver = Driver::findOrFail($request->driver_id);

        foreach ($payments['date'] as $key => $item):
          $totalPayment += $payments['payment'][$key];
        endforeach;
        $restPayment = $request->total_kasbon - $totalPayment;
        $data = InvoiceKasbon::create([
          'prefix' => $prefix->name,
          'num_bill' => $request->input('num_bill'),
          'driver_id' => $request->input('driver_id'),
          'total_kasbon' => $request->input('total_kasbon'),
          'total_payment' => $totalPayment,
          'rest_payment' => $restPayment,
          'memo' => $request->input('memo'),
        ]);

        if ($restPayment > 0) {
          $statusPayment = '1';
        } else {
          $statusPayment = '2';
        }

        foreach ($request->job_order_id as $item):
          Kasbon::where('id', $item)->update(['invoice_kasbon_id' => $data->id, 'status' => $statusPayment]);
        endforeach;

        foreach ($payments['date'] as $key => $item):
          PaymentKasbon::create([
            'invoice_kasbon_id' => $data->id,
            'coa_id' => $payments['coa_id'][$key],
            'date_payment' => $payments['date'][$key],
            'payment' => $payments['payment'][$key],
          ]);


        endforeach;

        if ($restPayment <= -1) {
          return response()->json([
            'status' => 'error',
            'message' => 'Pastikan sisa tagihan tidak negative',
            'redirect' => '/backend/invoicekasbons',
          ]);
          DB::rollBack();
        }

        DB::commit();
        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => '/backend/invoicekasbons',
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

  public function show($id)
  {
    $config['page_title'] = "Invoice Kasbon Supir";
    $config['print_url'] = "/backend/invoicekasbons/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/invoicekasbons', 'title' => "List Invoice Kasbon Supir"],
      ['page' => '#', 'title' => "Invoice Kasbon Supir"],
    ];
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = InvoiceKasbon::where('id', $id)->select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))->with(['driver:id,name', 'kasbons', 'paymentkasbons'])->firstOrFail();
    return view('backend.invoice.invoicekasbons.show', compact('config', 'page_breadcrumbs', 'data', 'cooperationDefault'));
  }

  public function print($id)
  {
    $config['page_title'] = "Invoice Kasbon Supir";
    $page_breadcrumbs = [
      ['page' => '/backend/invoicekasbons', 'title' => "List Invoice Kasbon Supir"],
      ['page' => '#', 'title' => "Invoice Kasbon Supir"],
    ];
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = InvoiceKasbon::where('id', $id)->select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))->with(['driver:id,name', 'kasbons', 'paymentkasbons'])->firstOrFail();

    $result = '';
    $no = 1;
    foreach ($data->kasbons as $val):
      $item[] = ['no' => $no++, 'nama' => $val->memo, 'nominal' => number_format($val->amount, 0, '.', ',')];
    endforeach;

    $item[] = ['no' => str_pad('Pembayaran', 36, '-', STR_PAD_BOTH)];
    $item[] = ['no' => 'No', 'nama' => 'Tgl Pembayaran', 'nominal' => 'Nominal'];
    $item[] = ['no' => '------------------------------------'];

    $noPay = 1;
    foreach ($data->paymentkasbons as $val):
      $item[] = ['no' => $noPay++, 'nama' => $val->date_payment, 'nominal' => number_format($val->payment, 0, '.', ',')];
    endforeach;
    $item[] = ['no' => '------------------------------------'];
    $item[] = ['no' => '', 'nama' => 'Total Kasbon', 'nominal' => number_format($data->total_kasbon, 0, '.', ',')];
    $item[] = ['no' => '', 'nama' => 'Total Pembayaran', 'nominal' => number_format($data->total_payment, 0, '.', ',')];
    $item[] = ['no' => '', 'nama' => 'Sisa Tagihan', 'nominal' => number_format($data->rest_payment, 0, '.', ',')];

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
          'PEMBAYARAN KASBON SUPIR',
          'No. Kasbon: ' . $data->num_invoice,
          'Nama: ' . $data->driver->name,
          'Tgl Pembayaran: ' . $this->convertToDate($data->created_at),
        ],
        'right' => [
          ''
        ]
      ],
      'footer' => [
        ['align' => 'center', 'data' => ['Mengetahui', 'Mengetahui']],
        ['align' => 'center', 'data' => ['', '']],
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
    $printed = new ContinousPaper($paper);
    $result .= $printed->output() . "\n";
    return response($result, 200)->header('Content-Type', 'text/plain');
    //    return view('backend.invoice.invoicekasbons.print', compact('config', 'page_breadcrumbs', 'data', 'profile'));
  }

  public function edit($id)
  {
    $config['page_title'] = "Edit Invoice Kasbon Supir";
    $config['page_description'] = "Edit Invoice Kasbon Supir";
    $page_breadcrumbs = [
      ['page' => '/backend/invoicekasbons', 'title' => "List Invoice Kasbon Supir"],
      ['page' => '#', 'title' => "Edit Invoice Kasbon Supir"],

    ];
    $data = InvoiceKasbon::where('id', $id)->select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))->with(['driver:id,name', 'kasbons', 'paymentkasbons.coa'])->firstOrFail();
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'invoicekasbons')->sole();
    return view('backend.invoice.invoicekasbons.edit', compact('config', 'page_breadcrumbs', 'data', 'selectCoa'));
  }

  public function update(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'payment.date' => 'required|array',
      'payment.date.*' => 'required|date_format:Y-m-d',
      'payment.payment' => 'required|array',
      'payment.payment.*' => 'required|integer',
      'payment.coa_id.*' => 'required|integer',
      'payment.coa_id..*' => 'required|integer',
    ]);

    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $totalPayment = 0;
        $payments = $request->payment;
        $statusPayment = 1;
        foreach ($payments['date'] as $key => $item):
          $totalPayment += $payments['payment'][$key];
        endforeach;
        $data = InvoiceKasbon::findOrFail($id);
        $pluck = Kasbon::whereHas('invoicekasbon', function ($query) use ($id) {
          return $query->where('id', '=', $id);
        })->pluck('id');
        $driver = Driver::findOrFail($data->driver_id);
        $restPayment = $data->rest_payment;
        $restPayment -= $totalPayment;
        $totalPayment += $data->total_payment;
        $data->update([
          'total_payment' => $totalPayment,
          'rest_payment' => $restPayment
        ]);

        if ($restPayment > 0) {
          $statusPayment = '1';
        } else {
          $statusPayment = '2';
        }
        Kasbon::whereIn('id', $pluck)->update(['status' => $statusPayment]);

        foreach ($payments['date'] as $key => $item):
          PaymentKasbon::create([
            'invoice_kasbon_id' => $data->id,
            'coa_id' => $payments['coa_id'][$key],
            'date_payment' => $payments['date'][$key],
            'payment' => $payments['payment'][$key],
          ]);

          $coa = Coa::findOrFail($payments['coa_id'][$key]);
          Journal::create([
            'coa_id' => $payments['coa_id'][$key],
            'date_journal' => $payments['date'][$key],
            'debit' => $payments['payment'][$key],
            'kredit' => 0,
            'table_ref' => 'invoicekasbons',
            'code_ref' => $data->id,
            'description' => "Penambahan saldo dari kasbon supir $driver->name dengan No. Invoice: " .$data->prefix.'-'.$data->num_bill.""
          ]);

          Journal::create([
            'coa_id' => 7,
            'date_journal' => $payments['date'][$key],
            'debit' => 0,
            'kredit' => $payments['payment'][$key],
            'table_ref' => 'invoicekasbons',
            'code_ref' => $data->id,
            'description' => "Pembayaran kasbon supir $driver->name ke $coa->name dengan No. Invoice: " .$data->prefix.'-'.$data->num_bill.""
          ]);
        endforeach;

        if ($restPayment <= -1) {
          return response()->json([
            'status' => 'error',
            'message' => 'Pastikan sisa tagihan tidak negative',
            'redirect' => '/backend/invoicekasbons',
          ]);
          DB::rollBack();
        }

        DB::commit();
        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => '/backend/invoicekasbons',
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

  public function findbypk(Request $request)
  {
    $data = json_decode($request->data);
    $response = NULL;
    if ($request->data) {
      $result = Kasbon::with('driver:id,name')->whereIn('id', $data)->get();

      $response = response()->json([
        'data' => $result,
      ]);
    }
    return $response;
  }

  public function datatabledetail($id)
  {
    $data = Kasbon::with(['driver:id,name'])->where('invoice_kasbon_id', $id);
    return Datatables::of($data)->make(true);
  }
}
