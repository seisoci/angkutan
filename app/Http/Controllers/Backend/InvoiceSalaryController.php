<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\ConfigCoa;
use App\Models\Driver;
use App\Models\InvoiceSalary;
use App\Models\JobOrder;
use App\Models\Journal;
use App\Models\Prefix;
use App\Models\Setting;
use DataTables;
use DB;
use Illuminate\Http\Request;
use Validator;

class InvoiceSalaryController extends Controller
{
  public function index(Request $request)
  {
    $config['page_title'] = "List Invoice Gaji Supir";
    $config['page_description'] = "Daftar List Invoice Gaji Supir";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Invoice Gaji Supir"],
    ];
    if ($request->ajax()) {
      $data = InvoiceSalary::with(['transport:id,num_pol', 'driver:id,name']);
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('details_url', function (InvoiceSalary $invoiceSalary) {
          return route('backend.invoicesalaries.datatabledetail', $invoiceSalary->id);
        })
        ->addColumn('action', function ($row) {
          $actionBtn = '
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-eye"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                  <a href="invoicesalaries/' . $row->id . '" class="dropdown-item">Invoice Detail</a>
                </div>
            </div>
          ';
          return $actionBtn;
        })
        ->make(true);

    }
    return view('backend.invoice.invoicesalaries.index', compact('config', 'page_breadcrumbs'));
  }

  public function create(Request $request)
  {
    $config['page_title'] = "Create Invoice Gaji Supir";
    $config['page_description'] = "Create Invoice Gaji Supir";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Create Invoice Gaji Supir"],
    ];
    $driver_id = $request->driver_id;
    $transport_id = $request->transport_id;
    if ($request->ajax()) {
      $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])->withSum('operationalexpense', 'amount')
        ->where('type', 'self')
        ->where('status_salary', '0')
        ->where('status_cargo', 'selesai')
        ->when($driver_id, function ($query, $driver_id) {
          return $query->where('driver_id', $driver_id);
        })
        ->when($transport_id, function ($query, $transport_id) {
          return $query->where('transport_id', $transport_id);
        });
      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'invoicesalaries')->sole();
    return view('backend.invoice.invoicesalaries.create', compact('config', 'page_breadcrumbs', 'selectCoa'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'job_order_id' => 'required|array',
      'job_order_id.*' => 'required|integer',
      'prefix' => 'required|integer',
      'num_bill' => 'required|integer',
      'driver_id' => 'required|integer',
      'transport_id' => 'required|integer',
      'coa_id' => 'required|integer',
      'invoice_date' => 'required|date_format:Y-m-d',
    ]);

    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $prefix = Prefix::findOrFail($request->prefix);
        $driver = Driver::findOrFail($request->driver_id);
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
        if (($checksaldo->saldo ?? FALSE) && $request->grand_total <= $checksaldo->saldo) {
          $data = InvoiceSalary::create([
            'prefix' => $prefix->name,
            'num_bill' => $request->input('num_bill'),
            'driver_id' => $request->input('driver_id'),
            'transport_id' => $request->input('transport_id'),
            'invoice_date' => $request->input('invoice_date'),
            'grandtotal' => $request->input('grand_total'),
            'description' => $request->input('description'),
            'memo' => $request->input('memo'),
          ]);

          foreach ($request->job_order_id as $item):
            JobOrder::where('id', $item)->update([
              'invoice_salary_id' => $data->id,
              'status_salary' => '1',
              'salary_coa_id' => $request->input('coa_id')
            ]);
          endforeach;

          Journal::create([
            'coa_id' => $request->input('coa_id'),
            'date_journal' => $request->input('invoice_date'),
            'debit' => 0,
            'kredit' => $request->input('grand_total'),
            'table_ref' => 'invoicesalaries',
            'code_ref' => $data->id,
            'description' => "Pembayaran gaji supir $driver->name"
          ]);

          Journal::create([
            'coa_id' => 37,
            'date_journal' => $request->input('invoice_date'),
            'debit' => $request->input('grand_total'),
            'kredit' => 0,
            'table_ref' => 'invoicesalaries',
            'code_ref' => $data->id,
            'description' => "Beban gaji supir $driver->name dengan $coa->name"
          ]);

          DB::commit();
          $response = response()->json([
            'status' => 'success',
            'message' => 'Data has been saved',
            'redirect' => '/backend/invoicesalaries',
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
    $config['page_title'] = "Invoice Gaji Supir";
    $config['print_url'] = "/backend/invoicesalaries/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/invoicesalaries', 'title' => "List Invoice Gaji"],
      ['page' => '#', 'title' => "Invoice Gaji Supir"],
    ];
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    $data = InvoiceSalary::with(['joborders.costumer:id,name', 'joborders.routefrom:id,name', 'joborders.routeto:id,name', 'transport:id,num_pol', 'driver:id,name', 'joborders' => function ($q) {
      $q->withSum('operationalexpense', 'amount');
    }])->findOrFail($id);

    return view('backend.invoice.invoicesalaries.show', compact('config', 'page_breadcrumbs', 'data', 'profile'));
  }

  public function print($id)
  {
    $config['page_title'] = "Invoice Gaji Supir";
    $config['print_url'] = "/backend/invoicesalaries/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/invoicesalaries', 'title' => "List Invoice Gaji"],
      ['page' => '#', 'title' => "Invoice Gaji Supir"],
    ];
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    $data = InvoiceSalary::with(['joborders.costumer:id,name', 'joborders.routefrom:id,name', 'joborders.routeto:id,name', 'transport:id,num_pol', 'driver:id,name', 'joborders' => function ($q) {
      $q->withSum('operationalexpense', 'amount');
    }])->findOrFail($id);

    return view('backend.invoice.invoicesalaries.print', compact('config', 'page_breadcrumbs', 'data', 'profile'));
  }

  public function findbypk(Request $request)
  {
    $data = json_decode($request->data);
    $response = NULL;
    if ($request->data) {
      $result = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])->withSum('operationalexpense', 'amount')->whereIn('id', $data)->get();

      $response = response()->json([
        'data' => $result,
      ]);
    }
    return $response;
  }

  public function datatabledetail($id)
  {
    $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])->withSum('operationalexpense', 'amount')->where('invoice_salary_id', $id);

    return Datatables::of($data)->make(true);
  }

}
