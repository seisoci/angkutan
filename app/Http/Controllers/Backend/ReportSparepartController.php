<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\UsageItem;
use App\Traits\CarbonTrait;
use DataTables;
use Illuminate\Http\Request;

class ReportSparepartController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:reportsparepart-list|reportsparepart-create|reportsparepart-edit|reportsparepart-delete', ['only' => ['index']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Pemakaian Sparepart";
    $config['page_description'] = "List Laporan Pemakaian Sparepart";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Pemakaian Sparepart"],
    ];
    if ($request->ajax()) {
      $driver_id = $request->driver_id;
      $transport_id = $request->transport_id;
      $date_start = $this->toDate($request->date_start);
      $date_end = $this->toDate($request->date_end);
      $data = UsageItem::with(['sparepart', 'invoiceusage.driver:id,name', 'invoiceusage.transport:id,num_pol'])
        ->whereHas('invoiceusage', function ($query) use ($driver_id, $transport_id, $date_start, $date_end) {
          // $query->where('type', 'self');
          $query->when($driver_id, function ($query, $driver_id) {
            return $query->where('driver_id', $driver_id);
          });
          $query->when($transport_id, function ($query, $transport_id) {
            return $query->where('transport_id', $transport_id);
          });
          $query->when($date_start, function ($query) use ($date_start, $date_end) {
            return $query->whereBetween('created_at', [$date_start, $date_end]);
          });
        });
      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);

    }
    return view('backend.report.reportsparepart.index', compact('config', 'page_breadcrumbs'));
  }
}
