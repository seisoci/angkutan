<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Costumer;
use App\Models\JobOrder;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ReportLdoNetProfitController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:reportldonetprofit-list|reportldonetprofit-create|reportldonetprofit-edit|reportldonetprofit-delete', ['only' => ['index']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Rekap Laba LDO";
    $config['page_description'] = "Rekap Laba LDO";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Rekap Laba LDO"],
    ];
    $config['excel_url'] = 'reportldonetprofit/document?type=EXCEL';
    $config['pdf_url'] = 'reportldonetprofit/document?type=PDF';
    $config['print_url'] = 'reportldonetprofit/print';

    if ($request->ajax()) {
      $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])
        ->withSum('operationalexpense', 'amount')
        ->where('type', 'ldo');

      return DataTables::of($data)
        ->make(true);
    }

    return view('backend.report.reportldonetprofit.index', compact('config', 'page_breadcrumbs'));

  }

}
