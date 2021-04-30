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

class InvoiceUsageItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $config['page_title']       = "List Pemakaian Barang";
      $config['page_description'] = "Daftar List Pemakaian Barang";
      $page_breadcrumbs = [
        ['page' => '#','title' => "List Pemakaian Barang"],
      ];

      if ($request->ajax()) {
        $type = $request->type;
        $data = InvoiceUsageItem::where('type', 'self');
        return DataTables::of($data)
          ->addIndexColumn()
          ->addColumn('action', function($row){
            $actionBtn = '
              <div class="dropdown">
                  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fas fa-eye"></i>
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a href="invoiceusageitems/'.$row->id.'" class="dropdown-item">Detail Pemakaian</a>
                  </div>
              </div>
            ';
              return $actionBtn;
          })
          ->make(true);
      }
      return view('backend.invoice.invoiceusageitems.index', compact('config', 'page_breadcrumbs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
      $config['page_title'] = "Create Pemakaian Barang";
      $page_breadcrumbs = [
        ['page' => '/backend/invoiceusageitems','title' => "List Pemakaian Barang"],
        ['page' => '#','title' => "Create Pemakaian Barang"],
      ];

      return view('backend.invoice.invoiceusageitems.create', compact('config', 'page_breadcrumbs'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $validator = Validator::make($request->all(), [
        'items.sparepart_id'      => 'required|array',
        'items.sparepart_id.*'    => 'required|integer',
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
        $prefix     = Prefix::findOrFail($request->prefix);
        $invoiceUsageItem  = InvoiceUsageItem::create([
          'num_bill'      => $request->num_bill,
          'prefix'        => $prefix->name,
          'driver_id'     => $request->driver_id,
          'transport_id'  => $request->transport_id,
          'type'          => $request->type,
          'total_payment' => $request->total_payment,
        ]);
        foreach($items['sparepart_id'] as $key => $item):
          $stock = Stock::where('sparepart_id', $items['sparepart_id'][$key])->firstOrFail();
          $data[] = [
            'invoice_usage_item_id'  => $invoiceUsageItem->id,
            'sparepart_id'    => $items['sparepart_id'][$key] ?? NULL,
            'name'            => $items['name'][$key] ?? NULL,
            'qty'             => $items['qty'][$key],
            'price'           => $items['price'][$key] ?? NULL,
          ];
          $stock->qty = $stock->qty - $items['qty'][$key];
          $stock->save();
        endforeach;
        UsageItem::insert($data);
        DB::commit();
        $response = response()->json([
          'status'    => 'success',
          'message'   => 'Data has been saved',
          'redirect'  => '/backend/invoiceusageitems',
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
      $config['print_url'] = "/backend/invoiceusageitems/$id/print";
      $page_breadcrumbs = [
        ['page' => '/backend/invoiceusageitems','title' => "List Pemakaian Barang"],
        ['page' => '#','title' => "Detail Pemakaian Barang"],
      ];
      $collection = Setting::all();
      $profile = collect($collection)->mapWithKeys(function ($item) {
        return [$item['name'] => $item['value']];
      });
      $data = InvoiceUsageItem::where('type', 'self')->with(['driver', 'transport','usageitem.sparepart:id,name'])->findOrFail($id);
      return view('backend.invoice.invoiceusageitems.show', compact('config', 'page_breadcrumbs', 'profile', 'data'));
    }

    public function print($id){
      $config['page_title'] = "Detail Pemakaian Barang";
      $page_breadcrumbs = [
        ['page' => '/backend/invoiceusageitems','title' => "List Pemakaian Barang"],
        ['page' => '#','title' => "Detail Pemakaian Barang"],
      ];
      $collection = Setting::all();
      $profile = collect($collection)->mapWithKeys(function ($item) {
        return [$item['name'] => $item['value']];
      });
      $data = InvoiceUsageItem::where('type', 'self')->with(['driver', 'transport','usageitem.sparepart:id,name'])->findOrFail($id);
      return view('backend.invoice.invoiceusageitems.print', compact('config', 'page_breadcrumbs', 'profile', 'data'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InvoiceUsageItem  $invoiceUsageItem
     * @return \Illuminate\Http\Response
     */
    public function edit(InvoiceUsageItem $invoiceUsageItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\InvoiceUsageItem  $invoiceUsageItem
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, InvoiceUsageItem $invoiceUsageItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InvoiceUsageItem  $invoiceUsageItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(InvoiceUsageItem $invoiceUsageItem)
    {
        //
    }
}
