<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Transport;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ReportTransportController extends Controller
{
  use CarbonTrait;

  public function index(Request $request)
  {
    $config['page_title'] = "laporan Data Mobil";
    $config['page_description'] = "Laporan Data Mobil";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Data Mobil"],
    ];
    $config['excel_url'] = 'reporttransports/document?type=EXCEL';
    $config['pdf_url'] = 'reporttransports/document?type=PDF';
    $config['print_url'] = 'reporttransports/print';

    if ($request->ajax()) {
      $data = Transport::where('another_expedition_id', NULL);
      return DataTables::of($data)
        ->addIndexColumn()
        ->editColumn('type_car', function(Transport $transport) {
          return ucwords($transport->type_car);
        })
        ->make(true);
    }
    return view('backend.report.reporttransports.index', compact('config', 'page_breadcrumbs'));
  }

}
