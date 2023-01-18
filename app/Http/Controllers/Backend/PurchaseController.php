<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\InvoicePurchase;
use App\Models\Prefix;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\Stock;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;

class PurchaseController extends Controller
{
    public function index()
    {
      $config['page_title']       ="Purchase Order";
      $page_breadcrumbs = [
        ['page' => '#','title' => "Purchase Order"],
      ];
      return view('backend.sparepart.purchases.index', compact('config', 'page_breadcrumbs'));
    }

    public function store(Request $request)
    {
      $validator = Validator::make($request->all(), [
        'invoice_date'          => 'required|date_format:Y-m-d',
        'due_date'              => 'required|date_format:Y-m-d',
        'discount'              => 'integer|nullable',
        'method_payment'        => 'required|in:cash,credit',
        'supplier_sparepart_id' => 'required|integer',
        'items.sparepart_id'    => 'required|array',
        'items.sparepart_id.*'  => 'required|integer',
        'items.qty'             => 'required|array',
        'items.qty.*'           => 'required|integer',
        'items.price'           => 'required|array',
        'items.price.*'         => 'required|integer',
        'payment.date'          => 'required|array',
        'payment.date.*'        => 'required|date_format:Y-m-d',
        'payment.payment'       => 'required|array',
        'payment.payment.*'     => 'required|integer',
      ]);

      if($validator->passes()){
        try {
          DB::beginTransaction();
          $totalBill    = 0;
          $totalPayment = 0;
          $restPayment  = 0;
          $items        = $request->items;
          $discount     = $request->discount ?? 0;
          $payments     = $request->payment;
          $prefix       = Prefix::find($request->prefix);

          foreach($items['sparepart_id'] as $key => $item):
            $totalBill += $items['qty'][$key] * $items['price'][$key];
          endforeach;

          foreach($payments['date'] as $key => $item):
            $totalPayment += $payments['payment'][$key];
          endforeach;

          $restPayment = ($totalBill - $discount) - $totalPayment;

          $invoice = InvoicePurchase::create([
            'supplier_sparepart_id'        => $request->input('supplier_sparepart_id'),
            'prefix'        => $prefix->name,
            'num_bill'      => $request->input('num_bill'),
            'invoice_date'  => $request->invoice_date,
            'due_date'      => $request->due_date,
            'total_bill'    => $totalBill,
            'total_payment' => $totalPayment,
            'rest_payment'  => $restPayment,
            'discount'      => $discount,
            'method_payment'=> $request->method_payment,
            'memo'          => $request->input('memo'),
          ]);

          foreach($items['sparepart_id'] as $key => $item):
            $data= [
                'invoice_purchase_id'   => $invoice->id,
                'supplier_sparepart_id' => $request->supplier_sparepart_id,
                'sparepart_id'          => $items['sparepart_id'][$key],
                'qty'                   => $items['qty'][$key],
                'price'                 => $items['price'][$key],
            ];
            Purchase::create($data);
            $stockSummary = Stock::firstOrCreate(
                ['sparepart_id' => $items['sparepart_id'][$key] ],
                ['qty' => $items['qty'][$key],]
            );
            if (!$stockSummary->wasRecentlyCreated) {
              $stockSummary->increment('qty', $items['qty'][$key]);
            }
          endforeach;

          foreach($payments['date'] as $key => $item):
            $dataPayment[] = [
              'invoice_purchase_id' => $invoice->id,
              'date_payment'        => $payments['date'][$key],
              'payment'             => $payments['payment'][$key],
            ];
          endforeach;

          count($dataPayment) > 0 ? PurchasePayment::insert($dataPayment) : NULL;
          DB::commit();

          $response = response()->json([
            'status'    => 'success',
            'message'   => 'Data has been saved',
            'redirect'  => '/backend/invoicepurchases',
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

}
