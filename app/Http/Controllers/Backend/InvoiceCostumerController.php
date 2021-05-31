<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\InvoiceCostumer;
use App\Models\JobOrder;
use App\Models\PaymentCostumer;
use App\Models\Prefix;
use App\Models\Setting;
use DataTables;
use DB;
use Illuminate\Http\Request;
use Validator;

class InvoiceCostumerController extends Controller
{
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
          $actionBtn = '
              <div class="dropdown">
                  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fas fa-eye"></i>
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    ' . $restPayment . '
                    <a href="invoicecostumers/' . $row->id . '" class="dropdown-item">Invoice Detail</a>
                  </div>
              </div>
            ';
          return $actionBtn;
        })
        ->make(true);

    }
    return view('backend.invoice.invoicecostumers.index', compact('config', 'page_breadcrumbs'));
  }

  public function create(Request $request)
  {
    $config['page_title'] = "Create Invoice Pelanggan";
    $config['page_description'] = "Create Invoice Pelanggan";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Create Invoice Pelanggan"],
    ];
    $costumer_id = $request->costumer_id;
    $route_from = $request->route_from;
    $route_to = $request->route_to;
    $route_from = $request->route_from;
    $cargo_id = $request->cargo_id;
    if ($request->ajax()) {
      $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])
        ->where('status_payment', '0')
        ->where('status_cargo', 'selesai')
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
    return view('backend.invoice.invoicecostumers.create', compact('config', 'page_breadcrumbs'));
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
    ]);
    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $prefix = Prefix::findOrFail($request->prefix);
        $data = InvoiceCostumer::create([
          'prefix' => $prefix->name,
          'num_bill' => $request->input('num_bill'),
          'costumer_id' => $request->input('costumer_id'),
          'invoice_date' => $request->input('invoice_date'),
          'due_date' => $request->input('due_date'),
          'total_bill' => $request->input('total_bill'),
          'total_cut' => $request->input('total_cut') ?? 0,
          'total_payment' => $request->input('payment.payment') ?? 0,
          'rest_payment' => $request->input('rest_payment'),
          'memo' => $request->input('memo'),
        ]);
        foreach ($request->job_order_id as $item):
          JobOrder::where('id', $item)->update(['invoice_costumer_id' => $data->id, 'status_payment' => '1']);
        endforeach;

        if ($request->input('payment.payment') && $request->input('payment.date_payment')) {
          PaymentCostumer::create([
            'invoice_costumer_id' => $data->id,
            'date_payment' => $request->input('payment.date_payment'),
            'payment' => $request->input('payment.payment'),
            'description' => $request->input('payment.description'),
          ]);
        }
        if($request->rest_payment <= -1){
          return response()->json([
            'status'    => 'error',
            'message'   => 'Pastikan sisa tagihan tidak negative',
            'redirect'  => '/backend/invoicecostumers',
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

  public function show($id)
  {
    $config['page_title'] = "Detail Pembayaran Pelanggan";
    $config['print_url'] = "/backend/invoicecostumers/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/invoicecostumers', 'title' => "List Detail Pembayaran Pelanggan"],
      ['page' => '#', 'title' => "Detail Detail Pembayaran Pelanggan"],
    ];
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    $data = InvoiceCostumer::select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))
      ->with(['joborders', 'costumer', 'paymentcostumers', 'joborders.anotherexpedition:id,name', 'joborders.driver:id,name', 'joborders.costumer:id,name', 'joborders.cargo:id,name', 'joborders.transport:id,num_pol', 'joborders.routefrom:id,name', 'joborders.routeto:id,name'])
      ->findOrFail($id);
    return view('backend.invoice.invoicecostumers.show', compact('config', 'page_breadcrumbs', 'data', 'profile'));
  }

  public function print($id)
  {
    $config['page_title'] = "Detail Pembayaran Pelanggan";
    $config['print_url'] = "/backend/invoicecostumers/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/invoicecostumers', 'title' => "List Detail Pembayaran Pelanggan"],
      ['page' => '#', 'title' => "Detail Detail Pembayaran Pelanggan"],
    ];
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    $data = InvoiceCostumer::select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))
      ->with(['joborders', 'costumer', 'paymentcostumers', 'joborders.anotherexpedition:id,name', 'joborders.driver:id,name', 'joborders.costumer:id,name', 'joborders.cargo:id,name', 'joborders.transport:id,num_pol', 'joborders.routefrom:id,name', 'joborders.routeto:id,name'])
      ->findOrFail($id);
    return view('backend.invoice.invoicecostumers.print', compact('config', 'page_breadcrumbs', 'data', 'profile'));
  }

  public function edit($id)
  {
    $config['page_title'] = "Detail Pembayaran Pelanggan";
    $page_breadcrumbs = [
      ['page' => '/backend/invoicecostumers', 'title' => "List Detail Pembayaran Pelanggan"],
      ['page' => '#', 'title' => "Detail Detail Pembayaran Pelanggan"],
    ];
    $data = InvoiceCostumer::select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))
      ->with(['joborders', 'costumer', 'paymentcostumers', 'joborders.anotherexpedition:id,name', 'joborders.driver:id,name', 'joborders.costumer:id,name', 'joborders.cargo:id,name', 'joborders.transport:id,num_pol', 'joborders.routefrom:id,name', 'joborders.routeto:id,name'])
      ->findOrFail($id);
    return view('backend.invoice.invoicecostumers.edit', compact('config', 'page_breadcrumbs', 'data'));
  }

  public function update(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'total_cut' => 'required|regex:/^\d+(\.\d{1,2})?$/',
    ]);
    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $data = InvoiceCostumer::findOrFail($id);
        $payment = PaymentCostumer::where('invoice_costumer_id', $data->id)->sum('payment');
        $payment += $request->input('payment.payment');
        $data->update([
          'total_cut' => $request->input('total_cut'),
          'rest_payment' => $request->input('rest_payment'),
          'total_payment' => $payment,
        ]);

        if ($request->input('payment.payment') && $request->input('payment.date_payment')) {
          PaymentCostumer::create([
            'invoice_costumer_id' => $data->id,
            'date_payment' => $request->input('payment.date_payment'),
            'payment' => $request->input('payment.payment'),
            'description' => $request->input('payment.description'),
          ]);
        }

        if($request->rest_payment <= -1){
          return response()->json([
            'status'    => 'error',
            'message'   => 'Pastikan sisa tagihan tidak negative',
            'redirect'  => '/backend/invoicecostumers',
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

  public function findbypk(Request $request)
  {
    $data = json_decode($request->data);
    $response = NULL;
    if ($request->data) {
      $result = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])->whereIn('id', $data)->get();

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
}
