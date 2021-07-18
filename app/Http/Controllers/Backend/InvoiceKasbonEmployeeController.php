<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ContinousPaper;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\ConfigCoa;
use App\Models\Employee;
use App\Models\InvoiceKasbonEmployee;
use App\Models\Journal;
use App\Models\Kasbon;
use App\Models\KasbonEmployee;
use App\Models\PaymentKasbonEmployee;
use App\Models\Prefix;
use App\Models\Setting;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class InvoiceKasbonEmployeeController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:invoicekasbonemployees-list|invoicekasbonemployees-create|invoicekasbonemployees-edit|invoicekasbonemployees-delete', ['only' => ['index']]);
    $this->middleware('permission:invoicekasbonemployees-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:invoicekasbonemployees-edit', ['only' => ['edit', 'update']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "List Invoice Kasbon Karyawaan";
    $config['page_description'] = "Daftar List Invoice Kasbon Karyawaan";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Invoice Kasbon Karyawaan"],
    ];
    if ($request->ajax()) {
      $data = InvoiceKasbonEmployee::with(['employee:id,name']);
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('details_url', function (InvoiceKasbonEmployee $invoiceKasbonEmployee) {
          return route('backend.invoicekasbonemployees.datatabledetail', $invoiceKasbonEmployee->id);
        })
        ->addColumn('action', function ($row) {
          $restPayment = $row->rest_payment != 0 ? '<a href="invoicekasbonemployees/' . $row->id . '/edit" class="dropdown-item">Bayar Sisa</a>' : NULL;
          $actionBtn = '
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-eye"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                  ' . $restPayment . '
                  <a href="invoicekasbonemployees/' . $row->id . '" class="dropdown-item">Invoice Detail</a>
                </div>
            </div>
          ';
          return $actionBtn;
        })
        ->make(true);

    }
    return view('backend.accounting.invoicekasbonemployees.index', compact('config', 'page_breadcrumbs'));
  }

  public function create(Request $request)
  {
    $config['page_title'] = "Create Invoice Kasbon Karyawaan";
    $config['page_description'] = "Create Invoice Kasbon Karyawaan";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Create Invoice Kasbon Karyawaan"],
    ];
    $employee_id = $request->employee_id;
    if ($request->ajax()) {
      $data = KasbonEmployee::with(['employee:id,name'])
        ->where('invoice_kasbon_employee_id', '=', NULL)
        ->where('kasbon_employees.status', '0')
        ->when($employee_id, function ($query, $employee_id) {
          return $query->where('employee_id', $employee_id);
        });
      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'invoicekasbonemployees')->sole();
    return view('backend.accounting.invoicekasbonemployees.create', compact('config', 'page_breadcrumbs', 'selectCoa'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'kasbon_id' => 'required|array',
      'kasbon_id.*' => 'required|integer',
      'prefix' => 'required|integer',
      'num_bill' => 'required|integer',
      'employee_id' => 'required|integer',
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
        $totalPayment = 0;
        $payments = $request->payment;
        $prefix = Prefix::find($request->prefix);
        $employee = Employee::findOrFail($request->employee_id);
        $statusPayment = 1;

        foreach ($payments['date'] as $key => $item):
          $totalPayment += $payments['payment'][$key];
        endforeach;
        $restPayment = $request->total_kasbon - $totalPayment;
        $data = InvoiceKasbonEmployee::create([
          'prefix' => $prefix->name,
          'num_bill' => $request->input('num_bill'),
          'employee_id' => $request->input('employee_id'),
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

        foreach ($request->kasbon_id as $item):
          KasbonEmployee::where('id', $item)->update(['invoice_kasbon_employee_id' => $data->id, 'status' => $statusPayment]);
        endforeach;

        foreach ($payments['date'] as $key => $item):
          PaymentKasbonEmployee::create([
            'invoice_kasbon_employee_id' => $data->id,
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
            'table_ref' => 'invoicekasbonemployees',
            'description' => "Penambahan saldo dari kasbon karyawaan $employee->name"
          ]);

          Journal::create([
            'coa_id' => 8,
            'date_journal' => $payments['date'][$key],
            'debit' => 0,
            'kredit' => $payments['payment'][$key],
            'table_ref' => 'invoicekasbonemployees',
            'description' => "Pembayaran kasbon karyawaan $employee->name ke $coa->name"
          ]);
        endforeach;


        if ($restPayment <= -1) {
          return response()->json([
            'status' => 'error',
            'message' => 'Pastikan sisa tagihan tidak negative',
            'redirect' => '/backend/invoicekasbonemployees',
          ]);
          DB::rollBack();
        }

        DB::commit();
        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => '/backend/invoicekasbonemployees',
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
    $config['page_title'] = "Invoice Kasbon Karyawaan";
    $config['print_url'] = "/backend/invoicekasbonemployees/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/invoicekasbonemployees', 'title' => "List Invoice Kasbon Karyawaan"],
      ['page' => '#', 'title' => "Invoice Kasbon Karyawaan"],
    ];
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    $data = InvoiceKasbonEmployee::where('id', $id)->select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))->with(['employee:id,name', 'kasbonemployees', 'paymentkasbonemployes'])->firstOrFail();
    return view('backend.accounting.invoicekasbonemployees.show', compact('config', 'page_breadcrumbs', 'data', 'profile'));
  }

  public function print($id)
  {
    $config['page_title'] = "Invoice Kasbon Karyawaan";
    $page_breadcrumbs = [
      ['page' => '/backend/invoicekasbonemployees', 'title' => "List Invoice Kasbon Karyawaan"],
      ['page' => '#', 'title' => "Invoice Kasbon Karyawaan"],
    ];
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    $data = InvoiceKasbonEmployee::where('id', $id)->select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))->with(['employee:id,name', 'kasbonemployees', 'paymentkasbonemployes'])->firstOrFail();

    $result = '';
    $no = 1;
    foreach ($data->kasbonemployees as $val):
      $item[] = ['no' => $no++, 'nama' => $val->memo, 'nominal' => number_format($val->amount, 0, '.', ',')];
    endforeach;

    $item[] = ['no' => str_pad('Pembayaran', 36, '-', STR_PAD_BOTH)];
    $item[] = ['no' => 'No', 'nama' => 'Tgl Pembayaran', 'nominal' => 'Nominal'];
    $item[] = ['no' => '------------------------------------'];

    $noPay = 1;
    foreach ($data->paymentkasbonemployes as $val):
      $item[] = ['no' => $noPay++, 'nama' => $val->date_payment, 'nominal' => number_format($val->payment, 0, '.', ',')];
    endforeach;
    $item[] = ['no' => '------------------------------------'];
    $item[] = ['no' => '', 'nama' => 'Total Kasbon', 'nominal' => number_format($data->total_kasbon, 0, '.', ',')];
    $item[] = ['no' => '', 'nama' => 'Total Pembayaran', 'nominal' => number_format($data->total_payment, 0, '.', ',')];
    $item[] = ['no' => '', 'nama' => 'Sisa Tagihan', 'nominal' => number_format($data->rest_payment, 0, '.', ',')];

    $paper = array(
      'panjang' => 36,
      'baris' => 31,
      'spasi' => 2,
      'column_width' => [
        'header' => [36, 0],
        'table' => [3, 22, 11],
        'footer' => [18, 18]
      ],
      'header' => [
        'left' => [
          strtoupper($profile['name']),
          $profile['address'],
          'PEMBAYARAN KASBON KARYAWAAN',
          'No. Kasbon: '. $data->num_invoice,
          'Nama: ' . $data->employee->name,
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
    $printed = new ContinousPaper($paper);
    $result .= $printed->output() . "\n";
    return response($result, 200)->header('Content-Type', 'text/plain');
//    return view('backend.accounting.invoicekasbonemployees.print', compact('config', 'page_breadcrumbs', 'data', 'profile'));
  }

  public function edit($id)
  {
    $config['page_title'] = "Edit Invoice Kasbon Karyawaan";
    $config['page_description'] = "Edit Invoice Kasbon Karyawaan";
    $page_breadcrumbs = [
      ['page' => '/backend/invoicekasbonemployees', 'title' => "List Invoice Kasbon Karyawaan"],
      ['page' => '#', 'title' => "Edit Invoice Kasbon Karyawaan"],

    ];
    $data = InvoiceKasbonEmployee::where('id', $id)->select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))->with(['employee:id,name', 'kasbonemployees', 'paymentkasbonemployes.coa'])->firstOrFail();
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'invoicekasbonemployees')->sole();
    return view('backend.accounting.invoicekasbonemployees.edit', compact('config', 'page_breadcrumbs', 'data', 'selectCoa'));
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
        $data = InvoiceKasbonEmployee::findOrFail($id);
        $pluck = KasbonEmployee::whereHas('invoicekasbonemployee', function ($query) use ($id) {
          return $query->where('id', '=', $id);
        })->pluck('id');
        $employee = Employee::findOrFail($data->employee_id);

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

        KasbonEmployee::whereIn('id', $pluck)->update(['status' => $statusPayment]);

        foreach ($payments['date'] as $key => $item):
          PaymentKasbonEmployee::create([
            'invoice_kasbon_employee_id' => $data->id,
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
            'table_ref' => 'invoicekasbonemployees',
            'code_ref' => $data->id,
            'description' => "Penambahan saldo dari kasbon karyawaan $employee->name"
          ]);

          Journal::create([
            'coa_id' => 8,
            'date_journal' => $payments['date'][$key],
            'debit' => 0,
            'kredit' => $payments['payment'][$key],
            'table_ref' => 'invoicekasbonemployees',
            'code_ref' => $data->id,
            'description' => "Pembayaran kasbon karyawaan $employee->name ke $coa->name"
          ]);
        endforeach;

        if ($restPayment <= -1) {
          return response()->json([
            'status' => 'error',
            'message' => 'Pastikan sisa tagihan tidak negative',
            'redirect' => '/backend/invoicekasbonemployees',
          ]);
          DB::rollBack();
        }

        DB::commit();
        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => '/backend/invoicekasbonemployees',
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
      $result = KasbonEmployee::with('employee:id,name')->whereIn('id', $data)->get();

      $response = response()->json([
        'data' => $result,
      ]);
    }
    return $response;
  }

  public function datatabledetail($id)
  {
    $data = KasbonEmployee::with(['employee:id,name'])->where('invoice_kasbon_employee_id', $id);
    return Datatables::of($data)->make(true);
  }
}
