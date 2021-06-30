<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\ConfigCoa;
use App\Models\InvoicePurchase;
use App\Models\InvoiceReturPurchase;
use App\Models\Journal;
use App\Models\Prefix;
use App\Models\ReturPurchase;
use App\Models\Setting;
use App\Models\Stock;
use App\Models\SupplierSparepart;
use DataTables;
use DB;
use Illuminate\Http\Request;
use Validator;

class InvoiceReturPurchaseController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:invoicereturpurchases-list|invoicereturpurchases-create|invoicereturpurchases-edit|invoicereturpurchases-delete', ['only' => ['index']]);
    $this->middleware('permission:invoicereturpurchases-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:invoicereturpurchases-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:invoicereturpurchases-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "List Invoice Retur Pembelian";
    $config['page_description'] = "Daftar List Invoice Retur Pembelian";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Retur Pembelian"],
    ];
    if ($request->ajax()) {
      $data = InvoiceReturPurchase::with('supplier:id,name');
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
                    <a href="invoicereturpurchases/' . $row->id . '" class="dropdown-item">Detail Retur</a>
                  </div>
              </div>
            ';
          return $actionBtn;
        })->make(true);

    }
    return view('backend.sparepart.invoicereturpurchases.index', compact('config', 'page_breadcrumbs'));
  }

  public function create()
  {
    $config['page_title'] = "Invoice Retur Pembelian";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Invoice Retur Pembelian"],
    ];
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'invoicereturpurchases')->sole();
    return view('backend.sparepart.invoicereturpurchases.create', compact('config', 'page_breadcrumbs', 'selectCoa'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'invoice_date' => 'required|date_format:Y-m-d',
      'supplier_sparepart_id' => 'required|integer',
      'prefix' => 'required|integer',
      'items.sparepart_id' => 'required|array',
      'items.sparepart_id.*' => 'required|integer',
      'items.qty' => 'required|array',
      'items.qty.*' => 'required|integer',
      'items.price' => 'required|array',
      'items.price.*' => 'required|regex:/^\d+(\.\d{1,2})?$/',
      'coa_id' => 'required|integer'
    ]);


    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $totalPayment = 0;
        $discount = $request->input('discount') ?? 0;
        $discountPO = $request->input('discountPO') ?? 0;
        $items = $request->items;
        $prefix = Prefix::find($request->prefix);
        $supplier = SupplierSparepart::findOrFail($request->input('supplier_sparepart_id'));
        $coa = Coa::findOrFail($request->input('coa_id'));

        foreach ($items['sparepart_id'] as $key => $item):
          $totalPayment += $items['qty'][$key] * $items['price'][$key];
        endforeach;
        if (!($discount <= $discountPO)) {
          return response()->json([
            'status' => 'errors',
            'message' => 'Potong dari diskon tidak boleh melebihi total diskon pembelian',
          ]);
        }

        $invoice = InvoiceReturPurchase::create([
          'supplier_sparepart_id' => $request->input('supplier_sparepart_id'),
          'invoice_purchase_id' => $request->input('invoice_purchase_id'),
          'prefix' => $prefix->name,
          'num_bill' => $request->input('num_bill'),
          'invoice_date' => $request->input('invoice_date'),
          'discount' => $discount,
          'total_payment' => $totalPayment,
        ]);

        if ($discount > 0) {
          Journal::create([
            'coa_id' => 42,
            'date_journal' => $request->input('invoice_date'),
            'debit' => $discount,
            'kredit' => 0,
            'table_ref' => 'invoicereturpurchases',
            'code_ref' => $invoice->id,
            'description' => "Potong dari diskon"
          ]);
        }

        Journal::create([
          'coa_id' => 17,
          'date_journal' => $request->input('invoice_date'),
          'debit' => 0,
          'kredit' => $totalPayment + $discount,
          'table_ref' => 'invoicereturpurchases',
          'code_ref' => $invoice->id,
          'description' => "Retur pembelian barang $supplier->name"
        ]);

        Journal::create([
          'coa_id' => $request->input('coa_id'),
          'date_journal' => $request->input('invoice_date'),
          'debit' => $totalPayment,
          'kredit' => 0,
          'table_ref' => 'invoicereturpurchases',
          'code_ref' => $invoice->id,
          'description' => "Penambahan $coa->name dari retur pembelian"
        ]);

        foreach ($items['sparepart_id'] as $key => $item):
          ReturPurchase::create([
            'invoice_retur_purchase_id' => $invoice->id,
            'sparepart_id' => $items['sparepart_id'][$key],
            'supplier_sparepart_id' => $request->input('supplier_sparepart_id'),
            'qty' => $items['qty'][$key],
            'price' => $items['price'][$key],
          ]);

          $stockSummary = Stock::where('sparepart_id', $items['sparepart_id'][$key])->where('invoice_purchase_id', $request->invoice_purchase_id)->firstOrFail();
          $stockSummary->decrement('qty', $items['qty'][$key]);
        endforeach;

        DB::commit();
        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => '/backend/invoicereturpurchases',
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
    $config['page_title'] = "Detail Invoice Retur Pembelian";
    $config['print_url'] = "/backend/invoicereturpurchases/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/invoicereturpurchases', 'title' => "List Invoice Retur Pembelian"],
      ['page' => '#', 'title' => "Detail Invoice Retur Pembelian"],
    ];
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    $data = InvoiceReturPurchase::with(['returpurchases.sparepart', 'supplier'])->findOrFail($id);
    return view('backend.sparepart.invoicereturpurchases.show', compact('config', 'page_breadcrumbs', 'data', 'profile'));
  }

  public function print($id)
  {
    $config['page_title'] = "Detail Invoice Retur Pembelian";
    $config['print_url'] = "/backend/invoicereturpurchases/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/invoicereturpurchases', 'title' => "List Invoice Retur Pembelian"],
      ['page' => '#', 'title' => "Detail Invoice Retur Pembelian"],
    ];
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    $data = InvoiceReturPurchase::with(['returpurchases.sparepart', 'supplier'])->findOrFail($id);
    return view('backend.sparepart.invoicereturpurchases.print', compact('config', 'page_breadcrumbs', 'data', 'profile'));
  }

  public function select2SparePart(Request $request)
  {
    $page = $request->page;
    $invoice_purchase_id = $request->invoice_purchase_id;
    $resultCount = 10;
    $offset = ($page - 1) * $resultCount;
    $data = Stock::leftJoin('spareparts', 'stocks.sparepart_id', '=', 'spareparts.id')
      ->leftJoin('invoice_purchases', 'invoice_purchases.id', '=', 'stocks.invoice_purchase_id')
      ->leftJoin('purchases', function ($join) {
        $join->on('stocks.invoice_purchase_id', '=', 'purchases.invoice_purchase_id')
          ->on('stocks.sparepart_id', '=', 'purchases.sparepart_id');
      })
      ->selectRaw('spareparts.id as id, spareparts.name as text, stocks.qty as qty, stocks.id as stock_id, purchases.price as price')
      ->where('spareparts.name', 'LIKE', '%' . $request->q . '%')
      ->where('stocks.qty', '>', 0)
      ->when($request->used, function ($query, $used) {
        return $query->whereNotIn('stocks.sparepart_id', $used);
      })
      ->when($invoice_purchase_id, function ($query, $invoice_purchase_id) {
        return $query->where('stocks.invoice_purchase_id', $invoice_purchase_id);
      })
      ->orderBy('spareparts.name')
      ->skip($offset)
      ->take($resultCount)
      ->groupBy('stocks.sparepart_id')
      ->get();

    $count = Stock::leftJoin('spareparts', 'stocks.sparepart_id', '=', 'spareparts.id')
      ->leftJoin('invoice_purchases', 'invoice_purchases.id', '=', 'stocks.invoice_purchase_id')
      ->leftJoin('purchases', function ($join) {
        $join->on('stocks.invoice_purchase_id', '=', 'purchases.invoice_purchase_id')
          ->on('stocks.sparepart_id', '=', 'purchases.sparepart_id');
      })
      ->selectRaw('spareparts.id as id, spareparts.name as text, stocks.qty as qty, stocks.id as stock_id, purchases.price as price')
      ->where('spareparts.name', 'LIKE', '%' . $request->q . '%')
      ->where('stocks.qty', '>', 0)
      ->when($request->used, function ($query, $used) {
        return $query->whereNotIn('stocks.sparepart_id', $used);
      })
      ->when($invoice_purchase_id, function ($query, $invoice_purchase_id) {
        return $query->where('stocks.invoice_purchase_id', $invoice_purchase_id);
      })
      ->orderBy('spareparts.name')
      ->skip($offset)
      ->take($resultCount)
      ->groupBy('stocks.sparepart_id')
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

  public function select2Invoice(Request $request)
  {
    $supplier_id = $request->supplier_id;
    $page = $request->page;
    $resultCount = 10;
    $offset = ($page - 1) * $resultCount;
    $data = InvoicePurchase::join('purchases', 'purchases.invoice_purchase_id', '=', 'invoice_purchases.id')
      ->leftJoin('stocks', function ($join) {
        $join->on('stocks.invoice_purchase_id', '=', 'invoice_purchases.id')
          ->on('purchases.sparepart_id', '=', 'stocks.sparepart_id');
      })
      ->leftJoin('invoice_retur_purchases', 'invoice_purchases.id', '=', 'invoice_retur_purchases.invoice_purchase_id')
      ->select(DB::raw(
        'invoice_purchases.id  AS id, CONCAT(invoice_purchases.prefix, " - ",
         invoice_purchases.num_bill) AS text, SUM(stocks.qty) AS total,
         (IFNULL(invoice_purchases.discount, 0) - IFNULL(invoice_retur_purchases.discount, 0)) as discount
        '))
      ->when($supplier_id, function ($query, $supplier_sparepart_id) {
        return $query->where('invoice_purchases.supplier_sparepart_id', $supplier_sparepart_id);
      })
      ->where('invoice_purchases.num_bill', 'LIKE', '%' . $request->q . '%')
      ->orderBy('invoice_purchases.invoice_date', 'asc')
      ->skip($offset)
      ->take($resultCount)
      ->groupBy('invoice_purchases.id')
      ->having(DB::raw('SUM(stocks.qty)'), '>', '0')
      ->get();

    $count = InvoicePurchase::join('purchases', 'purchases.invoice_purchase_id', '=', 'invoice_purchases.id')
      ->leftJoin('stocks', function ($join) {
        $join->on('stocks.invoice_purchase_id', '=', 'invoice_purchases.id')
          ->on('purchases.sparepart_id', '=', 'stocks.sparepart_id');
      })
      ->select(DB::raw('invoice_purchases.id  AS id, CONCAT(invoice_purchases.prefix, " - ", invoice_purchases.num_bill) AS text, SUM(stocks.qty) AS total'))
      ->when($supplier_id, function ($query, $supplier_sparepart_id) {
        return $query->where('invoice_purchases.supplier_sparepart_id', $supplier_sparepart_id);
      })
      ->where('invoice_purchases.num_bill', 'LIKE', '%' . $request->q . '%')
      ->orderBy('invoice_purchases.invoice_date', 'asc')
      ->skip($offset)
      ->take($resultCount)
      ->groupBy('invoice_purchases.id')
      ->having(DB::raw('SUM(stocks.qty)'), '>', '0')
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

}
