<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\InvoicePurchase;
use App\Models\Prefix;
use App\Models\Purchase;
use App\Models\Stock;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $config['page_title']       ="Purchase Order";
      $page_breadcrumbs = [
        ['page' => '#','title' => "Purchase Order"],
      ];
      return view('backend.sparepart.purchases.index', compact('config', 'page_breadcrumbs'));
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
        'items.qty'       => 'required|array',
        'items.qty.*'     => 'required|integer',
        'items.price'     => 'required|array',
        'items.price.*'   => 'required|integer',
      ]);

      if($validator->passes()){
        try {
          DB::beginTransaction();
          $invoice_date = Carbon::parse()->timezone('Asia/Jakarta')->format('Ymd');
          $invoice_db  = InvoicePurchase::select(DB::raw('MAX(SUBSTRING_INDEX(num_bill, "-", -1)+1) AS `num`'))->first();
          $grandtotal   = 0;
          $items        = $request->items;
          $prefix       = Prefix::find($request->prefix);
          $invoice_num  = $invoice_db['num'] != NULL ? $invoice_db['num'] : 1;
          foreach($items['sparepart_id'] as $key => $item):
            $grandtotal += $items['qty'][$key] * $items['price'][$key];
          endforeach;

          $invoice = InvoicePurchase::create([
            'supplier_sparepart_id'        => $request->input('supplier_sparepart_id'),
            'prefix'      => $prefix->name,
            'num_bill'    => $invoice_date. "-" .$invoice_num,
            'grandtotal'  => $grandtotal,
            'memo'        => $request->input('memo') ?? NULL,
            'description' => $request->input('description') ?? NULL,
          ]);
          dd($invoice);

          foreach($items['sparepart_id'] as $key => $item):
            $data[] = [
                'invoice_purchase_id'   => $invoice->id,
                'supplier_sparepart_id' => $request->supplier_sparepart_id,
                'sparepart_id'          => $items['sparepart_id'][$key],
                'qty'                   => $items['qty'][$key],
                'price'                 => $items['price'][$key],
            ];
            $stockSummary = Stock::firstOrCreate(
                ['sparepart_id' => $items['sparepart_id'][$key] ],
                ['qty' => $items['qty'][$key],]
            );
            if (!$stockSummary->wasRecentlyCreated) {
              $stockSummary->increment('qty', $items['qty'][$key]);
            }
          endforeach;

          Purchase::insert($data);

          DB::commit();
          $response = response()->json([
            'status'    => 'success',
            'message'   => 'Data has been saved',
            'redirect'  => '/backend/purchases',
          ]);
        } catch (\Throwable $throw) {
          DB::rollBack();
          $response = $throw;
        }
      }else{
        $response = response()->json(['error'=>$validator->errors()->all()]);
      }
      return $response;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function show(Purchase $purchase)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function edit(Purchase $purchase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Purchase $purchase)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function destroy(Purchase $purchase)
    {
        //
    }
}
