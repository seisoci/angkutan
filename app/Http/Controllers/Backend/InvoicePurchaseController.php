<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\InvoicePurchase;
use App\Models\Prefix;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\Setting;
use App\Models\Stock;
use Illuminate\Http\Request;
use DB;
use Validator;
use DataTables;

class InvoicePurchaseController extends Controller
{
  public function index(Request $request)
  {
    $config['page_title'] = "List Purchase Order";
    $config['page_description'] = "Daftar List Purchase Order";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Purchase Order"],
    ];
    if ($request->ajax()) {
      $data = InvoicePurchase::query()
        ->select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'));
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          $restPayment = $row->rest_payment != 0 ? '<a href="invoicepurchases/' . $row->id . '/edit" class="dropdown-item">Bayar Sisa</a>' : NULL;
          $actionBtn = '
              <div class="dropdown">
                  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fas fa-eye"></i>
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    ' . $restPayment . '
                    <a href="invoicepurchases/' . $row->id . '" class="dropdown-item">Invoice Detail</a>
                  </div>
              </div>
            ';
          return $actionBtn;
        })->make(true);

    }
    return view('backend.sparepart.invoicepurchases.index', compact('config', 'page_breadcrumbs'));
  }

  public function create()
  {
    $config['page_title'] = "Purchase Order";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Purchase Order"],
    ];
    return view('backend.sparepart.invoicepurchases.create', compact('config', 'page_breadcrumbs'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'invoice_date' => 'required|date_format:Y-m-d',
      'due_date' => 'required|date_format:Y-m-d',
      'discount' => 'integer|nullable',
      'method_payment' => 'required|in:cash,credit',
      'supplier_sparepart_id' => 'required|integer',
      'items.sparepart_id' => 'required|array',
      'items.sparepart_id.*' => 'required|integer',
      'items.qty' => 'required|array',
      'items.qty.*' => 'required|integer',
      'items.price' => 'required|array',
      'items.price.*' => 'required|integer',
      'payment.date' => 'required|array',
      'payment.date.*' => 'required|date_format:Y-m-d',
      'payment.payment' => 'required|array',
      'payment.payment.*' => 'required|integer',
    ]);

    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $totalBill = 0;
        $totalPayment = 0;
        $restPayment = 0;
        $items = $request->items;
        $discount = $request->discount ?? 0;
        $payments = $request->payment;
        $prefix = Prefix::find($request->prefix);

        foreach ($items['sparepart_id'] as $key => $item):
          $totalBill += $items['qty'][$key] * $items['price'][$key];
        endforeach;

        foreach ($payments['date'] as $key => $item):
          $totalPayment += $payments['payment'][$key];
        endforeach;

        $restPayment = ($totalBill - $discount) - $totalPayment;

        $invoice = InvoicePurchase::create([
          'supplier_sparepart_id' => $request->input('supplier_sparepart_id'),
          'prefix' => $prefix->name,
          'num_bill' => $request->input('num_bill'),
          'invoice_date' => $request->invoice_date,
          'due_date' => $request->due_date,
          'total_bill' => $totalBill,
          'total_payment' => $totalPayment,
          'rest_payment' => $restPayment,
          'discount' => $discount,
          'method_payment' => $request->method_payment,
          'memo' => $request->input('memo'),
        ]);

        foreach ($items['sparepart_id'] as $key => $item):
          Purchase::create([
            'invoice_purchase_id' => $invoice->id,
            'supplier_sparepart_id' => $request->supplier_sparepart_id,
            'sparepart_id' => $items['sparepart_id'][$key],
            'qty' => $items['qty'][$key],
            'price' => $items['price'][$key],
          ]);
          $stockSummary = Stock::firstOrCreate(
            ['sparepart_id' => $items['sparepart_id'][$key]],
            ['qty' => $items['qty'][$key],]
          );
          if (!$stockSummary->wasRecentlyCreated) {
            $stockSummary->increment('qty', $items['qty'][$key]);
          }
        endforeach;

        foreach ($payments['date'] as $key => $item):
          PurchasePayment::create([
            'invoice_purchase_id' => $invoice->id,
            'date_payment' => $payments['date'][$key],
            'payment' => $payments['payment'][$key],
          ]);
        endforeach;

        DB::commit();

        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => '/backend/invoicepurchases',
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
    $config['page_title'] = "Detail Purchase Order";
    $config['print_url'] = "/backend/invoicepurchases/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/invoicepurchases', 'title' => "List Purchase Order"],
      ['page' => '#', 'title' => "Detail Purchase Order"],
    ];
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    $data = InvoicePurchase::where('id', $id)->select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))->with(['purchases', 'supplier'])->firstOrFail();
    return view('backend.sparepart.invoicepurchases.show', compact('config', 'page_breadcrumbs', 'data', 'profile'));
  }

  public function print($id)
  {
    $config['page_title'] = "Detail Purchase Order";
    $config['print_url'] = "/backend/invoicepurchases/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/invoicepurchases', 'title' => "List Purchase Order"],
      ['page' => '#', 'title' => "Detail Purchase Order"],
    ];
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    $data = InvoicePurchase::where('id', $id)->select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))->with(['purchases', 'supplier'])->firstOrFail();
    return view('backend.sparepart.invoicepurchases.print', compact('config', 'page_breadcrumbs', 'data', 'profile'));
  }

  public function showpayment($id)
  {
    $config['page_title'] = "Detail Purchase Payment";
    $page_breadcrumbs = [
      ['page' => '/backend/drivers', 'title' => "List Purchase Order Payment"],
      ['page' => '#', 'title' => "Detail Purchase Order Payment"],
    ];
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    $data = InvoicePurchase::where('id', $id)->select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))->with(['purchases', 'supplier', 'purchasepayments'])->firstOrFail();
    return view('backend.sparepart.invoicepurchases.showpayment', compact('config', 'page_breadcrumbs', 'data', 'profile'));
  }

  public function edit($id)
  {
    $config['page_title'] = "Detail Purchase Payment";
    $page_breadcrumbs = [
      ['page' => '/backend/drivers', 'title' => "List Purchase Order Payment"],
      ['page' => '#', 'title' => "Detail Purchase Order Payment"],
    ];
    $data = InvoicePurchase::where('id', $id)->select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))->with(['purchases.sparepart:id,name', 'supplier', 'purchasepayments'])->firstOrFail();
    return view('backend.sparepart.invoicepurchases.edit', compact('config', 'page_breadcrumbs', 'data'));
  }

  public function update($id, Request $request)
  {
    $validator = Validator::make($request->all(), [
      'payment.date' => 'required|array',
      'payment.date.*' => 'required|date_format:Y-m-d',
      'payment.payment' => 'required|array',
      'payment.payment.*' => 'required|integer',
    ]);

    $response = response()->json([
      'status' => 'Error !',
      'message' => "Please complete your form",
    ]);

    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $totalPayment = 0;
        $restPayment = 0;
        $payments = $request->payment;

        $data = InvoicePurchase::findOrFail($id);

        foreach ($payments['date'] as $key => $item):
          $totalPayment += $payments['payment'][$key];
          $dataPayment[] = [
            'invoice_purchase_id' => $data->id,
            'date_payment' => $payments['date'][$key],
            'payment' => $payments['payment'][$key],
          ];
        endforeach;

        $restPayment = $data->rest_payment - $totalPayment;
        if ($restPayment <= -1) {
          return response()->json([
            'status' => 'error',
            'message' => 'Pastikan sisa tagihan tidak negative',
            'redirect' => '/backend/invoicepurchases',
          ]);
          DB::rollBack();
        }

        PurchasePayment::insert($dataPayment);

        $data->update([
          'rest_payment' => $restPayment,
        ]);

        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => '/backend/invoicepurchases',
        ]);
        DB::commit();
      } catch (\Throwable $throw) {
        DB::rollBack();
      }
    }
    return $response;
  }

}
