<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cooperation;
use App\Models\Costumer;
use App\Models\InvoiceCostumer;
use App\Models\JobOrder;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ReportPiutangBelumLunasController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:reportpiutanglunas-list|reportpiutanglunas-create|reportpiutanglunas-edit|reportpiutanglunas-delete', ['only' => ['index']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Piutang Belum Lunas";
    $config['page_description'] = "Laporan Piutang Belum Lunas";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Piutang Belum Lunas"],
    ];
    $config['excel_url'] = 'reportpiutangbelumlunas/document?type=EXCEL';
    $config['pdf_url'] = 'reportpiutangbelumlunas/document?type=PDF';
    $config['print_url'] = 'reportpiutangbelumlunas/print';

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
    return view('backend.report.reportpiutanglunas.index', compact('config', 'page_breadcrumbs'));
  }

  public function print(Request $request)
  {
    $config['page_title'] = "Laporan Piutang Belum Lunas";
    $config['page_description'] = "Laporan Piutang Belum Lunas";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Piutang Belum Lunas"],
    ];

    $cooperationDefault = Cooperation::where('default', '1')->first();
    $date = $request['date'];
    $costumerId = $request['customer_id'];
    $costumer = Costumer::find($costumerId);
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
      ->where('rest_payment', '>', 0)
      ->get();

    return view('backend.report.reportpiutangbelumlunas.print', compact('config', 'page_breadcrumbs', 'cooperationDefault', 'data', 'costumer', 'date'));
  }
}
