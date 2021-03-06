<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AnotherExpedition;
use App\Models\Coa;
use App\Models\ConfigCoa;
use App\Models\Cooperation;
use App\Models\InvoiceCostumer;
use App\Models\InvoiceLdo;
use App\Models\JobOrder;
use App\Models\Journal;
use App\Models\PaymentLdo;
use App\Models\PiutangKlaim;
use App\Models\Prefix;
use DataTables;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Throwable;
use Validator;

class InvoiceLdoController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:invoiceldo-list|invoiceldo-create|invoiceldo-edit|invoiceldo-delete', ['only' => ['index']]);
    $this->middleware('permission:invoiceldo-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:invoiceldo-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:invoiceldo-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "List Invoice LDO";
    $config['page_description'] = "Daftar List Invoice LDO";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Invoice LDO"],
    ];
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'invoiceldo')->sole();

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
      $data = InvoiceLdo::with(['anotherexpedition:id,name']);
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('details_url', function (InvoiceLdo $invoiceLdo) {
          return route('backend.invoiceldo.datatabledetail', $invoiceLdo->id);
        })
        ->addColumn('action', function ($row) {
          $restPayment = $row->rest_payment != 0 ? '<a href="invoiceldo/' . $row->id . '/edit" class="dropdown-item">Bayar Sisa</a>' : NULL;
          $actionBtn = '
              <div class="dropdown">
                  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fas fa-eye"></i>
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    ' . $restPayment . '
                    <a href="invoiceldo/' . $row->id . '" class="dropdown-item">Invoice Detail</a>
                  </div>
              </div>
            ';
          return $actionBtn;
        })
        ->make(true);

    }
    return view('backend.invoice.invoiceldo.index', compact('config', 'page_breadcrumbs', 'saldoGroup'));
  }

  public function create(Request $request)
  {
    $config['page_title'] = "Create Invoice LDO";
    $config['page_description'] = "Create Invoice LDO";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Create Invoice LDO"],
    ];
    $another_expedition_id = $request->another_expedition_id;
    $costumer_id = $request->costumer_id;
    $route_from = $request->route_from;
    $route_to = $request->route_to;
    $route_from = $request->route_from;
    $cargo_id = $request->cargo_id;
    if ($request->ajax()) {
      $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])
        ->withSum('roadmoneydetail', 'amount')
        ->where('status_payment_ldo', '0')
        ->where('status_cargo', 'selesai')
        ->where('another_expedition_id', '!=', 'NULL')
        ->when($another_expedition_id, function ($query, $another_expedition_id) {
          return $query->where('another_expedition_id', $another_expedition_id);
        })
        ->when($costumer_id, function ($query, $costumer_id) {
          return $query->where('costumer_id', $costumer_id);
        })
        ->when($route_from, function ($query, $route_from) {
          return $query->where('route_from', $route_from);
        })
        ->when($route_to, function ($query, $route_to) {
          return $query->where('route_to', $route_to);
        })
        ->when($cargo_id, function ($query, $cargo_id) {
          return $query->where('cargo_id', $cargo_id);
        });
      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'invoiceldo')->sole();
    return view('backend.invoice.invoiceldo.create', compact('config', 'page_breadcrumbs', 'selectCoa'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'job_order_id' => 'required|array',
      'job_order_id.*' => 'required|integer',
      'num_bill' => 'required|integer',
      'another_expedition_id' => 'required|integer',
      'coa_id' => 'required|integer',
      'invoice_date' => 'required|date_format:Y-m-d',
      'due_date' => 'required|date_format:Y-m-d'
    ]);

    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $totalCut = 0;
        $totalPiutang = 0;
        foreach ($request['job_orderid'] ?? array() as $key => $item):
          foreach ($item as $type => $jo) {
            if ($type == 'tambah') {
              $totalPiutang += $jo['nominal'];
            } else {
              $totalCut += $jo['nominal'];
            }
            PiutangKlaim::create([
              'job_order_id' => $key,
              'amount' => $jo['nominal'],
              'description' => $jo['keterangan'],
              'type' => $type,
              'invoice_type' => 'ldo'
            ]);
          }
        endforeach;
//        $prefix = Prefix::findOrFail($request->prefix);
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

        $noUrut = InvoiceLdo::selectRaw("IFNULL(MAX(SUBSTRING(`num_bill`, 7, 3)), 0) + 1 AS max")
            ->whereMonth('invoice_date', Carbon::createFromFormat('Y-m-d', $request->input('invoice_date'))->format('m'))
            ->whereYear('invoice_date', Carbon::createFromFormat('Y-m-d', $request->input('invoice_date'))->format('Y'))
            ->first()->max ?? 0;

        $noUrutNext = Carbon::createFromFormat('Y-m-d', $request->input('invoice_date'))->format('Ym') . "" . str_pad($noUrut, 3, "0", STR_PAD_LEFT);
        $data = InvoiceLdo::create([
          'prefix' => 'TAGLDO',
          'num_bill' => $noUrutNext,
          'another_expedition_id' => $request->input('another_expedition_id'),
          'invoice_date' => $request->input('invoice_date'),
          'due_date' => $request->input('due_date'),
          'total_bill' => $request->input('total_bill'),
          'total_piutang' => $totalPiutang,
          'total_cut' => $totalCut,
          'total_payment' => $request->input('payment.payment') ?? 0,
          'rest_payment' => $request->input('rest_payment'),
          'memo' => $request->input('memo'),
        ]);

        foreach ($request->job_order_id as $item):
          JobOrder::where('id', $item)->update(['invoice_ldo_id' => $data->id, 'status_payment_ldo' => '1']);
        endforeach;

        if ($request->input('payment.payment') && $request->input('payment.date_payment')) {
          if (($checksaldo->saldo ?? FALSE) && ($request->input('payment.payment') ?? 0) <= $checksaldo->saldo) {
            PaymentLdo::create([
              'invoice_ldo_id' => $data->id,
              'date_payment' => $request->input('payment.date_payment'),
              'payment' => $request->input('payment.payment'),
              'description' => $request->input('payment.description'),
              'coa_id' => $request->input('coa_id')
            ]);

            $LDO = AnotherExpedition::findOrFail($request->another_expedition_id);

            Journal::create([
              'coa_id' => $request->input('coa_id'),
              'date_journal' => $request->input('payment.date_payment'),
              'debit' => 0,
              'kredit' => $request->input('payment.payment'),
              'table_ref' => 'invoiceldo',
              'code_ref' => $data->id,
              'description' => "Pembayaran invoice ldo $LDO->name dengan No. Invoice: " . 'TAGLDO-' . $request->input('num_bill') . ""
            ]);

            Journal::create([
              'coa_id' => 39,
              'date_journal' => $request->input('payment.date_payment'),
              'debit' => $request->input('payment.payment'),
              'kredit' => 0,
              'table_ref' => 'invoiceldo',
              'code_ref' => $data->id,
              'description' => "Beban invoice ldo $LDO->name dengan $coa->name dan No. Invoice: " . 'TAGLDO-' . $request->input('num_bill') . ""
            ]);
          } else {
            DB::rollBack();
            return response()->json([
              'status' => 'errors',
              'message' => "Saldo $coa->name tidak ada/kurang",
            ]);
          }
        }

        if ($request->rest_payment <= -1) {
          DB::rollBack();
          return response()->json([
            'status' => 'error',
            'message' => 'Pastikan sisa tagihan tidak negative',
            'redirect' => '/backend/invoiceldo',
          ]);
        }

        DB::commit();
        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => '/backend/invoiceldo',
        ]);

      } catch (Throwable $throw) {
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
    $config['page_title'] = "Invoice Tagihan LDO";
    $config['print_url'] = "/backend/invoiceldo/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/invoiceldo', 'title' => "List Invoice Gaji"],
      ['page' => '#', 'title' => "Invoice Tagihan LDO"],
    ];
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = InvoiceLdo::with(['joborders' => function ($q) {
      $q->withSum('roadmoneydetail', 'amount');
    }, 'joborders.costumer:id,name', 'anotherexpedition', 'paymentldos', 'joborders.routefrom:id,name', 'joborders.routeto:id,name', 'anotherexpedition:id,name', 'joborders.piutangklaimldo'])
      ->findOrFail($id);

    return view('backend.invoice.invoiceldo.show', compact('config', 'page_breadcrumbs', 'data', 'cooperationDefault'));
  }

  public function print($id)
  {
    $config['page_title'] = "Invoice Tagihan LDO";
    $page_breadcrumbs = [
      ['page' => '/backend/invoiceldo', 'title' => "List Invoice Gaji"],
      ['page' => '#', 'title' => "Invoice Tagihan LDO"],
    ];
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = InvoiceLdo::with(['joborders' => function ($q) {
      $q->withSum('roadmoneydetail', 'amount');
    }, 'joborders.costumer:id,name', 'anotherexpedition', 'paymentldos', 'joborders.routefrom:id,name', 'joborders.routeto:id,name', 'anotherexpedition:id,name'])->findOrFail($id);

    return view('backend.invoice.invoiceldo.print', compact('config', 'page_breadcrumbs', 'data', 'cooperationDefault'));
  }

  public function edit($id)
  {
    $config['page_title'] = "Detail Invoice Tagihan LDO";
    $page_breadcrumbs = [
      ['page' => '/backend/invoiceldo', 'title' => "List Detail Pembayaran Pelanggan"],
      ['page' => '#', 'title' => "Detail Detail Pembayaran Pelanggan"],
    ];
    $data = InvoiceLdo::select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))
      ->with(['joborders' => function ($q) {
        $q->withSum('roadmoneydetail', 'amount');
      }, 'anotherexpedition', 'paymentldos.coa', 'joborders.anotherexpedition:id,name', 'joborders.driver:id,name', 'joborders.costumer:id,name', 'joborders.cargo:id,name', 'joborders.transport:id,num_pol', 'joborders.routefrom:id,name', 'joborders.routeto:id,name', 'joborders.piutangklaimldo'])
      ->withSum('paymentldos', 'payment')
      ->findOrFail($id);

    $plucked = $data->joborders->pluck('id');
    $total = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])
      ->withSum('roadmoneydetail', 'amount')
      ->whereIn('id', $plucked)
      ->get();

    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'invoiceldo')->sole();
    return view('backend.invoice.invoiceldo.edit', compact('config', 'page_breadcrumbs', 'data', 'selectCoa', 'total'));
  }

  public function update(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'coa_id' => 'required|integer',
    ]);
    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $data = InvoiceLdo::findOrFail($id);
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
        $payment = PaymentLdo::where('invoice_ldo_id', $data->id)->sum('payment');
        $payment += $request->input('payment.payment');
        $totalCut = 0;
        $totalPiutang = 0;

        foreach ($data->joborders as $item):
          PiutangKlaim::where('job_order_id', $item->id)->where('invoice_type', 'ldo')->delete();
        endforeach;

        foreach ($request['job_orderid'] ?? array() as $key => $item):
          PiutangKlaim::where('job_order_id', $key)->where('invoice_type', 'ldo')->delete();
          foreach ($item as $type => $jo) {
            if ($type == 'tambah') {
              $totalPiutang += $jo['nominal'];
            } else {
              $totalCut += $jo['nominal'];
            }
            PiutangKlaim::create([
              'job_order_id' => $key,
              'amount' => $jo['nominal'],
              'description' => $jo['keterangan'],
              'type' => $type,
              'invoice_type' => 'ldo',
            ]);
          }
        endforeach;

        $data->update([
          'total_cut' => $totalCut,
          'total_piutang' => $totalPiutang,
          'rest_payment' => $request['rest_payment'],
          'total_payment' => $payment,
          'total_bill' => $request['total_bill'],
        ]);

        if (($checksaldo->saldo ?? FALSE) && $request->input('payment.payment') <= $checksaldo->saldo) {
          $LDO = AnotherExpedition::findOrFail($data->another_expedition_id);

          if ($request->input('payment.payment') && $request->input('payment.date_payment')) {
            PaymentLdo::create([
              'invoice_ldo_id' => $data->id,
              'date_payment' => $request->input('payment.date_payment'),
              'payment' => $request->input('payment.payment'),
              'description' => $request->input('payment.description'),
              'coa_id' => $request->input('coa_id')
            ]);

            Journal::create([
              'coa_id' => $request->input('coa_id'),
              'date_journal' => $request->input('payment.date_payment'),
              'debit' => 0,
              'kredit' => $request->input('payment.payment'),
              'table_ref' => 'invoiceldo',
              'code_ref' => $data->id,
              'description' => "Pembayaran invoice ldo $LDO->name dengan No. Invoice: " . $data->prefix . '-' . $data->num_bill . ""
            ]);

            Journal::create([
              'coa_id' => 39,
              'date_journal' => $request->input('payment.date_payment'),
              'debit' => $request->input('payment.payment'),
              'kredit' => 0,
              'table_ref' => 'invoiceldo',
              'code_ref' => $data->id,
              'description' => "Beban invoice ldo $LDO->name dengan $coa->name dan No. Invoice: " . $data->prefix . '-' . $data->num_bill . ""
            ]);
          }

          if ($request->rest_payment <= -1) {
            DB::rollBack();
            return response()->json([
              'status' => 'error',
              'message' => 'Pastikan sisa tagihan tidak negative',
              'redirect' => '/backend/invoiceldo',
            ]);
          }

          DB::commit();
          $response = response()->json([
            'status' => 'success',
            'message' => 'Data has been saved',
            'redirect' => '/backend/invoiceldo',
          ]);
        } else {
          DB::rollBack();
          $response = response()->json([
            'status' => 'errors',
            'message' => "Saldo $coa->name tidak ada/kurang",
          ]);
        }
      } catch (Throwable $throw) {
        DB::rollBack();
        $response = $throw;
      }
    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }

  public function datatabledetail($id)
  {
    $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])
      ->withSum('operationalexpense', 'amount')->where('invoice_ldo_id', $id);
    return Datatables::of($data)->make(true);
  }

  public function findbypk(Request $request)
  {
    $data = json_decode($request->data);
    $response = NULL;
    if ($request->data) {
      $result = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])
        ->withSum('roadmoneydetail', 'amount')
        ->whereIn('id', $data)->get();

      $response = response()->json([
        'data' => $result,
      ]);
    }
    return $response;
  }

}
