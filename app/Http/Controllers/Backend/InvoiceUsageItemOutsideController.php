<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\ConfigCoa;
use App\Models\InvoiceUsageItem;
use App\Models\Journal;
use App\Models\Prefix;
use App\Models\Setting;
use App\Models\Stock;
use App\Models\UsageItem;
use DataTables;
use DB;
use Illuminate\Http\Request;
use Validator;

class InvoiceUsageItemOutsideController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:invoiceusageitemsoutside-list|invoiceusageitemsoutside-create|invoiceusageitemsoutside-edit|invoiceusageitemsoutside-delete', ['only' => ['index']]);
    $this->middleware('permission:invoiceusageitemsoutside-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:invoiceusageitemsoutside-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:invoiceusageitemsoutside-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "List Pembelian Barang Diluar";
    $config['page_description'] = "Daftar List Pembelian Barang Diluar";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Pembelian Barang Diluar"],
    ];

    if ($request->ajax()) {
      $type = $request->type;
      $data = InvoiceUsageItem::where('type', 'outside');
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          $actionBtn = '
              <div class="dropdown">
                  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fas fa-eye"></i>
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a href="invoiceusageitemsoutside/' . $row->id . '" class="dropdown-item">Detail Pembelian diluar</a>
                  </div>
              </div>
            ';
          return $actionBtn;
        })
        ->make(true);
    }
    return view('backend.invoice.invoiceusageitemsoutside.index', compact('config', 'page_breadcrumbs'));
  }

  public function create()
  {
    $config['page_title'] = "Create Pembelian Barang Diluar";
    $page_breadcrumbs = [
      ['page' => '/backend/invoiceusageitemsoutside', 'title' => "List Pembelian Barang Diluar"],
      ['page' => '#', 'title' => "Create Pembelian Barang Diluar"],
    ];
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'invoiceusageitemsoutside')->sole();
    return view('backend.invoice.invoiceusageitemsoutside.create', compact('config', 'page_breadcrumbs', 'selectCoa'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'invoice_date' => 'required|date_format:Y-m-d',
      'items.name' => 'required|array',
      'items.name.*' => 'required|string',
      'items.qty' => 'required|array',
      'items.qty.*' => 'required|integer',
      'prefix' => 'required|integer',
      'driver_id' => 'required|integer',
      'transport_id' => 'required|integer',
      'type' => 'required|in:self,outside',
      'coa_id' => 'required|integer',
    ]);
    if ($validator->passes()) {
      try {
        $items = $request->items;
        DB::beginTransaction();
        $prefix = Prefix::findOrFail($request->prefix);
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

        $totalPayment = 0;
        foreach ($items['name'] as $key => $item):
          $totalPayment += ($items['price'][$key] * $items['qty'][$key]);
        endforeach;

        if (($checksaldo->saldo ?? FALSE) && $totalPayment <= $checksaldo->saldo) {
          $invoiceUsageItem = InvoiceUsageItem::create([
            'invoice_date' => $request->input('invoice_date'),
            'num_bill' => $request->input('num_bill'),
            'prefix' => $prefix->name,
            'driver_id' => $request->input('driver_id'),
            'transport_id' => $request->input('transport_id'),
            'type' => $request->input('type'),
            'total_payment' => $totalPayment,
          ]);
          foreach ($items['name'] as $key => $item):
            UsageItem::create([
              'invoice_usage_item_id' => $invoiceUsageItem->id,
              'coa_id' => $request->input('coa_id'),
              'name' => $items['name'][$key],
              'qty' => $items['qty'][$key],
              'price' => $items['price'][$key],
            ]);
          endforeach;
          Journal::create([
            'coa_id' => $request->input('coa_id'),
            'date_journal' => $request->input('invoice_date'),
            'debit' => 0,
            'kredit' => $totalPayment,
            'table_ref' => 'invoiceldo',
            'code_ref' => $invoiceUsageItem->id,
            'description' => "Pembayaran invoice ldo"
          ]);

          Journal::create([
            'coa_id' => 40,
            'date_journal' => $request->input('invoice_date'),
            'debit' => $totalPayment,
            'kredit' => 0,
            'table_ref' => 'invoiceldo',
            'code_ref' => $invoiceUsageItem->id,
            'description' => "Beban invoice ldo dengan $coa->name"
          ]);
          DB::commit();
          $response = response()->json([
            'status' => 'success',
            'message' => 'Data has been saved',
            'redirect' => '/backend/invoiceusageitemsoutside',
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
    $config['page_title'] = "Detail Pembelian Barang Diluar";
    $config['print_url'] = "/backend/invoiceusageitemsoutside/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/invoiceusageitemsoutside', 'title' => "List Pembelian Barang Diluar"],
      ['page' => '#', 'title' => "Detail Pembelian Barang Diluar"],
    ];
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    $data = InvoiceUsageItem::where('type', 'outside')->with(['driver', 'transport', 'usageitem.sparepart:id,name'])->findOrFail($id);
    return view('backend.invoice.invoiceusageitemsoutside.show', compact('config', 'page_breadcrumbs', 'profile', 'data'));
  }

  public function print($id)
  {
    $config['page_title'] = "Detail Pembelian Barang Diluar";
    $page_breadcrumbs = [
      ['page' => '/backend/invoiceusageitemsoutside', 'title' => "List Pembelian Barang Diluar"],
      ['page' => '#', 'title' => "Detail Pembelian Barang Diluar"],
    ];
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    $data = InvoiceUsageItem::where('type', 'outside')->with(['driver', 'transport', 'usageitem.sparepart:id,name'])->findOrFail($id);
    return view('backend.invoice.invoiceusageitemsoutside.print', compact('config', 'page_breadcrumbs', 'profile', 'data'));
  }

}
