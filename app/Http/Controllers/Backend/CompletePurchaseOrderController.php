<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\CompletePurchaseOrder;
use App\Models\ConfigCoa;
use App\Models\Cooperation;
use App\Models\InvoicePurchase;
use App\Models\Journal;
use App\Models\PaymentCompletePurchaseOrder;
use App\Models\Prefix;
use App\Models\SupplierSparepart;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class CompletePurchaseOrderController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:completepurchaseorder-list|completepurchaseorder-create|completepurchaseorder-edit|completepurchaseorder-delete', ['only' => ['index']]);
    $this->middleware('permission:completepurchaseorder-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:completepurchaseorder-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:completepurchaseorder-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Invoice Pelunasan Pembelian Barang";
    $config['page_description'] = "Daftar Invoice Pelunasan Pembelian Barang";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Invoice Pelunasan Pembelian Barang"],
    ];
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'invoicepurchases')->sole();
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
      $data = CompletePurchaseOrder::with(['invoice_purchase']);
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          $restPayment = $row->rest_payment != 0 ? '<a href="completepurchaseorder/' . $row->id . '/edit" class="dropdown-item">Input Pembayaran</a>' : NULL;
          if (Auth::user()->can('delete completepurchaseorder')) {
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
                    ' . $restPayment . '
                    <a href = "completepurchaseorder/' . $row->id . '" class="dropdown-item" > Invoice Detail </a >
                    ' . $deleteBtn . '
                  </div >
              </div >
          ';
          return $actionBtn;
        })
        ->make(true);

    }


    return view('backend.invoice.completepurchaseorder.index', compact('config', 'page_breadcrumbs', 'saldoGroup'));
  }

  public function create(Request $request)
  {
    $config['page_title'] = "Create Invoice Pelunasan Pembelian Barang";
    $config['page_description'] = "Create Invoice Pelunasan Pembelian Barang";
    $page_breadcrumbs = [
      ['page' => '/backend/completepurchaseorder', 'title' => "List Invoice Pelunasan Pembelian Barang"],
      ['page' => '#', 'title' => "Create Invoice Pelunasan Pembelian Barang"],
    ];
    $supplier_sparepart_id = $request['supplier_sparepart_id'];
    $date = $request['date'];

    if ($request->ajax()) {
      $data = InvoicePurchase::with(['supplier'])
        ->where('rest_payment', '>', '0')
        ->when($date, function ($query, $date) {
          $date_format = explode(" / ", $date);
          $date_begin = $date_format[0];
          $date_end = $date_format[1];
          return $query->whereBetween('created_at', [$date_begin, $date_end]);
        })
        ->when($supplier_sparepart_id, function ($query, $supplier_sparepart_id) {
          return $query->where('supplier_sparepart_id', $supplier_sparepart_id);
        });

      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'invoicepurchases')->sole();
    return view('backend.invoice.completepurchaseorder.create', compact('config', 'page_breadcrumbs', 'selectCoa'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'invoice_purchase_id' => 'required|array',
      'invoice_purchase_id.*' => 'required|integer',
      'prefix' => 'required|integer',
      'num_bill' => 'required|integer',
      'supplier_sparepart_id' => 'required|integer',
      'memo' => 'nullable|string',
    ]);

    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $prefix = Prefix::find($request['prefix']);
        $totalBill = InvoicePurchase::whereIn('id', $request['invoice_purchase_id'])->sum('rest_payment');
        $restPayment = $totalBill - $request['payment']['payment'];

        $invoice = CompletePurchaseOrder::create([
          'num_bill' => $request['num_bill'],
          'prefix' => $prefix->name,
          'supplier_sparepart_id' => $request['supplier_sparepart_id'],
          'invoice_date' => $request['invoice_date'],
          'total_bill' => $totalBill,
          'total_payment' => $request['payment']['payment'],
          'rest_payment' => $restPayment,
          'memo' => $request['memo'],
        ]);

        InvoicePurchase::whereIn('id', $request['invoice_purchase_id'])->update(['complete_purchase_order_id' => $invoice->id]);

        $coa = Coa::findOrFail($request['payment']['coa_id']);
        $checksaldo = DB::table('journals')
          ->select(DB::raw('
          IF(`coas`.`normal_balance` = "Db", (SUM(`journals`.`debit`) - SUM(`journals`.`kredit`)),
          (SUM(`journals`.`kredit`) - SUM(`journals`.`debit`))) AS `saldo`
          '))
          ->leftJoin('coas', 'coas.id', '=', 'journals.coa_id')
          ->where('journals.coa_id', $request['payment']['coa_id'])
          ->groupBy('journals.coa_id')
          ->first();

        if (($checksaldo->saldo ?? FALSE) && $request['payment']['payment'] <= $checksaldo->saldo && $request['payment']['coa_id'] != NULL) {
          PaymentCompletePurchaseOrder::create([
            'complete_purchase_order_id' => $invoice->id,
            'date_payment' => $request['payment']['date_payment'],
            'coa_id' => $request['payment']['coa_id'],
            'payment' => $request['payment']['payment'],
            'description' => $request['payment']['description'],
          ]);

          $supplier = SupplierSparepart::findOrFail($request['supplier_sparepart_id']);

          Journal::create([
            'coa_id' => $request['payment']['coa_id'],
            'date_journal' => $request['payment']['date_payment'],
            'debit' => 0,
            'kredit' => $request['payment']['payment'],
            'table_ref' => 'completepurchaseorder',
            'code_ref' => $invoice->id,
            'description' => "Pembayaran barang supplier $supplier->name dengan No. Pelunasan Barang: " . $prefix->name . '-' . $request->num_bill . ""
          ]);

          Journal::create([
            'coa_id' => 15,
            'date_journal' => $request['payment']['date_payment'],
            'debit' => $request['payment']['payment'],
            'kredit' => 0,
            'table_ref' => 'completepurchaseorder',
            'code_ref' => $invoice->id,
            'description' => "Pembayaran utang pembelian barang $supplier->name dengan No. Pelunasan Barang: " . $prefix->name . '-' . $request->num_bill . ""
          ]);

        } else {
          DB::rollBack();
          return response()->json([
            'status' => 'errors',
            'message' => "Saldo $coa->name tidak ada/kurang",
          ]);
        }

        if ($restPayment == 0) {
          InvoicePurchase::whereIn('id', $request['invoice_purchase_id'])->update(['rest_payment' => 0]);
        }

        if ($restPayment <= -1) {
          return response()->json([
            'status' => 'error',
            'message' => 'Pastikan sisa tagihan tidak negative',
          ]);
          DB::rollBack();
        }

        DB::commit();

        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => '/backend/completepurchaseorder',
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
    $config['page_title'] = "Edit Invoice Pelunasan Pembelian Barang";
    $page_breadcrumbs = [
      ['page' => '/backend/completepurchaseorder', 'title' => "List Invoice Pelunasan Pembelian Barang"],
      ['page' => '#', 'title' => "Edit Invoice Pelunasan Pembelian Barang"],
    ];
    $data = CompletePurchaseOrder::with(['invoice_purchase.supplier', 'payment_complete.coa', 'supplier'])->findOrFail($id);
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'invoicepurchases')->sole();
    return view('backend.invoice.completepurchaseorder.edit', compact('config', 'page_breadcrumbs', 'data', 'selectCoa'));
  }

  public function update(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'payment.date_payment' => 'required|date_format:Y-m-d',
      'payment.coa_id' => 'required|integer',
      'payment.payment' => 'required',
    ]);
    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $data = CompletePurchaseOrder::with('supplier')->findOrFail($id);
        $coa = Coa::findOrFail($request['payment']['coa_id']);
        $checksaldo = DB::table('journals')
          ->select(DB::raw('
          IF(`coas`.`normal_balance` = "Db", (SUM(`journals`.`debit`) - SUM(`journals`.`kredit`)),
          (SUM(`journals`.`kredit`) - SUM(`journals`.`debit`))) AS `saldo`
          '))
          ->leftJoin('coas', 'coas.id', '=', 'journals.coa_id')
          ->where('journals.coa_id', $request['payment']['coa_id'])
          ->groupBy('journals.coa_id')
          ->first();
        $payment = PaymentCompletePurchaseOrder::where('complete_purchase_order_id', $data->id)->sum('payment');
        $payment += $request['payment']['payment'];
        $restPayment = $data['total_bill'] - $payment;

        $pluckInvoicePurchase = InvoicePurchase::where('complete_purchase_order_id', $data->id)
          ->get()
          ->pluck('id');

        if (($checksaldo->saldo ?? FALSE) && $request['payment']['payment'] <= $checksaldo->saldo) {

          $data->update([
            'rest_payment' => $restPayment,
            'total_payment' => $payment,
          ]);

          PaymentCompletePurchaseOrder::create([
            'complete_purchase_order_id' => $data->id,
            'date_payment' => $request['payment']['date_payment'],
            'coa_id' => $request['payment']['coa_id'],
            'payment' => $request['payment']['payment'],
            'description' => $request['payment']['description'],
          ]);

          Journal::create([
            'coa_id' => $request['payment']['coa_id'],
            'date_journal' => $request['payment']['date_payment'],
            'debit' => 0,
            'kredit' => $request['payment']['payment'],
            'table_ref' => 'completepurchaseorder',
            'code_ref' => $data->id,
            'description' => "Pembayaran barang supplier " . $data['supplier']['name'] . " dengan No. Pelunasan Barang: " . $data->name . '-' . $request->num_bill . ""
          ]);

          Journal::create([
            'coa_id' => 15,
            'date_journal' => $request['payment']['date_payment'],
            'debit' => $request['payment']['payment'],
            'kredit' => 0,
            'table_ref' => 'completepurchaseorder',
            'code_ref' => $data->id,
            'description' => "Pembayaran utang pembelian barang " . $data['supplier']['name'] . " dengan No. Pelunasan Barang: " . $data->name . '-' . $request->num_bill . ""
          ]);

          if ($request->rest_payment <= -1) {
            DB::rollBack();
            return response()->json([
              'status' => 'error',
              'message' => 'Pastikan sisa tagihan tidak negative',
              'redirect' => '/backend/completepurchaseorder',
            ]);
          }

          if ($restPayment == 0) {
            InvoicePurchase::whereIn('id', $pluckInvoicePurchase)->update(['rest_payment' => 0]);
          }
          DB::commit();
          $response = response()->json([
            'status' => 'success',
            'message' => 'Data has been saved',
            'redirect' => '/backend/completepurchaseorder',
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
    $config['page_title'] = "Invoice Pelunasan Pembelian Barang";
    $config['print_url'] = "/backend/completepurchaseorder/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/completepurchaseorder', 'title' => "List Invoice Pelunasan Pembelian Barang"],
      ['page' => '#', 'title' => "Show Invoice Pelunasan Pembelian Barang"],
    ];
    $cooperationDefault = Cooperation::where('default', '1')->first();


    $data = CompletePurchaseOrder::with(['invoice_purchase.supplier', 'payment_complete.coa', 'supplier'])->findOrFail($id);
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'invoicepurchases')->sole();
    return view('backend.invoice.completepurchaseorder.show', compact('config', 'page_breadcrumbs', 'data', 'selectCoa', 'cooperationDefault'));
  }

  public function print($id)
  {
    $config['page_title'] = "Invoice Pelunasan Pembelian Barang";
    $page_breadcrumbs = [
      ['page' => '/backend/completepurchaseorder', 'title' => "List Invoice Pelunasan Pembelian Barang"],
      ['page' => '#', 'title' => "Show Invoice Pelunasan Pembelian Barang"],
    ];
    $cooperationDefault = Cooperation::where('default', '1')->first();


    $data = CompletePurchaseOrder::with(['invoice_purchase.supplier', 'payment_complete.coa', 'supplier'])->findOrFail($id);
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'invoicepurchases')->sole();
    return view('backend.invoice.completepurchaseorder.print', compact('config', 'page_breadcrumbs', 'data', 'selectCoa', 'cooperationDefault'));
  }

  public function destroy($id)
  {
    try {
      DB::beginTransaction();
      $data = CompletePurchaseOrder::with('invoice_purchase')->findOrFail($id);
      foreach ($data->invoice_purchase as $item):
        $invoicePurchase = InvoicePurchase::find($item['id']);
        $invoicePurchase->update([
          'rest_payment' => $item['total_bill'] - $item['discount']
        ]);
      endforeach;

      Journal::where('table_ref', 'completepurchaseorder')->where('code_ref', $id)->delete();
      if ($data->delete()) {
        DB::commit();
        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been deleted',
        ]);
      }

    } catch (\Throwable $throw) {
      dd($throw);
      DB::rollBack();
      $response = response()->json([
        'status' => 'error',
        'message' => 'Data cant be deleted',
      ]);
    }
    return $response;
  }

  public function findbypk(Request $request)
  {
    $data = json_decode($request->data);
    $response = NULL;
    if ($request->data) {
      $result = InvoicePurchase::with(['supplier'])
        ->whereIn('id', $data)->get();

      $response = response()->json([
        'data' => $result,
      ]);
    }
    return $response;
  }
}
