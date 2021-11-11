<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AdditionalInvoiceCostumer;
use App\Models\Bank;
use App\Models\Coa;
use App\Models\ConfigCoa;
use App\Models\Cooperation;
use App\Models\Costumer;
use App\Models\Employee;
use App\Models\InvoiceCostumer;
use App\Models\JobOrder;
use App\Models\Journal;
use App\Models\PaymentCostumer;
use App\Models\Prefix;
use App\Models\Setting;
use App\Traits\CarbonTrait;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class InvoiceCostumerController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:invoicecostumers-list|invoicecostumers-create|invoicecostumers-edit|invoicecostumers-delete', ['only' => ['index']]);
    $this->middleware('permission:invoicecostumers-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:invoicecostumers-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:invoicecostumers-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "List Invoice Pelanggan";
    $config['page_description'] = "Daftar List Invoice Pelanggan";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Invoice Pelanggan"],
    ];
    $config['excel_url'] = 'reportinvoicecostumers/document?type=EXCEL';
    $config['pdf_url'] = 'reportinvoicecostumers/document?type=PDF';
    $config['print_url'] = 'reportinvoicecostumers/print';
    if ($request->ajax()) {
      $data = InvoiceCostumer::with(['costumer:id,name']);
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('details_url', function (InvoiceCostumer $invoiceCostumer) {
          return route('backend.invoicecostumers.datatabledetail', $invoiceCostumer->id);
        })
        ->addColumn('action', function ($row) {
          $restPayment = $row->rest_payment != 0 ? '<a href="invoicecostumers/' . $row->id . '/edit" class="dropdown-item">Input Pembayaran</a>' : NULL;
          $tax_coa_id = !$row->tax_coa_id && $row->total_tax > 0 ? '<a href="#" data-toggle="modal" data-target="#modalEditTax" data-id="' . $row->id . '"  data-tax="' . $row->total_tax . '" class="edit dropdown-item">Bayar Pajak</a>' : NULL;
          $fee_coa_id = !$row->fee_coa_id && $row->total_fee_thanks > 0 ? '<a href="#" data-toggle="modal" data-target="#modalEditFee" data-id="' . $row->id . '"  class="edit dropdown-item">Bayar Fee</a>' : NULL;
          if (Auth::user()->can('delete invoicecostumers')) {
            $deleteBtn = '<a href="#" data-toggle="modal" data-target="#modalDelete" data-id="' . $row->id . '" class="delete dropdown-item">Delete</a>';
          } else {
            $deleteBtn = '';
          }
          $actionBtn = '
            <div class="dropdown">
                  <button class="btn btn-secondary dropdown-toggle" type = "button" id = "dropdownMenuButton" data-toggle = "dropdown" aria-haspopup = "true" aria-expanded = "false" >
                      <i class="fas fa-eye" ></i >
                  </button >
                  <div class="dropdown-menu" aria-labelledby = "dropdownMenuButton" >
                    ' . $restPayment . $tax_coa_id . $fee_coa_id . '
                    <a href = "invoicecostumers/' . $row->id . '" class="dropdown-item" > Invoice Detail </a >
                    ' . $deleteBtn . '
                  </div >
              </div >
          ';
          return $actionBtn;
        })
        ->make(true);

    }
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'invoicecostumers')->sole();
    return view('backend.invoice.invoicecostumers.index', compact('config', 'page_breadcrumbs', 'selectCoa'));
  }

  public function create(Request $request)
  {
    $config['page_title'] = "Create Invoice Pelanggan";
    $config['page_description'] = "Create Invoice Pelanggan";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Create Invoice Pelanggan"],
    ];
    $costumer_id = $request->costumer_id;
    $route_to = $request->route_to;
    $route_from = $request->route_from;
    $cargo_id = $request->cargo_id;
    if ($request->ajax()) {
      $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])
        ->withSum('operationalexpense', 'amount')
        ->where('status_payment', '0')
        ->where('status_cargo', 'selesai')
        ->where('status_document', '1')
        ->when($costumer_id, function ($query, $costumer_id) {
          return $query->where('costumer_id', $costumer_id);
        })
        ->when($route_from, function ($query, $route_from) {
          return $query->where('route_from', $route_from);
        })
        ->when($route_to, function ($query, $route_to) {
          return $query->where('route_to', $route_to);
        })
        ->when($cargo_id, function ($query, $cargo_id) {
          return $query->where('cargo_id', $cargo_id);
        });
      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'invoicecostumers')->sole();
    return view('backend.invoice.invoicecostumers.create', compact('config', 'page_breadcrumbs', 'selectCoa'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'job_order_id' => 'required|array',
      'job_order_id.*' => 'required|integer',
      'prefix' => 'required|integer',
      'num_bill' => 'required|integer',
      'costumer_id' => 'required|integer',
      'invoice_date' => 'required|date_format:Y-m-d',
      'due_date' => 'required|date_format:Y-m-d',
      'coa_id' => 'required|integer',
    ]);

    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $prefix = Prefix::findOrFail($request->prefix);
        $costumer = Costumer::findOrFail($request->input('costumer_id'));
        $data = InvoiceCostumer::create([
          'prefix' => $prefix->name,
          'num_bill' => $request->input('num_bill'),
          'costumer_id' => $request->input('costumer_id'),
          'invoice_date' => $request->input('invoice_date'),
          'due_date' => $request->input('due_date'),
          'total_tax' => $request->input('total_tax'),
          'total_bill' => $request->input('total_bill'),
          'total_fee_thanks' => $request->input('total_fee'),
          'total_cut' => $request->input('total_cut') ?? 0,
          'total_piutang' => $request->input('total_piutang') ?? 0,
          'total_payment' => $request->input('payment.payment') ?? 0,
          'rest_payment' => $request->input('rest_payment'),
          'memo' => $request->input('memo'),
        ]);

        foreach ($request->job_order_id as $item):
          JobOrder::where('id', $item)->update(['invoice_costumer_id' => $data->id, 'status_payment' => '1']);
        endforeach;

        foreach ($request['tambahan']['total'] as $key => $item):
          $additionalInvoiceCostumer = AdditionalInvoiceCostumer::create([
            'invoice_costumer_id' => $data->id,
            'description' => $request['tambahan'][$key]['description'],
            'total' => $request['tambahan'][$key]['total']
          ]);

          Journal::create([
            'coa_id' => 43,
            'date_journal' => $data->date_begin,
            'debit' => $request['tambahan'][$key]['total'],
            'kredit' => 0,
            'table_ref' => 'additional_invoice_costumers',
            'code_ref' => $additionalInvoiceCostumer->id,
            'description' => "Penambahan piutang usaha dari " . $request['tambahan'][$key]['description'] . " dengan No Invoice Costumer" . $data->prefix . "-" . $data->num_bill,
          ]);

          Journal::create([
            'coa_id' => 52,
            'date_journal' => $data->date_begin,
            'debit' => 0,
            'kredit' => $request['tambahan'][$key]['total'],
            'table_ref' => 'additional_invoice_costumers',
            'code_ref' => $additionalInvoiceCostumer->id,
            'description' => "Penambahan Pendapatan joborder dari tagihan invoice costumer Penambahan piutang usaha dari " . $request['tambahan'][$key]['description'] . " dengan No Invoice Costumer" . $data->prefix . "-" . $data->num_bill,
          ]);
        endforeach;

        if (($request->input('total_cut') ?? 0) > 0) {
          Journal::create([
            'coa_id' => 43,
            'date_journal' => date("Y-m-d"),
            'debit' => 0,
            'kredit' => ($request->input('total_cut') ?? 0),
            'table_ref' => 'invoicecostumers',
            'code_ref' => $data->id,
            'description' => "Potongan Klaim tagihan JO pelanggan $costumer->name dan No. Invoice: " . $prefix->name . '-' . $request->input('num_bill')
          ]);

          Journal::create([
            'coa_id' => 46,
            'date_journal' => date("Y-m-d"),
            'debit' => $request->input('total_cut'),
            'kredit' => 0,
            'table_ref' => 'invoicecostumers',
            'code_ref' => $data->id,
            'description' => "Potongan Pendapatan untuk Potongan Klaim dengan No. Invoice " . $prefix->name . '-' . $request->input('num_bill')
          ]);
        }

        if (($request->input('total_piutang') ?? 0) > 0) {
          Journal::create([
            'coa_id' => 52,
            'date_journal' => date("Y-m-d"),
            'debit' => 0,
            'kredit' => $request->input('total_piutang'),
            'table_ref' => 'invoicecostumers',
            'code_ref' => $data->id,
            'description' => "Piutang Pendapatan untuk klaim pelanggan $costumer->name dengan No.Invoice: " . $prefix->name . '-' . $request->input('num_bill')
          ]);

          Journal::create([
            'coa_id' => 43,
            'date_journal' => date("Y-m-d"),
            'debit' => $request->input('total_piutang'),
            'kredit' => 0,
            'table_ref' => 'invoicecostumers',
            'code_ref' => $data->id,
            'description' => "Piutang Pendapatan untuk klaim pelanggan $costumer->name dengan No.Invoice: " . $prefix->name . '-' . $request->input('num_bill')
          ]);
        }

        if ($request->input('payment.payment') && $request->input('payment.date_payment')) {
          PaymentCostumer::create([
            'invoice_costumer_id' => $data->id,
            'coa_id' => $request->input('coa_id'),
            'date_payment' => $request->input('payment.date_payment'),
            'payment' => $request->input('payment.payment'),
            'description' => $request->input('payment.description'),
          ]);

          Journal::create([
            'coa_id' => $request->input('coa_id'),
            'date_journal' => $request->input('payment.date_payment'),
            'debit' => $request->input('payment.payment'),
            'kredit' => 0,
            'table_ref' => 'invoicecostumers',
            'code_ref' => $data->id,
            'description' => "Penambahan saldo dari tagihan JO pelanggan $costumer->name dengan No. Invoice: " . $prefix->name . '-' . $request->input('num_bill')
          ]);

          Journal::create([
            'coa_id' => 43,
            'date_journal' => $request->input('payment.date_payment'),
            'debit' => 0,
            'kredit' => ($request->input('payment.payment') ?? 0),
            'table_ref' => 'invoicecostumers',
            'code_ref' => $data->id,
            'description' => "Pembayaran tagihan JO pelanggan $costumer->name dengan No.Invoice: " . $prefix->name . '-' . $request->input('num_bill')
          ]);
        }

        if ($request->rest_payment <= -1) {
          DB::rollBack();
          return response()->json([
            'status' => 'error',
            'message' => 'Pastikan sisa tagihan tidak negative',
            'redirect' => '/backend/invoicecostumers',
          ]);
        }

        DB::commit();
        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => '/backend/invoicecostumers',
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
    $config['page_title'] = "Detail Pembayaran Pelanggan";
    $config['print_url'] = "/backend/invoicecostumers/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/invoicecostumers', 'title' => "List Detail Pembayaran Pelanggan"],
      ['page' => '#', 'title' => "Detail Detail Pembayaran Pelanggan"],
    ];
    $data = InvoiceCostumer::select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))
      ->with(['joborders', 'costumer.cooperation', 'paymentcostumers.coa', 'joborders.anotherexpedition:id,name', 'joborders.driver:id,name', 'joborders.costumer:id,name', 'joborders.cargo:id,name', 'joborders.transport:id,num_pol', 'joborders.routefrom:id,name', 'joborders.routeto:id,name'])
      ->findOrFail($id);
    return view('backend.invoice.invoicecostumers.show', compact('config', 'page_breadcrumbs', 'data'));
  }

  public function print($id, Request $request)
  {
    $config['page_title'] = "Detail Pembayaran Pelanggan";
    $config['print_url'] = "/backend/invoicecostumers/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/invoicecostumers', 'title' => "List Detail Pembayaran Pelanggan"],
      ['page' => '#', 'title' => "Detail Detail Pembayaran Pelanggan"],
    ];
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $bank = Bank::findOrFail($request->bank_id);
    $data = InvoiceCostumer::select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))
      ->with(['joborders', 'costumer', 'paymentcostumers', 'joborders.anotherexpedition:id,name', 'joborders.driver:id,name', 'joborders.costumer:id,name', 'joborders.cargo:id,name', 'joborders.transport:id,num_pol', 'joborders.routefrom:id,name', 'joborders.routeto:id,name'])
      ->findOrFail($id);
    return view('backend.invoice.invoicecostumers.print', compact('config', 'page_breadcrumbs', 'data', 'cooperationDefault', 'bank'));
  }

  public function edit($id)
  {
    $config['page_title'] = "Edit Pembayaran Pelanggan";
    $page_breadcrumbs = [
      ['page' => '/backend/invoicecostumers', 'title' => "List Detail Pembayaran Pelanggan"],
      ['page' => '#', 'title' => "Edit Pembayaran Pelanggan"],
    ];
    $data = InvoiceCostumer::select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))
      ->with(['joborders', 'costumer', 'paymentcostumers.coa', 'joborders.anotherexpedition:id,name', 'joborders.driver:id,name', 'joborders.costumer:id,name', 'joborders.cargo:id,name', 'joborders.transport:id,num_pol', 'joborders.routefrom:id,name', 'joborders.routeto:id,name'])
      ->findOrFail($id);
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'invoicecostumers')->sole();
    return view('backend.invoice.invoicecostumers.edit', compact('config', 'page_breadcrumbs', 'data', 'selectCoa'));
  }

  public function update(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'payment.payment' => 'required|integer',
      'payment.date_payment' => 'required|date_format:Y-m-d',
      'coa_id' => 'required|integer',
    ]);
    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $data = InvoiceCostumer::findOrFail($id);
        $costumer = Costumer::findOrFail($data->costumer_id);
        $payment = PaymentCostumer::where('invoice_costumer_id', $data->id)->sum('payment');
        $payment += $request->input('payment.payment');
        $data->update([
          'total_cut' => $request->input('total_cut'),
          'total_piutang' => $request->input('total_piutang'),
          'rest_payment' => $request->input('rest_payment'),
          'total_payment' => $payment,
        ]);

        Journal::where([
          ['table_ref', 'invoicecostumers'],
          ['code_ref', $id],
          ['description', 'like', '%Potongan%']
        ])->delete();

        Journal::where([
          ['table_ref', 'invoicecostumers'],
          ['code_ref', $id],
          ['description', 'like', '%Piutang Pendapatan%']
        ])->delete();

        if (($request->input('total_piutang') ?? 0) > 0) {
          Journal::create([
            'coa_id' => 52,
            'date_journal' => date("Y-m-d"),
            'debit' => 0,
            'kredit' => $request->input('total_piutang'),
            'table_ref' => 'invoicecostumers',
            'code_ref' => $data->id,
            'description' => "Piutang Pendapatan untuk klaim pelanggan $costumer->name dengan No. Invoice: " . $data->prefix . '-' . $data->num_bill
          ]);

          Journal::create([
            'coa_id' => 43,
            'date_journal' => date("Y-m-d"),
            'debit' => $request->input('total_piutang'),
            'kredit' => 0,
            'table_ref' => 'invoicecostumers',
            'code_ref' => $data->id,
            'description' => "Piutang Pendapatan untuk klaim pelanggan $costumer->name dengan No. Invoice: " . $data->prefix . '-' . $data->num_bill
          ]);
        }

        if (($request->input('total_cut') ?? 0) > 0) {
          Journal::create([
            'coa_id' => 46,
            'date_journal' => date("Y-m-d"),
            'debit' => $request->input('total_cut'),
            'kredit' => 0,
            'table_ref' => 'invoicecostumers',
            'code_ref' => $data->id,
            'description' => "Potongan Pendapatan untuk Potongan Klaim dengan No. Invoice: " . $data->prefix . '-' . $data->num_bill
          ]);

          Journal::create([
            'coa_id' => 43,
            'date_journal' => date("Y-m-d"),
            'debit' => 0,
            'kredit' => ($request->input('total_cut') ?? 0),
            'table_ref' => 'invoicecostumers',
            'code_ref' => $data->id,
            'description' => "Potongan Klaim tagihan JO pelanggan $costumer->name dengan No. Invoice: " . $data->prefix . '-' . $data->num_bill
          ]);
        }

        if ($request->input('payment.payment') && $request->input('payment.date_payment')) {
          PaymentCostumer::create([
            'invoice_costumer_id' => $data->id,
            'coa_id' => $request->input('coa_id'),
            'date_payment' => $request->input('payment.date_payment'),
            'payment' => $request->input('payment.payment'),
            'description' => $request->input('payment.description'),
          ]);

          Journal::create([
            'coa_id' => $request->input('coa_id'),
            'date_journal' => $request->input('payment.date_payment'),
            'debit' => $request->input('payment.payment'),
            'kredit' => 0,
            'table_ref' => 'invoicecostumers',
            'code_ref' => $data->id,
            'description' => "Penambahan saldo dari tagihan JO pelanggan $costumer->name  dengan No. Invoice: " . $data->prefix . '-' . $data->num_bill
          ]);

          Journal::create([
            'coa_id' => 43,
            'date_journal' => $request->input('payment.date_payment'),
            'debit' => 0,
            'kredit' => $request->input('payment.payment'),
            'table_ref' => 'invoicecostumers',
            'code_ref' => $data->id,
            'description' => "Pembayaran tagihan JO pelanggan $costumer->name  dengan No. Invoice: " . $data->prefix . '-' . $data->num_bill
          ]);
        }

        if ($request->rest_payment <= -1) {
          return response()->json([
            'status' => 'error',
            'message' => 'Pastikan sisa tagihan tidak negative',
            'redirect' => '/backend/invoicecostumers',
          ]);
          DB::rollBack();
        }

        DB::commit();
        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => '/backend/invoicecostumers',
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

  public function destroy($id)
  {
    $response = response()->json([
      'status' => 'error',
      'message' => 'Data cannot be deleted',
    ]);
    try {
      DB::transaction(function () use ($id) {
        Journal::where('table_ref', 'invoicecostumers')->where('code_ref', $id)->delete();
        JobOrder::where('invoice_costumer_id', $id)->update(['status_payment' => '0']);
        InvoiceCostumer::find($id)->delete();
      });
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been deleted',
      ]);
    } catch (\Throwable $e) {
    }

    return $response;
  }

  public function findbypk(Request $request)
  {
    $data = json_decode($request->data);
    $response = NULL;
    if ($request->data) {
      $result = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])
        ->whereIn('id', $data)->get();

      $response = response()->json([
        'data' => $result,
      ]);
    }
    return $response;
  }

  public function datatabledetail($id)
  {
    $data = JobOrder::where('invoice_costumer_id', $id);

    return Datatables::of($data)->make(true);
  }

  public function taxfee(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'fee_coa_id' => 'integer',
      'tax_coa_id' => 'integer',
    ]);
    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $data = InvoiceCostumer::findOrFail($id);
        $coa = Coa::findOrFail($request->input('coa_id'));
        $type = $request->input('type') == 'tax' ? 'Pajak' : 'Fee';
        if ($request->input('type') == 'tax') {
          $data->update([
            'tax_coa_id' => $request->input('coa_id')
          ]);
        } else {
          $data->update([
            'fee_coa_id' => $request->input('coa_id')
          ]);
        }
        $checksaldo = DB::table('journals')
          ->select(DB::raw('
          IF(`coas`.`normal_balance` = "Db", (SUM(`journals`.`debit`) - SUM(`journals`.`kredit`)),
          (SUM(`journals`.`kredit`) - SUM(`journals`.`debit`))) AS `saldo`
          '))
          ->leftJoin('coas', 'coas.id', '=', 'journals.coa_id')
          ->where('journals.coa_id', $request->coa_id)
          ->groupBy('journals.coa_id')
          ->first();

        if (($checksaldo->saldo ?? FALSE) && $data->total_tax <= $checksaldo->saldo) {
          Journal::create([
            'coa_id' => $request->input('coa_id'),
            'date_journal' => $this->dateNow(),
            'debit' => 0,
            'kredit' => $request->input('type') == 'tax' ? $data->total_tax : $data->total_fee_thanks,
            'table_ref' => 'invoicecostumers',
            'code_ref' => $data->id,
            'description' => "Pembayaran $type"
          ]);

          Journal::create([
            'coa_id' => $request->input('type') == 'tax' ? 53 : 54,
            'date_journal' => $this->dateNow(),
            'debit' => $request->input('type') == 'tax' ? $data->total_tax : $data->total_fee_thanks,
            'kredit' => 0,
            'table_ref' => 'invoicecostumers',
            'code_ref' => $data->id,
            'description' => "Beban $type"
          ]);
          DB::commit();
          $response = response()->json([
            'status' => 'success',
            'message' => 'Data has been saved',
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
}
