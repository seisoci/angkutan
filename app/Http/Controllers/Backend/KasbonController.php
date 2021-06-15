<?php


namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\ConfigCoa;
use App\Models\Driver;
use App\Models\Journal;
use App\Models\Kasbon;
use App\Traits\CarbonTrait;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class KasbonController extends Controller
{
  use CarbonTrait;

  public function index(Request $request)
  {
    $config['page_title'] = "List Kasbon";
    $config['page_description'] = "Daftar List Kasbon";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Kasbon"],
    ];

    if ($request->ajax()) {
      $data = Kasbon::with('driver:id,name');
      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'kasbon')->sole();
    return view('backend.invoice.kasbon.index', compact('config', 'page_breadcrumbs', 'selectCoa'));
  }

  public function create(Request $request)
  {
    $config['page_title'] = "Create Invoice Kasbon Supir";
    $config['page_description'] = "Create Invoice Kasbon Supir";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Create Invoice Kasbon Supir"],
    ];
    $driver_id = $request->driver_id;
    if ($request->ajax()) {
      $data = Kasbon::with(['driver:id,name'])
        ->when($driver_id, function ($query, $driver_id) {
          return $query->where('driver_id', $driver_id);
        });
      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    return view('backend.invoice.invoicekasbons.create', compact('config', 'page_breadcrumbs'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'driver_id' => 'required|integer',
      'coa_id' => 'required|integer',
      'amount' => 'required|integer',
      'memo' => 'required|string',
    ]);

    if ($validator->passes()) {
      try {
        DB::beginTransaction();
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
        if (($checksaldo->saldo ?? FALSE) && $request->amount <= $checksaldo->saldo) {
          Kasbon::create([
            'driver_id' => $request->input('driver_id'),
            'coa_id' => $request->input('coa_id'),
            'amount' => $request->input('amount'),
            'memo' => $request->input('memo'),
          ]);
          Journal::create([
            'coa_id' => 8,
            'date_journal' => $this->dateNow(),
            'debit' => $request->input('amount'),
            'kredit' => 0,
            'table_ref' => 'kasbon',
            'description' => "Supir $driver->name melakukan kasbon dengan $coa->name"
          ]);
          Journal::create([
            'coa_id' => $request->input('coa_id'),
            'date_journal' => $this->dateNow(),
            'debit' => 0,
            'kredit' => $request->input('amount'),
            'table_ref' => 'kasbon',
            'description' => "Pengeluaran untuk kasbon $driver->name"
          ]);
          $response = response()->json([
            'status' => 'success',
            'message' => 'Data has been saved',
          ]);
          DB::commit();
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

}
