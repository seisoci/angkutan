<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\InvoiceCostumer;
use App\Models\JobOrder;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ReportPiutangController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:reportinvoicecostumers-list|reportinvoicecostumers-create|reportinvoicecostumers-edit|reportinvoicecostumers-delete', ['only' => ['index']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Piutang";
    $config['page_description'] = "Laporan Piutang";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Piutang"],
    ];
    $config['excel_url'] = 'reportpiutang/document?type=EXCEL';
    $config['pdf_url'] = 'reportpiutang/document?type=PDF';
    $config['print_url'] = 'reportpiutang/print';

    if ($request->ajax()) {
      $date = $request['date'];
      $costumerId = $request['costumer_id'];
      $data = InvoiceCostumer::with(['costumer:id,name', 'paymentcostumers'])
        ->when($date, function ($query, $date) {
          $date_format = explode(" / ", $date);
          $date_begin = $date_format[0];
          $date_end = $date_format[1];
          return $query->whereBetween('invoice_date', [$date_begin, $date_end]);
        })
        ->when($costumerId, function ($query, $costumerId) {
          return $query->where('costumer_id', $costumerId);
        })
        ->where('rest_payment', '>', 0);

      return DataTables::of($data)
        ->addIndexColumn()
        ->editColumn('num_invoice', function (InvoiceCostumer $invoiceCostumer) {
          return '<a target="_blank" href="/backend/invoicecostumers/' . $invoiceCostumer->id . '">' . $invoiceCostumer->num_invoice . '</a>';
        })
        ->rawColumns(['num_invoice'])
        ->make(true);
    }
    return view('backend.report.reportpiutang.index', compact('config', 'page_breadcrumbs'));
  }
}
