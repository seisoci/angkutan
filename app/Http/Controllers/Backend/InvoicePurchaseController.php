<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\ConfigCoa;
use App\Models\InvoicePurchase;
use App\Models\Journal;
use App\Models\Prefix;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\Setting;
use App\Models\Stock;
use App\Models\SupplierSparepart;
use DataTables;
use DB;
use Illuminate\Http\Request;
use Validator;

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
    $config['page_title'] = "Create Purchase Order";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Create Purchase Order"],
    ];
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'invoicepurchases')->sole();
    return view('backend.sparepart.invoicepurchases.create', compact('config', 'page_breadcrumbs', 'selectCoa'));
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
      'payment.coa' => 'required|array',
      'payment.coa.*' => 'required|integer',
    ]);

    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $totalBill = 0;
        $totalPayment = 0;
        $items = $request->items;
        $discount = $request->discount ?? 0;
        $payments = $request->payment;
        $prefix = Prefix::find($request->prefix);
        $supplier = SupplierSparepart::findOrFail($request->supplier_sparepart_id);
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
          Stock::create([
            'sparepart_id' => $items['sparepart_id'][$key],
            'invoice_purchase_id' => $invoice->id,
            'qty' => $items['qty'][$key]
          ]);
//          $stockSummary = Stock::firstOrCreate(
//            ['sparepart_id' => $items['sparepart_id'][$key]],
//            ['qty' => $items['qty'][$key]]
//          );
//          if (!$stockSummary->wasRecentlyCreated) {
//            $stockSummary->increment('qty', $items['qty'][$key]);
//          }
        endforeach;

        foreach ($payments['date'] as $key => $item):
          $coa = Coa::findOrFail($payments['coa'][$key]);
          $checksaldo = DB::table('journals')
            ->select(DB::raw('
          IF(`coas`.`normal_balance` = "Db", (SUM(`journals`.`debit`) - SUM(`journals`.`kredit`)),
          (SUM(`journals`.`kredit`) - SUM(`journals`.`debit`))) AS `saldo`
          '))
            ->leftJoin('coas', 'coas.id', '=', 'journals.coa_id')
            ->where('journals.coa_id', $payments['coa'][$key])
            ->groupBy('journals.coa_id')
            ->first();

          if (($checksaldo->saldo ?? FALSE) && $payments['payment'][$key] <= $checksaldo->saldo) {
            PurchasePayment::create([
              'invoice_purchase_id' => $invoice->id,
              'date_payment' => $payments['date'][$key],
              'coa_id' => $payments['coa'][$key],
              'payment' => $payments['payment'][$key],
            ]);

            Journal::create([
              'coa_id' => $payments['coa'][$key],
              'date_journal' => $payments['date'][$key],
              'debit' => 0,
              'kredit' => $payments['payment'][$key],
              'table_ref' => 'invoicepurchases',
              'code_ref' => $invoice->id,
              'description' => "Pembayaran barang supplier $supplier->name"
            ]);

          } else {
            DB::rollBack();
            return response()->json([
              'status' => 'errors',
              'message' => "Saldo $coa->name tidak ada/kurang",
            ]);
          }
        endforeach;

        if ($restPayment <= -1) {
          return response()->json([
            'status' => 'error',
            'message' => 'Pastikan sisa tagihan tidak negative',
            'redirect' => '/backend/invoicepurchases',
          ]);
          DB::rollBack();
        } elseif ($restPayment > 0) {
          Journal::create([
            'coa_id' => 15,
            'date_journal' => $request->input('invoice_date'),
            'debit' => 0,
            'kredit' => $restPayment,
            'table_ref' => 'invoicepurchases',
            'code_ref' => $invoice->id,
            'description' => "Utang pembelian barang $supplier->name"
          ]);
        }

        if (!($discount <= 0)) {
          Journal::create([
            'coa_id' => 42,
            'date_journal' => $request->input('invoice_date'),
            'debit' => 0,
            'kredit' => $discount,
            'table_ref' => 'invoicepurchases',
            'code_ref' => $invoice->id,
            'description' => "Diskon Pembelian barang barang $supplier->name"
          ]);
        }

        Journal::create([
          'coa_id' => 17,
          'date_journal' => $request->input('invoice_date'),
          'debit' => $totalBill,
          'kredit' => 0,
          'table_ref' => 'invoicepurchases',
          'code_ref' => $invoice->id,
          'description' => "Penambahan persediaan barang $supplier->name"
        ]);

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
    $config['page_title'] = "Edit Purchase Payment";
    $page_breadcrumbs = [
      ['page' => '/backend/drivers', 'title' => "List Purchase Order Payment"],
      ['page' => '#', 'title' => "Detail Purchase Order Payment"],
    ];
    $data = InvoicePurchase::where('id', $id)
      ->select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))
      ->with(['purchases.sparepart:id,name', 'supplier', 'purchasepayments.coa'])
      ->firstOrFail();
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'invoicepurchases')->sole();
    return view('backend.sparepart.invoicepurchases.edit', compact('config', 'page_breadcrumbs', 'data', 'selectCoa'));
  }

  public function update($id, Request $request)
  {
    $validator = Validator::make($request->all(), [
      'payment.date' => 'required|array',
      'payment.date.*' => 'required|date_format:Y-m-d',
      'payment.payment' => 'required|array',
      'payment.payment.*' => 'required|integer',
      'payment.coa' => 'required|array',
      'payment.coa.*' => 'required|integer',
    ]);

    $response = response()->json([
      'status' => 'Error !',
      'message' => "Please complete your form",
    ]);

    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $payments = $request->payment;
        $data = InvoicePurchase::findOrFail($id);
        $supplier = SupplierSparepart::findOrFail($data->supplier_sparepart_id);

        $totalPayment = 0;
        foreach ($payments['date'] as $key => $item):
          $totalPayment += $payments['payment'][$key];
          $coa = Coa::findOrFail($payments['coa'][$key]);
          $checksaldo = DB::table('journals')
            ->select(DB::raw('
          IF(`coas`.`normal_balance` = "Db", (SUM(`journals`.`debit`) - SUM(`journals`.`kredit`)),
          (SUM(`journals`.`kredit`) - SUM(`journals`.`debit`))) AS `saldo`
          '))
            ->leftJoin('coas', 'coas.id', '=', 'journals.coa_id')
            ->where('journals.coa_id', $payments['coa'][$key])
            ->groupBy('journals.coa_id')
            ->first();

          if (($checksaldo->saldo ?? FALSE) && $payments['payment'][$key] <= $checksaldo->saldo) {
            PurchasePayment::create([
              'invoice_purchase_id' => $data->id,
              'date_payment' => $payments['date'][$key],
              'coa_id' => $payments['coa'][$key],
              'payment' => $payments['payment'][$key],
            ]);

            Journal::create([
              'coa_id' => $payments['coa'][$key],
              'date_journal' => $payments['date'][$key],
              'debit' => 0,
              'kredit' => $payments['payment'][$key],
              'table_ref' => 'invoicepurchases',
              'code_ref' => $data->id,
              'description' => "Pembayaran barang supplier $supplier->name"
            ]);

            Journal::create([
              'coa_id' => 15,
              'date_journal' => $payments['date'][$key],
              'debit' => $payments['payment'][$key],
              'kredit' => 0,
              'table_ref' => 'invoicepurchases',
              'code_ref' => $data->id,
              'description' => "Pembayaran utang pembelian barang $supplier->name"
            ]);
          } else {
            DB::rollBack();
            return response()->json([
              'status' => 'errors',
              'message' => "Saldo $coa->name tidak ada/kurang",
            ]);
          }
        endforeach;

        $restPayment = $data->rest_payment - $totalPayment;
        $data->update([
          'rest_payment' => $restPayment,
        ]);

        if ($restPayment <= -1) {
          return response()->json([
            'status' => 'error',
            'message' => 'Pastikan sisa tagihan tidak negative',
            'redirect' => '/backend/invoicepurchases',
          ]);
          DB::rollBack();
        }

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
