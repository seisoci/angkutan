<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\InvoicePurchase;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\Setting;
use Illuminate\Http\Request;
use DB;
use Validator;
use DataTables;
class InvoicePurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $config['page_title']       = "List Invoice Purchase Order";
      $config['page_description'] = "Daftar List Invoice Purchase Order";
      $page_breadcrumbs = [
        ['page' => '#','title' => "List Invoice Purchase Order"],
      ];
      if ($request->ajax()) {
        $data = InvoicePurchase::query()
        ->select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'));
        return Datatables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function($row){
            $restPayment = $row->rest_payment != 0 ? '<a href="invoicepurchases/'.$row->id.'/edit" class="dropdown-item">Bayar Sisa</a>' : NULL;
            $actionBtn = '
              <div class="dropdown">
                  <button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fas fa-eye"></i>
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    '.$restPayment.'
                    <a href="invoicepurchases/'.$row->id.'" class="dropdown-item">Invoice Detail</a>
                    <a href="invoicepurchases/'.$row->id.'/showpayment" class="dropdown-item">Invoice Pembayaran</a>
                  </div>
              </div>
            ';
            return $actionBtn;
        })->make(true);

      }
      return view('backend.sparepart.invoicepurchases.index', compact('config', 'page_breadcrumbs'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InvoicePurchase  $invoicePurchase
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      $config['page_title'] = "Detail Invoice Purchase";
      $page_breadcrumbs = [
        ['page' => '/backend/drivers','title' => "List Invoice Purchase Order"],
        ['page' => '#','title' => "Detail Invoice Purchase Order"],
      ];
      $collection = Setting::all();
      $profile = collect($collection)->mapWithKeys(function ($item) {
          return [$item['name'] => $item['value']];
      });
      $data = InvoicePurchase::where('id', $id)->select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))->with(['purchases', 'supplier'])->firstOrFail();
      return view('backend.sparepart.invoicepurchases.show',compact('config', 'page_breadcrumbs', 'data', 'profile'));
    }

    public function showpayment($id)
    {
      $config['page_title'] = "Detail Invoice Purchase Payment";
      $page_breadcrumbs = [
        ['page' => '/backend/drivers','title' => "List Invoice Purchase Order Payment"],
        ['page' => '#','title' => "Detail Invoice Purchase Order Payment"],
      ];
      $collection = Setting::all();
      $profile = collect($collection)->mapWithKeys(function ($item) {
          return [$item['name'] => $item['value']];
      });
      $data = InvoicePurchase::where('id', $id)->select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))->with(['purchases', 'supplier', 'purchasepayments'])->firstOrFail();
      return view('backend.sparepart.invoicepurchases.showpayment',compact('config', 'page_breadcrumbs', 'data', 'profile'));
    }

    public function edit($id){
      $config['page_title'] = "Detail Invoice Purchase Payment";
      $page_breadcrumbs = [
        ['page' => '/backend/drivers','title' => "List Invoice Purchase Order Payment"],
        ['page' => '#','title' => "Detail Invoice Purchase Order Payment"],
      ];
      $data = InvoicePurchase::where('id', $id)->select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))->with(['purchases.sparepart:id,name', 'supplier', 'purchasepayments'])->firstOrFail();
      return view('backend.sparepart.invoicepurchases.edit',compact('config', 'page_breadcrumbs', 'data'));
    }

    public function update($id, Request $request){
      $validator = Validator::make($request->all(), [
        'payment.date'          => 'required|array',
        'payment.date.*'        => 'required|date_format:Y-m-d',
        'payment.payment'       => 'required|array',
        'payment.payment.*'     => 'required|integer',
      ]);

      $response = response()->json([
        'status'    => 'Error !',
        'message'   => "Please complete your form",
      ]);

      if($validator->passes()){
        try {
          DB::beginTransaction();
          $totalPayment = 0;
          $restPayment  = 0;
          $payments     = $request->payment;

          $data = InvoicePurchase::findOrFail($id);

          foreach($payments['date'] as $key => $item):
            $totalPayment += $payments['payment'][$key];
            $dataPayment[] = [
              'invoice_purchase_id' => $data->id,
              'date_payment'        => $payments['date'][$key],
              'payment'             => $payments['payment'][$key],
            ];
          endforeach;

          $restPayment = $data->rest_payment - $totalPayment;
          if($restPayment < -1){
            return response()->json([
              'status'    => 'error',
              'message'   => 'Pastikan sisa tagihan tidak negative',
              'redirect'  => '/backend/invoicepurchases',
            ]);
            DB::rollBack();
          }

          PurchasePayment::insert($dataPayment);

          $data->update([
            'rest_payment'  => $restPayment,
          ]);

          $response = response()->json([
            'status'    => 'success',
            'message'   => 'Data has been saved',
            'redirect'  => '/backend/invoicepurchases',
          ]);
          DB::commit();
        } catch (\Throwable $throw) {
          DB::rollBack();
        }
      }
      return $response;
    }

}
