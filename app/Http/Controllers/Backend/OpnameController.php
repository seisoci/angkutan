<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Opname;
use App\Models\OpnameDetail;
use App\Models\Setting;
use App\Models\Sparepart;
use App\Models\Stock;
use DataTables;
use DB;
use Illuminate\Http\Request;
use Validator;

class OpnameController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $config['page_title']       = "List Opname";
      $config['page_description'] = "Daftar List Opname";
      $page_breadcrumbs = [
        ['page' => '#','title' => "List Opname"],
      ];

      if ($request->ajax()) {
        $data = Opname::query();
        return DataTables::of($data)
          ->addIndexColumn()
          ->addColumn('action', function($row){
            $actionBtn = '
              <div class="dropdown">
                  <button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fas fa-eye"></i>
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a href="opnames/'.$row->id.'" class="dropdown-item">Retur Detail</a>
                  </div>
              </div>
            ';
              return $actionBtn;
          })
          ->make(true);
      }
      return view('backend.sparepart.opnames.index', compact('config', 'page_breadcrumbs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $config['page_title'] = "Create Opname";
      $page_breadcrumbs = [
        ['page' => '/backend/opnames','title' => "List Opname"],
        ['page' => '#','title' => "Create Opname"],
      ];
      return view('backend.sparepart.opnames.create', compact('config', 'page_breadcrumbs'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $validator = Validator::make($request->all(), [
        'items.sparepart_id'      => 'required|array',
        'items.sparepart_id.*'    => 'required|integer',
        'items.qty_now'           => 'required|array',
        'items.qty_now.*'         => 'required|integer',
        'items.stock_id'           => 'required|array',
        'items.stock_id.*'         => 'required|integer',
      ]);
      if($validator->passes()){
      try {
        $items   = $request->items;
        // dd($items);
        DB::beginTransaction();
        $opanme  = Opname::create(['description' => $request->description]);
        foreach($items['sparepart_id'] as $key => $item):
          $stock = Stock::where('sparepart_id', $items['sparepart_id'][$key])->firstOrFail();
          $data[] = [
              'opname_id'             => $opanme->id,
              'sparepart_id'          => $items['sparepart_id'][$key],
              'qty'                   => $items['qty_now'][$key],
              'qty_system'            => $stock->qty,
              'qty_difference'        => $items['qty_now'][$key] - $stock->qty,
          ];
          $stock->qty = $items['qty_now'][$key];
          $stock->save();
        endforeach;
        OpnameDetail::insert($data);
        DB::commit();
        $response = response()->json([
          'status'    => 'success',
          'message'   => 'Data has been saved',
          'redirect'  => '/backend/opnames',
        ]);
      }catch (\Throwable $throw) {
        DB::rollBack();
        $response = $throw;
      }

      }else{
        $response = response()->json(['error'=>$validator->errors()->all()]);
      }
      return $response;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Opname  $opname
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      $config['page_title'] = "Detail Opname";
      $page_breadcrumbs = [
        ['page' => '/backend/opnames','title' => "List Opname"],
        ['page' => '#','title' => "Detail Opname"],
      ];
      $collection = Setting::all();
      $profile = collect($collection)->mapWithKeys(function ($item) {
        return [$item['name'] => $item['value']];
      });
      $data = Opname::with(['opnamedetail.sparepart:id,name'])->findOrFail($id);
      return view('backend.sparepart.opnames.show',compact('config', 'page_breadcrumbs', 'data', 'profile'));
    }

}
