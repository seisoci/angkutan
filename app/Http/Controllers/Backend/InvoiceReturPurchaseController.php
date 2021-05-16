<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\InvoiceReturPurchase;
use App\Models\Prefix;
use App\Models\ReturPurchase;
use App\Models\Setting;
use App\Models\Stock;
use DataTables;
use DB;
use Illuminate\Http\Request;
use Validator;

class InvoiceReturPurchaseController extends Controller
{

    public function index(Request $request)
    {
      $config['page_title']       = "List Invoice Retur Pembelian";
      $config['page_description'] = "Daftar List Invoice Retur Pembelian";
      $page_breadcrumbs = [
        ['page' => '#','title' => "List Retur Pembelian"],
      ];
      if ($request->ajax()) {
        $data = InvoiceReturPurchase::with('supplier:id,name');
        return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function($row){
            $restPayment = $row->rest_payment != 0 ? '<a href="invoicepurchases/'.$row->id.'/edit" class="dropdown-item">Bayar Sisa</a>' : NULL;
            $actionBtn = '
              <div class="dropdown">
                  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fas fa-eye"></i>
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    '.$restPayment.'
                    <a href="invoicereturpurchases/'.$row->id.'" class="dropdown-item">Detail Retur</a>
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
      $config['page_title']       ="Invoice Retur Pembelian";
      $page_breadcrumbs = [
        ['page' => '#','title' => "List Invoice Retur Pembelian"],
      ];
      return view('backend.sparepart.invoicereturpurchases.create', compact('config', 'page_breadcrumbs'));
    }

    public function store(Request $request)
    {
      $validator = Validator::make($request->all(), [
        'note_date'             => 'required|date_format:Y-m-d',
        'supplier_sparepart_id' => 'required|integer',
        'prefix'                => 'required|integer',
        'items.sparepart_id'    => 'required|array',
        'items.sparepart_id.*'  => 'required|integer',
        'items.qty'             => 'required|array',
        'items.qty.*'           => 'required|integer',
        'items.price'           => 'required|array',
        'items.price.*'         => 'required|integer',
      ]);

      if($validator->passes()){
        try {
          DB::beginTransaction();
          $totalPayment = 0;
          $items        = $request->items;
          $prefix       = Prefix::find($request->prefix);

          foreach($items['sparepart_id'] as $key => $item):
            $totalPayment += $items['qty'][$key] * $items['price'][$key];
          endforeach;

          $invoice = InvoiceReturPurchase::create([
            'supplier_sparepart_id'        => $request->input('supplier_sparepart_id'),
            'prefix'        => $prefix->name,
            'num_bill'      => $request->input('num_bill'),
            'note_date'     => $request->note_date,
            'total_payment' => $totalPayment,
          ]);

          foreach($items['sparepart_id'] as $key => $item):
            $data[] = [
                'invoice_retur_purchase_id'   => $invoice->id,
                'sparepart_id'          => $items['sparepart_id'][$key],
                'qty'                   => $items['qty'][$key],
                'price'                 => $items['price'][$key],
            ];
            $stockSummary = Stock::firstOrCreate(
                ['sparepart_id' => $items['sparepart_id'][$key] ],
                ['qty' => $items['qty'][$key],]
            );
            if (!$stockSummary->wasRecentlyCreated) {
              $stockSummary->decrement('qty', $items['qty'][$key]);
            }
          endforeach;

          ReturPurchase::insert($data);
//          DB::commit();

          $response = response()->json([
            'status'    => 'success',
            'message'   => 'Data has been saved',
            'redirect'  => '/backend/invoicereturpurchases',
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

    public function show($id)
    {
      $config['page_title'] = "Detail Invoice Retur Pembelian";
      $config['print_url']  = "/backend/invoicereturpurchases/$id/print";
      $page_breadcrumbs = [
        ['page' => '/backend/invoicereturpurchases','title' => "List Invoice Retur Pembelian"],
        ['page' => '#','title' => "Detail Invoice Retur Pembelian"],
      ];
      $collection = Setting::all();
      $profile = collect($collection)->mapWithKeys(function ($item) {
          return [$item['name'] => $item['value']];
      });
      $data = InvoiceReturPurchase::with(['returpurchases.sparepart', 'supplier'])->findOrFail($id);
      return view('backend.sparepart.invoicereturpurchases.show',compact('config', 'page_breadcrumbs', 'data', 'profile'));
    }

    public function print($id){
      $config['page_title'] = "Detail Invoice Retur Pembelian";
      $config['print_url']  = "/backend/invoicereturpurchases/$id/print";
      $page_breadcrumbs = [
        ['page' => '/backend/invoicereturpurchases','title' => "List Invoice Retur Pembelian"],
        ['page' => '#','title' => "Detail Invoice Retur Pembelian"],
      ];
      $collection = Setting::all();
      $profile = collect($collection)->mapWithKeys(function ($item) {
          return [$item['name'] => $item['value']];
      });
      $data = InvoiceReturPurchase::with(['returpurchases.sparepart', 'supplier'])->findOrFail($id);
      return view('backend.sparepart.invoicereturpurchases.print',compact('config', 'page_breadcrumbs', 'data', 'profile'));
    }

}
