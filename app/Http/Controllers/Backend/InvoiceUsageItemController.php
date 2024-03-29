<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ContinousPaper;
use App\Http\Controllers\Controller;
use App\Models\Cooperation;
use App\Models\Driver;
use App\Models\InvoiceUsageItem;
use App\Models\Journal;
use App\Models\Stock;
use App\Models\Transport;
use App\Models\UsageItem;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class InvoiceUsageItemController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:invoiceusageitems-list|invoiceusageitems-create|invoiceusageitems-edit|invoiceusageitems-delete', ['only' => ['index']]);
    $this->middleware('permission:invoiceusageitems-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:invoiceusageitems-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:invoiceusageitems-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "List Pemakaian Barang";
    $config['page_description'] = "Daftar List Pemakaian Barang";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Pemakaian Barang"],
    ];

    if ($request->ajax()) {
      $data = InvoiceUsageItem::with('driver', 'transport')->where('invoice_usage_items.type', 'self');
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          $deleteBtn = '<a href="#" data-toggle="modal" data-target="#modalDelete" data-id="' . $row->id . '" class="delete dropdown-item">Delete</a>';
          $actionBtn = '
              <div class="dropdown">
                  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fas fa-eye"></i>
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a href="invoiceusageitems/' . $row->id . '" class="dropdown-item">Detail Pemakaian</a>
                    ' . $deleteBtn . '
                  </div>
              </div>
            ';
          return $actionBtn;
        })
        ->make(true);
    }
    return view('backend.invoice.invoiceusageitems.index', compact('config', 'page_breadcrumbs'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'invoice_date' => 'required|date_format:Y-m-d',
      'items.sparepart_id' => 'required|array',
      'items.sparepart_id.*' => 'required|integer',
      'items.qty' => 'required|array',
      'items.qty.*' => 'required|integer',
      'driver_id' => 'required|integer',
      'transport_id' => 'required|integer',
      'type' => 'required|in:self,outside',
    ]);
    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $grandTotal = 0;
        $items = $request->items;
        foreach ($items['sparepart_id'] as $key => $item):
          $grandTotal += $items['qty'][$key] * $items['price'][$key];
        endforeach;

        $request->request->add([
          'prefix' => 'PBPKM',
          'total_payment' => $grandTotal
        ]);

        $invoiceUsageItem = InvoiceUsageItem::create($request->all());

        $driver = Driver::findOrFail($request->input('driver_id'));
        $transport = Transport::findOrFail($request->input('transport_id'));
        foreach ($items['sparepart_id'] as $key => $item):
          $stock = Stock::findOrFail($items['stock_id'][$key]);
          $totalPrice = $items['qty'][$key] * $items['price'][$key];
          UsageItem::create([
            'invoice_usage_item_id' => $invoiceUsageItem->id,
            'sparepart_id' => $items['sparepart_id'][$key],
            'invoice_purchase_id' => $items['invoice_purchase_id'][$key],
            'coa_id' => 17,
            'qty' => $items['qty'][$key],
            'price' => $items['price'][$key],
            'description' => $items['description'][$key],
          ]);

          Journal::create([
            'coa_id' => 17,
            'date_journal' => $request->input('invoice_date'),
            'debit' => 0,
            'kredit' => $totalPrice,
            'table_ref' => 'invoiceusageitems',
            'code_ref' => $invoiceUsageItem->id,
            'description' => "Pengurangan stok di persediaan barang dengan No. Invoice: " . 'PBPKM-' . $request->input('num_bill') . " untuk supir $driver->name dengan No. Pol: $transport->num_pol"
          ]);

          Journal::create([
            'coa_id' => 41,
            'date_journal' => $request->input('invoice_date'),
            'debit' => $totalPrice,
            'kredit' => 0,
            'table_ref' => 'invoiceusageitems',
            'code_ref' => $invoiceUsageItem->id,
            'description' => "Beban pemakaian barang dengan No. Invoice: " . 'PBPKM-' . $request->input('num_bill') . " untuk supir $driver->name  dengan No. Pol: $transport->num_pol"
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

  function create(Request $request)
  {
    $config['page_title'] = "Create Pemakaian Barang";
    $page_breadcrumbs = [
      ['page' => '/backend/invoiceusageitems', 'title' => "List Pemakaian Barang"],
      ['page' => '#', 'title' => "Create Pemakaian Barang"],
    ];

    return view('backend.invoice.invoiceusageitems.create', compact('config', 'page_breadcrumbs'));
  }

  public function show($id)
  {
    $config['page_title'] = "Detail Pemakaian Barang";
    $config['print_url'] = route('backend.invoiceusageitems.print', $id);
    $config['print_dotmatrix_url'] = route('backend.invoiceusageitems.print-dotmatrix', $id);
    $page_breadcrumbs = [
      ['page' => '/backend/invoiceusageitems', 'title' => "List Pemakaian Barang"],
      ['page' => '#', 'title' => "Detail Pemakaian Barang"],
    ];
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = InvoiceUsageItem::with([
      'driver',
      'transport',
      'usageitem.sparepart:id,name',
      'usageitem.invoicepurchase:id,supplier_sparepart_id',
      'usageitem.invoicepurchase.supplier'
    ])->where('type', 'self')
      ->findOrFail($id);

    return view('backend.invoice.invoiceusageitems.show', compact('config', 'page_breadcrumbs', 'cooperationDefault', 'data'));
  }

  public function print($id)
  {
    $config['page_title'] = "Detail Pemakaian Barang";
    $page_breadcrumbs = [
      ['page' => '/backend/invoiceusageitems', 'title' => "List Pemakaian Barang"],
      ['page' => '#', 'title' => "Detail Pemakaian Barang"],
    ];
    $profile = Cooperation::where('default', '1')->first();
    $data = InvoiceUsageItem::where('type', 'self')->with(['driver', 'transport', 'usageitem.sparepart:id,name', 'usageitem.invoicepurchase:id,supplier_sparepart_id', 'usageitem.invoicepurchase.supplier'])->findOrFail($id);

    return view('backend.invoice.invoiceusageitems.print', compact('config', 'page_breadcrumbs', 'profile', 'data'));
  }

  public function printDotMatrix($id)
  {
    $cooperationDefault = Cooperation::where('default', '1')->first();
    $data = InvoiceUsageItem::where('type', 'self')->with(['driver', 'transport', 'usageitem.sparepart:id,name', 'usageitem.invoicepurchase:id,supplier_sparepart_id', 'usageitem.invoicepurchase.supplier'])->findOrFail($id);
    $result = '';
    $no = 1;
    foreach ($data->usageitem as $val):
      $item[] = ['no' => $no++, 'nama' => $val->sparepart->name, 'supplier' => $val->invoicepurchase->supplier->name, 'nominal' => $val->qty];
    endforeach;

    $paper = array(
      'panjang' => 35,
      'baris' => 31,
      'spasi' => 2,
      'column_width' => [
        'header' => [35, 0],
        'table' => [3, 20, 8, 4],
        'footer' => [18, 17]
      ],
      'header' => [
        'left' => [
          strtoupper($cooperationDefault['nickname']),
          $cooperationDefault['address'],
          'PEMAKAIAN BARANG',
          'No. Pemakaian: ' . $data->num_invoice,
          'Nama: ' . $data->driver->name,
          'No Polisi: ' . $data->transport->num_pol,
          'Tgl Pengambilan: ' . $this->convertToDate($data->created_at),
        ],
        'right' => [
          ''
        ]
      ],
      'footer' => [
        ['align' => 'center', 'data' => ['Mengetahui', 'Mengetahui']],
        ['align' => 'center', 'data' => ['', '']],
        ['align' => 'center', 'data' => ['', '']],
        ['align' => 'center', 'data' => [Auth::user()->name, $data->driver->name]],
      ],
      'table' => [
        'header' => ['No', 'Nama Barang', 'Supplier', 'Jml'],
        'produk' => $item,
        'footer' => array(
          'catatan' => ''
        )
      ]
    );
    $printed = new ContinousPaper($paper);
    $result .= $printed->output() . "\n";
    return response($result, 200)->header('Content-Type', 'text/plain');
  }

  public function destroy($id)
  {
    try {
      DB::beginTransaction();
      $data = InvoiceUsageItem::with('usageitem')->findOrFail($id);
      foreach ($data->usageitem as $item):
        $stock = Stock::where([
          ['invoice_purchase_id', $item['invoice_purchase_id']],
          ['sparepart_id', $item['sparepart_id']]
        ])->first();

        $stock->update([
          'qty' => $stock->qty + $item['qty']
        ]);
        $stock->save();
      endforeach;

      Journal::where('table_ref', 'invoiceusageitems')->where('code_ref', $data->id)->delete();
      if ($data->delete()) {
        DB::commit();
        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been deleted',
        ]);
      }

    } catch (\Throwable $throw) {
      DB::rollBack();
      $response = $throw;
    }
    return $response;
  }
}
