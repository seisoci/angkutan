<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ContinousPaperLong;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\ConfigCoa;
use App\Models\Cooperation;
use App\Models\InvoicePurchase;
use App\Models\InvoiceReturPurchase;
use App\Models\Journal;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\Stock;
use App\Models\SupplierSparepart;
use App\Models\UsageItem;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class InvoicePurchaseController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:invoicepurchases-list|invoicepurchases-create|invoicepurchases-edit|invoicepurchases-delete', ['only' => ['index']]);
    $this->middleware('permission:invoicepurchases-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:invoicepurchases-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:invoicepurchases-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "List Purchase Order";
    $config['page_description'] = "Daftar List Purchase Order";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Purchase Order"],
    ];

    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'invoicepurchases')->sole();

    $saldoGroup = collect($selectCoa->coa)->map(function ($coa) {
      return [
        'name' => $coa->name ?? NULL,
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
      $invoiceDate = $request['invoice_date'];
      $supplierSparepartId = $request['supplier_sparepart_id'];
      $status = $request['status'];
      $data = InvoicePurchase::with('supplier')
        ->select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))
        ->when($invoiceDate, function ($query, $date) {
          $date_format = explode(" / ", $date);
          $date_begin = $date_format[0];
          $date_end = $date_format[1];
          return $query->whereBetween('invoice_date', [$date_begin, $date_end]);
        })
        ->when($supplierSparepartId, function ($query, $supplierSparepartId) {
          return $query->where('supplier_sparepart_id', $supplierSparepartId);
        })
        ->when($status, function ($query, $status) {
          if ($status == 'lunas') {
            return $query->where('rest_payment', 0);
          } elseif ($status == 'belum_lunas') {
            return $query->where('rest_payment', '>', $status);
          }
        });

      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          $restPayment = NULL;
          $usageItem = UsageItem::where('invoice_purchase_id', $row->id)->exists();
          $completePO = InvoicePurchase::where('id', $row->id)->whereNotNull('complete_purchase_order_id')->first();
          $invoiceReturItem = InvoiceReturPurchase::where('invoice_purchase_id', $row->id)->exists();
          $deleteBtn = $usageItem || $invoiceReturItem || isset($completePO) ? NULL : '<a href="#" data-toggle="modal" data-target="#modalDelete" data-id="' . $row->id . '" class="delete dropdown-item">Delete</a>';
          return '
              <div class="dropdown">
                  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fas fa-eye"></i>
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a href="invoicepurchases/' . $row->id . '" class="dropdown-item">Invoice Detail</a>
                    ' . $restPayment . $deleteBtn . '
                  </div>
              </div>
            ';
        })->make(true);
    }
    return view('backend.sparepart.invoicepurchases.index', compact('config', 'page_breadcrumbs', 'saldoGroup'));
  }

  public function create()
  {
    $config['page_title'] = "Create Purchase Order";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Create Purchase Order"],
    ];
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'invoicepurchases')->sole();
    return view('backend.sparepart.invoicepurchases.create', compact('config', 'page_breadcrumbs', 'selectCoa'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'invoice_date' => 'required|date_format:Y-m-d',
      'due_date' => 'required|date_format:Y-m-d',
      'discount' => 'integer|nullable',
      'method_payment' => 'required|in:cash,credit',
      'supplier_sparepart_id' => 'required|integer',
      'items.sparepart_id' => 'required|array',
      'items.sparepart_id.*' => 'required|integer',
      'items.qty' => 'required|array',
      'items.qty.*' => 'required|integer',
      'items.price' => 'required|array',
      'items.price.*' => 'required|integer',
    ]);

    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $totalBill = 0;
        $items = $request['items'];
        $supplier = SupplierSparepart::findOrFail($request->supplier_sparepart_id);
        foreach ($items['sparepart_id'] as $key => $item):
          $totalBill += $items['qty'][$key] * $items['price'][$key];
        endforeach;
        $restPayment = $totalBill - $request['discount'];

        $request->request->add([
          'prefix' => 'PB',
          'rest_payment' => $restPayment,
          'total_bill' => $totalBill,
          'discount' => $request['discount'] ?? 0,
        ]);

        $invoice = InvoicePurchase::create($request->all());

        foreach ($items['sparepart_id'] as $key => $item):
          Purchase::create([
            'invoice_purchase_id' => $invoice->id,
            'supplier_sparepart_id' => $request->supplier_sparepart_id,
            'sparepart_id' => $items['sparepart_id'][$key],
            'qty' => $items['qty'][$key],
            'price' => $items['price'][$key],
            'description' => $items['description'][$key],
          ]);
          Stock::create([
            'sparepart_id' => $items['sparepart_id'][$key],
            'invoice_purchase_id' => $invoice['id'],
            'qty' => $items['qty'][$key]
          ]);

        endforeach;

        if ($restPayment <= -1) {
          DB::rollBack();
          return response()->json([
            'status' => 'error',
            'message' => 'Pastikan sisa tagihan tidak negative',
            'redirect' => '/backend/invoicepurchases',
          ]);
        } elseif ($restPayment > 0) {
          Journal::create([
            'coa_id' => 15,
            'date_journal' => $request['invoice_date'],
            'debit' => 0,
            'kredit' => $restPayment,
            'table_ref' => 'invoicepurchases',
            'code_ref' => $invoice['id'],
            'description' => "Utang pembelian barang $supplier->name dengan No. Invoice: " . 'PB-' . $request->input('num_bill')
          ]);
        }

        if (!($request['discount'] <= 0)) {
          Journal::create([
            'coa_id' => 42,
            'date_journal' => $request['invoice_date'],
            'debit' => 0,
            'kredit' => $request['discount'],
            'table_ref' => 'invoicepurchases',
            'code_ref' => $invoice['id'],
            'description' => "Diskon Pembelian barang barang $supplier->name dengan No. Invoice: " . 'PB-' . $request->input('num_bill')
          ]);
        }

        Journal::create([
          'coa_id' => 17,
          'date_journal' => $request['invoice_date'],
          'debit' => $totalBill,
          'kredit' => 0,
          'table_ref' => 'invoicepurchases',
          'code_ref' => $invoice['id'],
          'description' => "Penambahan persediaan barang $supplier->name dengan No. Invoice: " . 'PB-' . $request->input('num_bill')
        ]);

        DB::commit();

        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => '/backend/invoicepurchases',
        ]);
      } catch (\Throwable $throw) {
        Log::error($throw);
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
    $config['page_title'] = "Detail Purchase Order";
    $config['print_url'] = route('backend.invoicepurchase.print', $id);
    $config['print_dotmatrix_url'] = route('backend.invoicepurchase.print-dotmatrix', $id);
    $page_breadcrumbs = [
      ['page' => '/backend/invoicepurchases', 'title' => "List Purchase Order"],
      ['page' => '#', 'title' => "Detail Purchase Order"],
    ];
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = InvoicePurchase::where('id', $id)
      ->select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))
      ->with(['purchases', 'supplier'])
      ->firstOrFail();

    return view('backend.sparepart.invoicepurchases.show', compact('config', 'page_breadcrumbs', 'data', 'cooperationDefault'));
  }

  public function print($id)
  {
    $config['page_title'] = "Detail Purchase Order";

    $page_breadcrumbs = [
      ['page' => '/backend/invoicepurchases', 'title' => "List Purchase Order"],
      ['page' => '#', 'title' => "Detail Purchase Order"],
    ];
    $cooperationDefault = Cooperation::where('default', '1')->first();
    $data = InvoicePurchase::where('id', $id)
      ->with(['purchases', 'supplier'])
      ->select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))
      ->firstOrFail();

    return view('backend.sparepart.invoicepurchases.print', compact('config', 'page_breadcrumbs', 'data', 'cooperationDefault'));
  }

  public function printDotMatrix($id)
  {
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = InvoicePurchase::where('id', $id)->select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))->with(['purchases', 'supplier'])->firstOrFail();
    $no = 1;
    foreach ($data->purchases as $val):
      $item[] = [
        'no' => $no++,
        'nama' => $val->sparepart->name,
        'item' => $val->qty,
        'price' => number_format($val->price, 0, '.', ','),
        'total' => number_format($val->qty * $val->price, 0, '.', ',')
      ];
    endforeach;
    $item[] = ['no' => '--------------------------------------------------------------------------------'];
    $item[] = ['1' => '', '2' => '', '3' => '', 'name' => 'Diskon', 'nominal' => number_format($data->discount, 0, '.', ',')];
    $item[] = ['1' => '', '2' => '', '3' => ' ', 'name' => 'Total Tagihan', 'nominal' => number_format($data->total_bill, 0, '.', ',')];
    $item[] = ['no' => str_pad('Pembayaran', 80, '-', STR_PAD_BOTH)];
    $item[] = ['no' => 'No', 'nama' => 'Tgl Pembayaran', '1' => '', '2' => '', 'nominal' => 'Nominal'];
    $item[] = ['no' => '--------------------------------------------------------------------------------'];
    $noPayment = 1;
    foreach ($data->purchasepayments as $val):
      $item[] = [
        'no' => $noPayment++,
        'nama' => $val->date_payment,
        '1' => '',
        '2' => '',
        'price' => number_format($val->payment, 0, '.', ',')
      ];
    endforeach;
    $item[] = ['no' => '--------------------------------------------------------------------------------'];
    $item[] = ['no' => '', '1' => '', '2' => '', 'nama' => 'Total Tagihan', 'nominal' => number_format($data->total_bill, 0, '.', ',')];
    $item[] = ['no' => '', '1' => '', '2' => '', 'nama' => 'Total Pembayaran', 'nominal' => number_format($data->total_payment, 0, '.', ',')];
    $item[] = ['no' => '', '1' => '', '2' => '', 'nama' => 'Sisa Tagihan', 'nominal' => number_format($data->rest_payment, 0, '.', ',')];
    $result = '';
    $paper = array(
      'panjang' => 80,
      'baris' => 29,
      'spasi' => 3,
      'column_width' => [
        'header' => [40, 40],
        'table' => [3, 40, 6, 19, 12],
        'footer' => [40, 40]
      ],
      'header' => [
        'left' => [
          $cooperationDefault['nickname'],
          $cooperationDefault['address'],
          'Telp: ' . $cooperationDefault['phone'],
          'Fax: ' . $cooperationDefault['fax'],
          'INVOICE PURCHASE ORDER',

        ],
        'right' => [
          'No. Invoice: ' . $data->num_invoice,
          'Supplier: ' . $data->supplier->name,
          'Tanggal: ' . $this->convertToDate($data->created_at),
          'Metode Pembayaran: ' . $data->method_payment,
          'Tgl Jth Tempo: ' . $data->due_date,

        ]
      ],
      'footer' => [
        ['align' => 'center', 'data' => ['Mengetahui', 'Mengetahui']],
        ['align' => 'center', 'data' => ['', '']],
        ['align' => 'center', 'data' => ['', '']],
        ['align' => 'center', 'data' => ['...................', '...................']],
      ],
      'table' => [
        'header' => ['No', 'Nama Produk', 'Unit', 'Harga', 'Total'],
        'produk' => $item,
        'footer' => array(
          'catatan' => ''
        )
      ]
    );
    $printed = new ContinousPaperLong($paper);
    $result .= $printed->output() . "\n";
    return response($result, 200)->header('Content-Type', 'text/plain');
  }

  public function showpayment($id)
  {
    $config['page_title'] = "Detail Purchase Payment";
    $page_breadcrumbs = [
      ['page' => '/backend/drivers', 'title' => "List Purchase Order Payment"],
      ['page' => '#', 'title' => "Detail Purchase Order Payment"],
    ];
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = InvoicePurchase::where('id', $id)->select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))->with(['purchases', 'supplier', 'purchasepayments'])->firstOrFail();
    return view('backend.sparepart.invoicepurchases.showpayment', compact('config', 'page_breadcrumbs', 'data', 'cooperationDefault'));
  }

  public function edit($id)
  {
    $config['page_title'] = "Edit Purchase Payment";
    $page_breadcrumbs = [
      ['page' => '/backend/drivers', 'title' => "List Purchase Order Payment"],
      ['page' => '#', 'title' => "Detail Purchase Order Payment"],
    ];
    $data = InvoicePurchase::where('id', $id)
      ->select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))
      ->with(['purchases.sparepart:id,name', 'supplier', 'purchasepayments.coa'])
      ->firstOrFail();
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'invoicepurchases')->sole();
    return view('backend.sparepart.invoicepurchases.edit', compact('config', 'page_breadcrumbs', 'data', 'selectCoa'));
  }

  public function update($id, Request $request)
  {
    $validator = Validator::make($request->all(), [
      'payment.date' => 'required|array',
      'payment.date.*' => 'required|date_format:Y-m-d',
      'payment.payment' => 'required|array',
      'payment.payment.*' => 'required|integer',
      'payment.coa' => 'required|array',
      'payment.coa.*' => 'required|integer',
    ]);

    $response = response()->json([
      'status' => 'Error !',
      'message' => "Please complete your form",
    ]);

    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $payments = $request->payment;
        $data = InvoicePurchase::withSum('purchasepayments', 'payment')->findOrFail($id);
        $supplier = SupplierSparepart::findOrFail($data->supplier_sparepart_id);
        $totalPayment = $data->purchasepayments_sum_payment;
        foreach ($payments['date'] as $key => $item):
          $totalPayment += $payments['payment'][$key];
          $coa = Coa::findOrFail($payments['coa'][$key]);
          $checksaldo = DB::table('journals')
            ->select(DB::raw('
          IF(`coas`.`normal_balance` = "Db", (SUM(`journals`.`debit`) - SUM(`journals`.`kredit`)),
          (SUM(`journals`.`kredit`) - SUM(`journals`.`debit`))) AS `saldo`
          '))
            ->leftJoin('coas', 'coas.id', '=', 'journals.coa_id')
            ->where('journals.coa_id', $payments['coa'][$key])
            ->groupBy('journals.coa_id')
            ->first();

          if (($checksaldo->saldo ?? FALSE) && $payments['payment'][$key] <= $checksaldo->saldo) {
            PurchasePayment::create([
              'invoice_purchase_id' => $data->id,
              'date_payment' => $payments['date'][$key],
              'coa_id' => $payments['coa'][$key],
              'payment' => $payments['payment'][$key],
            ]);

            Journal::create([
              'coa_id' => $payments['coa'][$key],
              'date_journal' => $payments['date'][$key],
              'debit' => 0,
              'kredit' => $payments['payment'][$key],
              'table_ref' => 'invoicepurchases',
              'code_ref' => $data->id,
              'description' => "Pembayaran barang supplier $supplier->name dengan No. Invoice: " . $data->prefix . '-' . $data->num_bill . ""
            ]);

            Journal::create([
              'coa_id' => 15,
              'date_journal' => $payments['date'][$key],
              'debit' => $payments['payment'][$key],
              'kredit' => 0,
              'table_ref' => 'invoicepurchases',
              'code_ref' => $data->id,
              'description' => "Pembayaran utang pembelian barang $supplier->name dengan No. Invoice: " . $data->prefix . '-' . $data->num_bill . ""
            ]);
          } else {
            DB::rollBack();
            return response()->json([
              'status' => 'errors',
              'message' => "Saldo $coa->name tidak ada/kurang",
            ]);
          }
        endforeach;

        $restPayment = $data->rest_payment - $totalPayment;
        $data->update([
          'total_payment' => $totalPayment,
          'rest_payment' => $restPayment,
        ]);

        if ($restPayment <= -1) {
          return response()->json([
            'status' => 'error',
            'message' => 'Pastikan sisa tagihan tidak negative',
            'redirect' => '/backend/invoicepurchases',
          ]);
          DB::rollBack();
        }

        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => '/backend/invoicepurchases',
        ]);

        DB::commit();
      } catch (\Throwable $throw) {
        DB::rollBack();
      }
    }
    return $response;
  }

  public function destroy($id)
  {
    $data = InvoicePurchase::findOrFail($id);
    Journal::where('table_ref', 'invoicepurchases')->where('code_ref', $data->id)->delete();
    if ($data->delete()) {
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been deleted',
      ]);
    }
    return $response;
  }

}
