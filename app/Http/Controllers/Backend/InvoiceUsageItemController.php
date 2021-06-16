<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Driver;
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

class InvoiceUsageItemController extends Controller
{
  public function index(Request $request)
  {
    $config['page_title'] = "List Pemakaian Barang";
    $config['page_description'] = "Daftar List Pemakaian Barang";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Pemakaian Barang"],
    ];

    if ($request->ajax()) {
      $type = $request->type;
      $data = InvoiceUsageItem::where('type', 'self');
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          $actionBtn = '
              <div class="dropdown">
                  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fas fa-eye"></i>
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a href="invoiceusageitems/' . $row->id . '" class="dropdown-item">Detail Pemakaian</a>
                  </div>
              </div>
            ';
          return $actionBtn;
        })
        ->make(true);
    }
    return view('backend.invoice.invoiceusageitems.index', compact('config', 'page_breadcrumbs'));
  }

  function create(Request $request)
  {
    $config['page_title'] = "Create Pemakaian Barang";
    $page_breadcrumbs = [
      ['page' => '/backend/invoiceusageitems', 'title' => "List Pemakaian Barang"],
      ['page' => '#', 'title' => "Create Pemakaian Barang"],
    ];

    return view('backend.invoice.invoiceusageitems.create', compact('config', 'page_breadcrumbs'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'invoice_date' => 'required|date_format:Y-m-d',
      'items.sparepart_id' => 'required|array',
      'items.sparepart_id.*' => 'required|integer',
      'items.qty' => 'required|array',
      'items.qty.*' => 'required|integer',
      'prefix' => 'required|integer',
      'driver_id' => 'required|integer',
      'transport_id' => 'required|integer',
      'type' => 'required|in:self,outside',
    ]);
    if ($validator->passes()) {
      try {
        $items = $request->items;
        DB::beginTransaction();
        $prefix = Prefix::findOrFail($request->prefix);
        $invoiceUsageItem = InvoiceUsageItem::create([
          'num_bill' => $request->num_bill,
          'prefix' => $prefix->name,
          'invoice_date' => $request->invoice_date,
          'driver_id' => $request->driver_id,
          'transport_id' => $request->transport_id,
          'type' => $request->type,
          'total_payment' => $request->total_payment,
        ]);
        $driver = Driver::findOrFail($request->driver_id);
        foreach ($items['sparepart_id'] as $key => $item):
          $stock = Stock::findOrFail($items['invoice_purchase_id'][$key]);

          $totalPrice = $items['qty'][$key] * $items['price'][$key];
          UsageItem::create([
            'invoice_usage_item_id' => $invoiceUsageItem->id,
            'sparepart_id' => $items['sparepart_id'][$key],
            'coa_id' => 17,
            'qty' => $items['qty'][$key],
            'price' => $items['price'][$key],
          ]);

          Journal::create([
            'coa_id' => 17,
            'date_journal' => $request->input('invoice_date'),
            'debit' => 0,
            'kredit' => $totalPrice,
            'table_ref' => 'invoiceusageitems',
            'code_ref' => $invoiceUsageItem->id,
            'description' => "Pengurangan stok di persediaan barang untuk supir $driver->name"
          ]);

          Journal::create([
            'coa_id' => 41,
            'date_journal' => $request->input('invoice_date'),
            'debit' => $totalPrice,
            'kredit' => 0,
            'table_ref' => 'invoiceusageitems',
            'code_ref' => $invoiceUsageItem->id,
            'description' => "Beban pemakaian barang supir $driver->name"
          ]);

          $stock->qty = $stock->qty - $items['qty'][$key];
          $stock->save();
        endforeach;
        DB::commit();
        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => '/backend/invoiceusageitems',
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
    $config['page_title'] = "Detail Pemakaian Barang";
    $config['print_url'] = "/backend/invoiceusageitems/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/invoiceusageitems', 'title' => "List Pemakaian Barang"],
      ['page' => '#', 'title' => "Detail Pemakaian Barang"],
    ];
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    $data = InvoiceUsageItem::where('type', 'self')->with(['driver', 'transport', 'usageitem.sparepart:id,name'])->findOrFail($id);
    return view('backend.invoice.invoiceusageitems.show', compact('config', 'page_breadcrumbs', 'profile', 'data'));
  }

  public function print($id)
  {
    $config['page_title'] = "Detail Pemakaian Barang";
    $page_breadcrumbs = [
      ['page' => '/backend/invoiceusageitems', 'title' => "List Pemakaian Barang"],
      ['page' => '#', 'title' => "Detail Pemakaian Barang"],
    ];
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    $data = InvoiceUsageItem::where('type', 'self')->with(['driver', 'transport', 'usageitem.sparepart:id,name'])->findOrFail($id);
    return view('backend.invoice.invoiceusageitems.print', compact('config', 'page_breadcrumbs', 'profile', 'data'));
  }


}
