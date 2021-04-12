<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use Illuminate\Http\Request;
use DataTables;
class StockController extends Controller
{
  public function __invoke(Request $request)
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
}
