<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cooperation;
use App\Models\Journal;
use App\Models\Opname;
use App\Models\OpnameDetail;
use App\Models\Setting;
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
      try {
        $items = $request->items;
        DB::beginTransaction();
        $opanme = Opname::create(['description' => $request->description]);
        foreach ($items['sparepart_id'] as $key => $item):
          $stock = Stock::with('sparepart')
            ->where('sparepart_id', $items['sparepart_id'][$key])
            ->where('invoice_purchase_id', $items['invoice_purchase_id'][$key])
            ->firstOrFail();

          $data = OpnameDetail::create([
            'opname_id' => $opanme->id,
            'sparepart_id' => $items['sparepart_id'][$key],
            'qty' => $items['qty_now'][$key],
            'qty_system' => $stock->qty,
            'qty_difference' => $items['qty_now'][$key] - $stock->qty,
          ]);

          Journal::create([
            'coa_id' => 49,
            'date_journal' => $this->dateNow(),
            'debit' => $items['price'][$key],
            'kredit' => 0,
            'table_ref' => 'opnames',
            'code_ref' => $data->id,
            'description' => "Pengeluaran barang ".$stock->sparepart->name
          ]);

          Journal::create([
            'coa_id' => 17,
            'date_journal' => $this->dateNow(),
            'debit' => 0,
            'kredit' => $items['price'][$key],
            'table_ref' => 'opnames',
            'code_ref' => $data->id,
            'description' => "Penambahan kehilangan barang ".$stock->sparepart->name
          ]);

          $stock->update([
            'qty' => $items['qty_difference'][$key]
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

}
