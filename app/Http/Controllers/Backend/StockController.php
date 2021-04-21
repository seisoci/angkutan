<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use Illuminate\Http\Request;
use DataTables;
class StockController extends Controller
{
  public function index(Request $request)
  {
    $config['page_title']       = "List Stok Spare Part";
    $config['page_description'] = "Daftar List Spare Part";
    $page_breadcrumbs = [
      ['page' => '#','title' => "List Spare Part"],
    ];

    if ($request->ajax()) {
      $data = Stock::with(['sparepart']);
      return Datatables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    return view('backend.sparepart.stocks.index', compact('config', 'page_breadcrumbs'));
  }

  public function select2(Request $request){
    $page = $request->page;
    $resultCount = 10;
    $offset = ($page - 1) * $resultCount;
    $data = Stock::leftJoin('spareparts', 'stocks.sparepart_id', '=', 'spareparts.id')
        ->where('spareparts.name', 'LIKE', '%' . $request->q. '%')
        ->orderBy('spareparts.name')
        ->skip($offset)
        ->take($resultCount)
        ->selectRaw('spareparts.id as id, spareparts.name as text, qty')
        ->get();

    $count = Stock::leftJoin('spareparts', 'stocks.sparepart_id', '=', 'spareparts.id')
        ->where('spareparts.name', 'LIKE', '%' . $request->q. '%')
        ->get()
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
