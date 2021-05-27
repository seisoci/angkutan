<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\JobOrder;
use App\Models\Setting;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class ReportRecapSalaryController extends Controller
{
  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Gaji Supir";
    $config['page_description'] = "Laporan Gaji Supir";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Gaji Supir"],
    ];
    $config['excel_url'] = 'reportsalaries_OLD/document?type=EXCEL';
    $config['pdf_url'] = 'reportsalaries_OLD/document?type=PDF';
    $config['print_url'] = 'reportsalaries_OLD/print';

    if ($request->ajax()) {
      $date = $request->date;
      $data = JobOrder::with('driver:id,name')
        ->select('*',
          DB::raw(
            'COUNT(`driver_id`) as report_qty,
        SUM(`invoice_bill`) as report_total_basic_price,
        (SUM(`invoice_bill`) - (SUM((`invoice_bill`)) * SUM((IFNULL(`tax_percent`, 0))/100))) as report_total_tax
        '))
        ->withSum('operationalexpense','amount')
        ->where('another_expedition_id', NULL)
        ->where('status_cargo',  'selesai')
        ->when($date, function ($query, $date) {
          $date_format = explode(" / ", $date);
          $date_begin = $date_format[0];
          $date_end = $date_format[1];
          return $query->whereBetween('date_begin', [$date_begin, $date_end]);
        })
        ->groupBy('driver_id');
      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    return view('backend.report.reportsalaries.index', compact('config', 'page_breadcrumbs'));
  }


}
