<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\InvoiceUsageItem;
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
    public function index(Request $request)
    {
      $config['page_title']       = "List Pembelian Barang Diluar";
      $config['page_description'] = "Daftar List Pembelian Barang Diluar";
      $page_breadcrumbs = [
        ['page' => '#','title' => "List Pembelian Barang Diluar"],
      ];

      if ($request->ajax()) {
        $type = $request->type;
        $data = InvoiceUsageItem::where('type', 'outside');
        return DataTables::of($data)
          ->addIndexColumn()
          ->addColumn('action', function($row){
            $actionBtn = '
              <div class="dropdown">
                  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fas fa-eye"></i>
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a href="invoiceusageitemsoutside/'.$row->id.'" class="dropdown-item">Detail Pembelian diluar</a>
                  </div>
              </div>
            ';
              return $actionBtn;
          })
          ->make(true);
      }
      return view('backend.invoice.invoiceusageitemsoutside.index', compact('config', 'page_breadcrumbs'));
    }

    public function create(Request $request)
    {
      $config['page_title'] = "Create Pemakaian Barang";
      $page_breadcrumbs = [
        ['page' => '/backend/invoiceusageitemsoutside','title' => "List Pemakaian Barang"],
        ['page' => '#','title' => "Create Pemakaian Barang"],
      ];

      return view('backend.invoice.invoiceusageitemsoutside.create', compact('config', 'page_breadcrumbs'));
    }

    public function store(Request $request)
    {
      $validator = Validator::make($request->all(), [
        'invoice_date'            => 'required|date_format:Y-m-d',
        'items.name'              => 'required|array',
        'items.name.*'            => 'required|string',
        'items.qty'               => 'required|array',
        'items.qty.*'             => 'required|integer',
        'prefix'                  => 'required|integer',
        'driver_id'               => 'required|integer',
        'transport_id'            => 'required|integer',
        'type'                    => 'required|in:self,outside',
      ]);
      if($validator->passes()){
      try {
        $items   = $request->items;
        DB::beginTransaction();
        $totalPayment = 0;
        $prefix     = Prefix::findOrFail($request->prefix);
        foreach($items['name'] as $key => $item):
          $totalPayment += ($items['price'][$key] * $items['qty'][$key]);
        endforeach;
        $invoiceUsageItem  = InvoiceUsageItem::create([
          'invoice_date'  => $request->invoice_date,
          'num_bill'      => $request->num_bill,
          'prefix'        => $prefix->name,
          'driver_id'     => $request->driver_id,
          'transport_id'  => $request->transport_id,
          'type'          => $request->type,
          'total_payment' => $totalPayment,
        ]);
        foreach($items['name'] as $key => $item):
          $data[] = [
            'invoice_usage_item_id'  => $invoiceUsageItem->id,
            'name'            => $items['name'][$key],
            'qty'             => $items['qty'][$key],
            'price'           => $items['price'][$key],
          ];
        endforeach;
        UsageItem::insert($data);
        DB::commit();
        $response = response()->json([
          'status'    => 'success',
          'message'   => 'Data has been saved',
          'redirect'  => '/backend/invoiceusageitemsoutside',
        ]);
      }catch (\Throwable $throw) {
        DB::rollBack();
        $response = $throw;
      }
      }else{
        $response = response()->json(['error'=>$validator->errors()->all()]);
      }
      return $response;
    }

    public function show($id)
    {
      $config['page_title'] = "Detail Pemakaian Barang";
      $config['print_url'] = "/backend/invoiceusageitemsoutside/$id/print";
      $page_breadcrumbs = [
        ['page' => '/backend/invoiceusageitemsoutside','title' => "List Pemakaian Barang"],
        ['page' => '#','title' => "Detail Pemakaian Barang"],
      ];
      $collection = Setting::all();
      $profile = collect($collection)->mapWithKeys(function ($item) {
        return [$item['name'] => $item['value']];
      });
      $data = InvoiceUsageItem::where('type', 'outside')->with(['driver', 'transport','usageitem.sparepart:id,name'])->findOrFail($id);
      return view('backend.invoice.invoiceusageitemsoutside.show', compact('config', 'page_breadcrumbs', 'profile', 'data'));
    }

    public function print($id){
      $config['page_title'] = "Detail Pemakaian Barang";
      $page_breadcrumbs = [
        ['page' => '/backend/invoiceusageitemsoutside','title' => "List Pemakaian Barang"],
        ['page' => '#','title' => "Detail Pemakaian Barang"],
      ];
      $collection = Setting::all();
      $profile = collect($collection)->mapWithKeys(function ($item) {
        return [$item['name'] => $item['value']];
      });
      $data = InvoiceUsageItem::where('type', 'outside')->with(['driver', 'transport','usageitem.sparepart:id,name'])->findOrFail($id);
      return view('backend.invoice.invoiceusageitemsoutside.print', compact('config', 'page_breadcrumbs', 'profile', 'data'));
    }

}
