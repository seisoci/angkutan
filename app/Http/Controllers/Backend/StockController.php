<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:stocks-list|stocks-create|stocks-edit|stocks-delete', ['only' => ['index']]);
    $this->middleware('permission:stocks-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:stocks-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:stocks-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "List Stok Spare Part";
    $config['page_description'] = "Daftar List Spare Part";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Spare Part"],
    ];

    if ($request->ajax()) {
      $data = DB::table('stocks')
        ->select(['spareparts.name', DB::raw('SUM(`qty`) AS `qty`')])
        ->leftJoin('spareparts', 'spareparts.id', '=', 'stocks.sparepart_id')
        ->groupBy('stocks.sparepart_id');
      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    return view('backend.sparepart.stocks.index', compact('config', 'page_breadcrumbs'));
  }

  public function select2(Request $request)
  {
    $page = $request->page;
    $resultCount = 10;
    $offset = ($page - 1) * $resultCount;
    $data = Stock::leftJoin('spareparts', 'stocks.sparepart_id', '=', 'spareparts.id')
      ->selectRaw('spareparts.id as id, spareparts.name as text, qty, stocks.id as stock_id')
      ->where('spareparts.name', 'LIKE', '%' . $request->q . '%')
      ->where('qty', '>', 0)
      ->when($request->used, function ($query, $used) {
        return $query->whereNotIn('sparepart_id', $used);
      })
      ->orderBy('spareparts.name')
      ->skip($offset)
      ->take($resultCount)
      ->groupBy('sparepart_id')
      ->get();

    $count = Stock::leftJoin('spareparts', 'stocks.sparepart_id', '=', 'spareparts.id')
      ->where('spareparts.name', 'LIKE', '%' . $request->q . '%')
      ->where('qty', '>', 0)
      ->when($request->used, function ($query, $used) {
        return $query->whereNotIn('sparepart_id', $used);
      })
      ->groupBy('sparepart_id')
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

  public function select2Invoice(Request $request)
  {

    $sparepart_id_current = $request->sparepart_id_current;
    $sparepart_id = $request->sparepart_id;
    $invoice_id = $request->invoice_id;
    $sparePartPluck = '';
    foreach ($sparepart_id  as $key => $item){
      if(isset($invoice_id[$key])){
        if($key > 0){
          $sparePartPluck.= " AND";
        }
        $sparePartPluck .= " NOT(`stocks`.`invoice_purchase_id` ='".$invoice_id[$key]."' and `stocks`.`sparepart_id`='".$item."')";
      }
    }
    $page = $request->page;
    $resultCount = 10;
    $offset = ($page - 1) * $resultCount;
    $data = Stock::leftJoin('spareparts', 'stocks.sparepart_id', '=', 'spareparts.id')
      ->leftJoin('invoice_purchases', 'stocks.invoice_purchase_id', '=', 'invoice_purchases.id')
      ->leftJoin('purchases', function ($join) use ($sparepart_id_current) {
        $join->on('purchases.invoice_purchase_id', '=', 'stocks.invoice_purchase_id')
          ->where('purchases.sparepart_id', $sparepart_id_current);
      })
      ->selectRaw('stocks.id as id, invoice_purchases.id as invoice_purchase_id, CONCAT(invoice_purchases.prefix, " - " , invoice_purchases.num_bill) as text, stocks.qty as qty, purchases.price as price')
      ->when($sparepart_id_current, function ($query, $sparepart_id_current) {
        return $query->where('stocks.sparepart_id', $sparepart_id_current);
      })
      ->where('invoice_purchases.num_bill', 'LIKE', '%' . $request->q . '%')
      ->where('stocks.qty', '>', 0)
      ->when($sparePartPluck, function ($query) use($sparePartPluck) {
        return $query->whereRaw($sparePartPluck);
      })
      ->orderBy('invoice_purchases.invoice_date', 'asc')
      ->skip($offset)
      ->take($resultCount)
      ->groupBy('stocks.invoice_purchase_id')
      ->get();

    $count = Stock::leftJoin('spareparts', 'stocks.sparepart_id', '=', 'spareparts.id')
      ->leftJoin('invoice_purchases', 'stocks.invoice_purchase_id', '=', 'invoice_purchases.id')
      ->leftJoin('purchases', function ($join) use ($sparepart_id_current) {
        $join->on('purchases.invoice_purchase_id', '=', 'stocks.invoice_purchase_id')
          ->where('purchases.sparepart_id', $sparepart_id_current);
      })
      ->selectRaw('stocks.id as id, CONCAT(invoice_purchases.prefix, " - " , invoice_purchases.num_bill) as text, stocks.qty as qty, purchases.price as price')
      ->when($sparepart_id_current, function ($query) use($sparepart_id_current) {
        return $query->where('stocks.sparepart_id', $sparepart_id_current);
      })
      ->where('invoice_purchases.num_bill', 'LIKE', '%' . $request->q . '%')
      ->where('stocks.qty', '>', 0)
      ->when($sparePartPluck, function ($query, $sparePartPluck) {
        return $query->whereRaw($sparePartPluck);
      })
      ->orderBy('invoice_purchases.invoice_date', 'asc')
      ->skip($offset)
      ->take($resultCount)
      ->groupBy('stocks.invoice_purchase_id')
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

  public function select2Opname(Request $request)
  {
    $sparepart_id_current = $request->sparepart_id_current;
    $sparepart_id = $request->sparepart_id;
    $invoice_id = $request->invoice_id;
    $sparePartPluck = '';
    foreach ($sparepart_id  as $key => $item){
      if(isset($invoice_id[$key])){
        if($key > 0){
          $sparePartPluck.= " AND";
        }
        $sparePartPluck .= " NOT(`stocks`.`invoice_purchase_id` ='".$invoice_id[$key]."' and `stocks`.`sparepart_id`='".$item."')";
      }
    }
    $page = $request->page;
    $resultCount = 10;
    $offset = ($page - 1) * $resultCount;
    $data = Stock::leftJoin('spareparts', 'stocks.sparepart_id', '=', 'spareparts.id')
      ->leftJoin('invoice_purchases', 'stocks.invoice_purchase_id', '=', 'invoice_purchases.id')
      ->leftJoin('purchases', function ($join) use ($sparepart_id_current) {
        $join->on('purchases.invoice_purchase_id', '=', 'stocks.invoice_purchase_id')
          ->where('purchases.sparepart_id', $sparepart_id_current);
      })
      ->selectRaw('stocks.id as id, invoice_purchases.id as invoice_purchase_id, CONCAT(invoice_purchases.prefix, " - " , invoice_purchases.num_bill) as text, stocks.qty as qty, purchases.price as price')
      ->when($sparepart_id_current, function ($query, $sparepart_id_current) {
        return $query->where('stocks.sparepart_id', $sparepart_id_current);
      })
      ->where('invoice_purchases.num_bill', 'LIKE', '%' . $request->q . '%')
      ->where('stocks.qty', '>', 0)
      ->when($sparePartPluck, function ($query) use($sparePartPluck) {
        return $query->whereRaw($sparePartPluck);
      })
      ->orderBy('invoice_purchases.invoice_date', 'asc')
      ->skip($offset)
      ->take($resultCount)
      ->groupBy('stocks.invoice_purchase_id')
      ->get();

    $count = Stock::leftJoin('spareparts', 'stocks.sparepart_id', '=', 'spareparts.id')
      ->leftJoin('invoice_purchases', 'stocks.invoice_purchase_id', '=', 'invoice_purchases.id')
      ->leftJoin('purchases', function ($join) use ($sparepart_id_current) {
        $join->on('purchases.invoice_purchase_id', '=', 'stocks.invoice_purchase_id')
          ->where('purchases.sparepart_id', $sparepart_id_current);
      })
      ->selectRaw('stocks.id as id, CONCAT(invoice_purchases.prefix, " - " , invoice_purchases.num_bill) as text, stocks.qty as qty, purchases.price as price')
      ->when($sparepart_id_current, function ($query) use($sparepart_id_current) {
        return $query->where('stocks.sparepart_id', $sparepart_id_current);
      })
      ->where('invoice_purchases.num_bill', 'LIKE', '%' . $request->q . '%')
      ->where('stocks.qty', '>', 0)
      ->when($sparePartPluck, function ($query, $sparePartPluck) {
        return $query->whereRaw($sparePartPluck);
      })
      ->orderBy('invoice_purchases.invoice_date', 'asc')
      ->skip($offset)
      ->take($resultCount)
      ->groupBy('stocks.invoice_purchase_id')
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
