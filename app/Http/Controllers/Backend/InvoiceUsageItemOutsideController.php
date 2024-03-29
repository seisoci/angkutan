<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ContinousPaper;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\ConfigCoa;
use App\Models\Cooperation;
use App\Models\Driver;
use App\Models\InvoiceUsageItem;
use App\Models\Journal;
use App\Models\Prefix;
use App\Models\Setting;
use App\Models\Stock;
use App\Models\Transport;
use App\Models\UsageItem;
use App\Traits\CarbonTrait;
use DataTables;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class InvoiceUsageItemOutsideController extends Controller
{
  use CarbonTrait;

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

    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'invoiceusageitemsoutside')->sole();

    $saldoGroup = collect($selectCoa->coa)->map(function ($coa) {
      return [
        'name'  => $coa->name ?? NULL,
        'balance' => DB::table('journals')
            ->select(DB::raw('
          IF(`coas`.`normal_balance` = "Db", (SUM(`journals`.`debit`) - SUM(`journals`.`kredit`)),
          (SUM(`journals`.`kredit`) - SUM(`journals`.`debit`))) AS `saldo`
          '))
            ->leftJoin('coas', 'coas.id', '=', 'journals.coa_id')
            ->where('journals.coa_id', $coa->id)
            ->groupBy('journals.coa_id')
            ->first()->saldo ?? 0,
      ];
    });

    if ($request->ajax()) {
      $type = $request->type;
      $data = InvoiceUsageItem::with('transport', 'driver')->where('invoice_usage_items.type', 'outside');
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          $deleteBtn ='<a href="#" data-toggle="modal" data-target="#modalDelete" data-id="' . $row->id . '" class="delete dropdown-item">Delete</a>';
          $actionBtn = '
              <div class="dropdown">
                  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fas fa-eye"></i>
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a href="invoiceusageitemsoutside/' . $row->id . '" class="dropdown-item">Detail Pembelian diluar</a>
                    '.$deleteBtn.'
                  </div>
              </div>
            ';
          return $actionBtn;
        })
        ->make(true);
    }
    return view('backend.invoice.invoiceusageitemsoutside.index', compact('config', 'page_breadcrumbs', 'saldoGroup'));
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
      'driver_id' => 'required|integer',
      'transport_id' => 'required|integer',
      'type' => 'required|in:self,outside',
      'coa_id' => 'required|integer',
    ]);
    if ($validator->passes()) {
      try {
        $items = $request->items;
        DB::beginTransaction();
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
        $driver = Driver::findOrFail($request->input('driver_id'));
        $transport = Transport::findOrFail($request->input('transport_id'));

        $totalPayment = 0;
        foreach ($items['name'] as $key => $item):
          $totalPayment += ($items['price'][$key] * $items['qty'][$key]);
        endforeach;

        if (($checksaldo->saldo ?? FALSE) && $totalPayment <= $checksaldo->saldo) {
          $invoiceUsageItem = InvoiceUsageItem::create([
            'invoice_date' => $request['invoice_date'],
            'num_bill' => $request['num_bill'],
            'prefix' => 'PBL',
            'driver_id' => $request['driver_id'],
            'transport_id' => $request['transport_id'],
            'type' => $request['type'],
            'memo' => $request['memo'],
            'total_payment' => $totalPayment,
          ]);
          foreach ($items['name'] as $key => $item):
            UsageItem::create([
              'invoice_usage_item_id' => $invoiceUsageItem->id,
              'coa_id' => $request->input('coa_id'),
              'name' => $items['name'][$key],
              'qty' => $items['qty'][$key],
              'price' => $items['price'][$key],
              'description' => $items['description'][$key],
            ]);
          endforeach;
          Journal::create([
            'coa_id' => $request->input('coa_id'),
            'date_journal' => $request->input('invoice_date'),
            'debit' => 0,
            'kredit' => $totalPayment,
            'table_ref' => 'invoiceusageitemsoutside',
            'code_ref' => $invoiceUsageItem->id,
            'description' => "Pembelian barang diluar dengan No. Invoice: ".'PBL-'.$request->input('num_bill')." dengan supir $driver->name dan No. Pol: $transport->num_pol"
          ]);

          Journal::create([
            'coa_id' => 40,
            'date_journal' => $request->input('invoice_date'),
            'debit' => $totalPayment,
            'kredit' => 0,
            'table_ref' => 'invoiceusageitemsoutside',
            'code_ref' => $invoiceUsageItem->id,
            'description' => "Beban pembelian barang diluar $coa->name dengan No. Invoice:  " .'PBL-'.$request->input('num_bill')." dengan supir $driver->name dan No. Pol: $transport->num_pol"
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
    $config['print_url'] = route('backend.invoiceusageitemsoutside.print', $id);
    $config['print_dotmatrix_url'] = route('backend.invoiceusageitemsoutside.print-dotmatrix', $id);
    $page_breadcrumbs = [
      ['page' => '/backend/invoiceusageitemsoutside', 'title' => "List Pembelian Barang Diluar"],
      ['page' => '#', 'title' => "Detail Pembelian Barang Diluar"],
    ];
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = InvoiceUsageItem::with([
      'driver',
      'transport',
      'usageitem.sparepart:id,name'
    ])->where('type', 'outside')
      ->findOrFail($id);

    return view('backend.invoice.invoiceusageitemsoutside.show', compact('config', 'page_breadcrumbs', 'cooperationDefault', 'data'));
  }

  public function print($id)
  {
    $config['page_title'] = "Detail Pembelian Barang Diluar";
    $page_breadcrumbs = [
      ['page' => route('backend.invoiceusageitemsoutside.index'), 'title' => "List Pembelian Barang Diluar"],
      ['page' => '#', 'title' => "Detail Pembelian Barang Diluar"],
    ];
    $profile = Cooperation::where('default', '1')->first();
    $data = InvoiceUsageItem::where('type', 'outside')->with(['driver', 'transport', 'usageitem.sparepart:id,name'])->findOrFail($id);
    return view('backend.invoice.invoiceusageitemsoutside.print', compact('config', 'page_breadcrumbs', 'profile', 'data'));
  }

  public function printDotMatrix($id){
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = InvoiceUsageItem::where('type', 'outside')->with(['driver', 'transport', 'usageitem.sparepart:id,name'])->findOrFail($id);

    $result = '';
    $no = 1;
    foreach ($data->usageitem as $val):
      $item[] = [
        'no' => $no++,
        'nama' => $val->name,
        'qty' => $val->qty,
        'price' => number_format($val->price, 0, '.', ','),
        'total_price' => number_format($val->total_price, 0, '.', ',')
      ];
    endforeach;
    $item[] = ['no' => '------------------------------------'];
    $item[] = ['no' => '', '1' => '', '2' => '', 'name' => 'Total', 'nominal' => number_format($data->total_payment, 0, '.', ',')];
    $paper = array(
      'panjang' => 36,
      'baris' => 31,
      'spasi' => 2,
      'column_width' => [
        'header' => [36, 0],
        'table' => [3, 10, 3, 10, 10],
        'footer' => [18, 18]
      ],
      'header' => [
        'left' => [
          strtoupper($cooperationDefault['nickname']),
          $cooperationDefault['address'],
          'PEMBELIAN BARANG DILUAR',
          'No. Refrensi: ' . $data->num_invoice,
          'Nama: ' . $data->driver->name,
          'No Polisi: ' . $data->transport->num_pol,
          'Tgl Pembelian: ' . $this->convertToDate($data->created_at),
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
        'header' => ['No', 'Nama', 'Jml', 'Harga', 'Total'],
        'produk' => $item,
        'footer' => array(
          'catatan' => $data->memo,
        )
      ]
    );
    $printed = new ContinousPaper($paper);
    $result .= $printed->output() . "\n";
    return response($result, 200)->header('Content-Type', 'text/plain');
  }

  public function destroy($id)
  {
    $data = InvoiceUsageitem::findOrFail($id);
    Journal::where('table_ref', 'invoiceusageitemsoutside')->where('code_ref', $data->id)->delete();
    if ($data->delete()) {
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been deleted',
      ]);
    }
    return $response;
  }

}
