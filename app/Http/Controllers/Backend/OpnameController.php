<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cooperation;
use App\Models\Journal;
use App\Models\Opname;
use App\Models\OpnameDetail;
use App\Models\Sparepart;
use App\Models\Stock;
use App\Traits\CarbonTrait;
use DataTables;
use DB;
use Illuminate\Http\Request;
use Validator;

class OpnameController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:opnames-list|opnames-create|opnames-edit|opnames-delete', ['only' => ['index']]);
    $this->middleware('permission:opnames-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:opnames-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:opnames-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "List Opname";
    $config['page_description'] = "Daftar List Opname";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Opname"],
    ];

    if ($request->ajax()) {
      $data = Opname::query();
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          $actionBtn = '
              <div class="dropdown">
                  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fas fa-eye"></i>
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a href="opnames/' . $row->id . '" class="dropdown-item">Detail Opname</a>
                  </div>
              </div>
            ';
          return $actionBtn;
        })
        ->make(true);
    }
    return view('backend.sparepart.opnames.index', compact('config', 'page_breadcrumbs'));
  }

  public function create()
  {
    $config['page_title'] = "Create Opname";
    $page_breadcrumbs = [
      ['page' => '/backend/opnames', 'title' => "List Opname"],
      ['page' => '#', 'title' => "Create Opname"],
    ];
    return view('backend.sparepart.opnames.create', compact('config', 'page_breadcrumbs'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'items.sparepart_id' => 'required|array',
      'items.sparepart_id.*' => 'required|integer',
      'items.qty_now' => 'required|array',
      'items.qty_now.*' => 'required|integer',
    ]);
    if ($validator->passes()) {
      DB::beginTransaction();
      try {
        $items = $request->items;
        $opanme = Opname::create(['description' => $request->description]);
        foreach ($items['sparepart_id'] as $key => $item):
          if ($items['qty_now'][$key] < 0) {
            return response()->json([
              'status' => 'error',
              'message' => 'Stok Fisik Tidak Boleh Negative',
              'redirect' => '/backend/opnames',
            ]);
          }

          $stock = Stock::with('sparepart')
            ->where('invoice_purchase_id', $items['invoice_purchase_id'][$key])
            ->where('sparepart_id', $items['sparepart_id'][$key])
            ->first();

          $data = OpnameDetail::create([
            'opname_id' => $opanme->id,
            'sparepart_id' => $items['sparepart_id'][$key],
            'qty' => $items['qty_now'][$key],
            'qty_system' => $stock['qty'],
            'qty_difference' => $items['qty_now'][$key] - $stock['qty'],
          ]);

          $price = $data['qty_difference'] > 0 ? ($items['price'][$key] * $data['qty_difference']) : ($items['price'][$key] * $data['qty_difference']);

          Journal::create([
            'coa_id' => 49,
            'date_journal' => $this->dateNow(),
            'debit' => $price < 0 ? abs($price) : 0,
            'kredit' => max($price, 0),
            'table_ref' => 'opnames',
            'code_ref' => $data['id'],
            'description' => "Stok Opname " . $stock->sparepart->name
          ]);

          Journal::create([
            'coa_id' => 17,
            'date_journal' => $this->dateNow(),
            'debit' => max($price, 0),
            'kredit' => $price < 0 ? abs($price) : 0,
            'table_ref' => 'opnames',
            'code_ref' => $data['id'],
            'description' => "Stok Opname " . $stock->sparepart->name
          ]);

          $stock->update([
            'qty' => $items['qty_now'][$key]
          ]);

        endforeach;
        DB::commit();
        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => '/backend/opnames',
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

  public function show($id)
  {
    $config['page_title'] = "Detail Opname";
    $config['print_url'] = "/backend/opnames/$id/print";
    $page_breadcrumbs = [
      ['page' => '/backend/opnames', 'title' => "List Opname"],
      ['page' => '#', 'title' => "Detail Opname"],
    ];
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = Opname::with(['opnamedetail.sparepart:id,name'])->findOrFail($id);
    return view('backend.sparepart.opnames.show', compact('config', 'page_breadcrumbs', 'data', 'cooperationDefault'));
  }

  public function print($id)
  {
    $config['page_title'] = "Detail Opname";
    $page_breadcrumbs = [
      ['page' => '/backend/opnames', 'title' => "List Opname"],
      ['page' => '#', 'title' => "Detail Opname"],
    ];
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = Opname::with(['opnamedetail.sparepart:id,name'])->findOrFail($id);
    return view('backend.sparepart.opnames.print', compact('config', 'page_breadcrumbs', 'data', 'cooperationDefault'));
  }

  public function select2Invoice(Request $request)
  {
    $sparepartId = $request['sparepart_id'];
    $page = $request->page;
    $resultCount = 10;
    $offset = ($page - 1) * $resultCount;
    $data = Stock::selectRaw('
        invoice_purchases.id AS id,
        CONCAT(invoice_purchases.prefix, " - " , invoice_purchases.num_bill) AS text,
        stocks.qty AS qty,
        purchases.price AS price
      ')
      ->leftJoin('spareparts', 'stocks.sparepart_id', '=', 'spareparts.id')
      ->leftJoin('invoice_purchases', 'stocks.invoice_purchase_id', '=', 'invoice_purchases.id')
      ->leftJoin('purchases', function ($join) use ($sparepartId) {
        $join->on('purchases.invoice_purchase_id', '=', 'stocks.invoice_purchase_id');
        $join->where('purchases.sparepart_id', $sparepartId);
      })
      ->where('stocks.sparepart_id', $sparepartId)
      ->where('invoice_purchases.num_bill', 'LIKE', '%' . $request->q . '%')
      ->orderBy('invoice_purchases.invoice_date', 'desc')
      ->skip($offset)
      ->take($resultCount)
      ->groupBy('stocks.invoice_purchase_id')
      ->get();

    $count = Stock::leftJoin('spareparts', 'stocks.sparepart_id', '=', 'spareparts.id')
      ->leftJoin('invoice_purchases', 'stocks.invoice_purchase_id', '=', 'invoice_purchases.id')
      ->leftJoin('purchases', function ($join) use ($sparepartId) {
        $join->on('purchases.invoice_purchase_id', '=', 'stocks.invoice_purchase_id');
        $join->where('purchases.sparepart_id', $sparepartId);
      })
      ->selectRaw('stocks.id as id, CONCAT(invoice_purchases.prefix, " - " , invoice_purchases.num_bill) as text, stocks.qty as qty, purchases.price as price')
      ->where('stocks.sparepart_id', $sparepartId)
      ->where('invoice_purchases.num_bill', 'LIKE', '%' . $request->q . '%')
      ->skip($offset)
      ->take($resultCount)
      ->orderBy('invoice_purchases.invoice_date', 'asc')
      ->groupBy('stocks.invoice_purchase_id')
      ->count();

    $endCount = $offset + $resultCount;
    $morePages = $count > $endCount;

    $results = array(
      "results" => $data,
      "pagination" => array(
        "more" => $morePages
      )
    );

    return response()->json($results);
  }

  public function select2Opname(Request $request)
  {
    $page = $request->page;
    $resultCount = 10;
    $offset = ($page - 1) * $resultCount;
    $data = Sparepart::selectRaw('
        `spareparts`.`id` AS `id`,
        `spareparts`.`name` AS `text`,
         SUM(IFNULL(`stocks`.`qty`, 0)) AS `qty`
      ')
      ->leftJoin('stocks', 'stocks.sparepart_id', '=', 'spareparts.id')
      ->where('spareparts.name', 'LIKE', '%' . $request->q . '%')
      ->orderBy('spareparts.name', 'asc')
      ->groupBy('spareparts.id')
      ->skip($offset)
      ->take($resultCount)
      ->get();

    $count = Sparepart::where('spareparts.name', 'LIKE', '%' . $request->q . '%')
      ->count();
    $endCount = $offset + $resultCount;
    $morePages = $count > $endCount;

    $results = array(
      "results" => $data,
      "pagination" => array(
        "more" => $morePages
      )
    );

    return response()->json($results);
  }

}
