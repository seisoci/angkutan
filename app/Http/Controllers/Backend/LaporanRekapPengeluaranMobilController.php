<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\InvoiceUsageItem;
use App\Models\JobOrder;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class LaporanRekapPengeluaranMobilController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:laporan-rekap-pengeluaran-mobil-list|laporan-rekap-pengeluaran-mobil-create|laporan-rekap-pengeluaran-mobil-edit|laporan-rekap-pengeluaran-mobil-delete', ['only' => ['index']]);
    $this->middleware('permission:laporan-rekap-pengeluaran-mobil-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:laporan-rekap-pengeluaran-mobil-edit', ['only' => ['edit', 'update']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Rekap Pengeluaran Mobil";
    $config['page_description'] = "Daftar List Laporan Rekap Pengeluaran Mobil";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Laporan Rekap Pengeluaran Mobil"],
    ];
    $config['excel_url'] = 'laporan-rekap-gaji-bulanan/document?type=EXCEL';
    $config['pdf_url'] = 'laporan-rekap-gaji-bulanan/document?type=PDF';
    $config['print_url'] = 'laporan-rekap-gaji-bulanan/print';

    if($request->ajax()){
      $data = InvoiceUsageItem::selectRaw('
          `transports`.`num_pol` AS `transport_name`,
           COALESCE(SUM(`invoice_usage_items`.`total_payment`), 0) AS `total_payment`
      ')
        ->leftJoin('transports', 'transports.id', '=', 'invoice_usage_items.transport_id')
        ->groupBy('transport_id');

      if($request->filled('transport_id')){
        $data->where('transport_id', $request['transport_id']);
      }
      if($request->filled('date_begin')){
        $data->where('invoice_date', '>=', $request['date_begin']);
      }
      if($request->filled('date_end')){
        $data->where('invoice_date', '<=', $request['date_end']);
      }

      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }

    return view('backend.report.laporan-rekap-pengeluaran-mobil.index', compact('config', 'page_breadcrumbs'));
  }

}
