<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\JobOrder;
use App\Models\Setting;
use App\Models\Transport;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class ReportRecapJobOrderController extends Controller
{
  use CarbonTrait;

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Rekap Job Order";
    $config['page_description'] = "Laporan Rekap Job Order";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Rekap Job Order"],
    ];
    $config['excel_url'] = 'reportrecapjoborder/document?type=EXCEL';
    $config['pdf_url'] = 'reportrecapjoborder/document?type=PDF';
    $config['print_url'] = 'reportrecapjoborder/print';

    if ($request->ajax()) {
      $date = $request->date;
      $data = JobOrder::with('costumer')
        ->select('*',
          DB::raw(
            'COUNT(`costumer_id`) as report_qty,
        SUM(`invoice_bill`) as report_total_basic_price,
        (SUM(`invoice_bill`) - (SUM((`invoice_bill`)) * SUM((IFNULL(`tax_percent`, 0))/100))) as report_total_tax
      '))
        ->where('status_cargo',  'selesai')
        ->when($date, function ($query, $date) {
          $date_format = explode(" / ", $date);
          $date_begin = $date_format[0];
          $date_end = $date_format[1];
          return $query->whereBetween('date_begin', [$date_begin, $date_end]);
        })
        ->groupBy('costumer_id')
        ->get();

      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    return view('backend.report.reportrecapjoborder.index', compact('config', 'page_breadcrumbs'));
  }

  public function document(Request $request)
  {
    $type = $request->type;
    $date = $request->date;
    $data = JobOrder::with('costumer')
      ->select('*',
        DB::raw(
          'COUNT(`costumer_id`) as report_qty,
        SUM(`invoice_bill`) as report_total_basic_price,
        (SUM(`invoice_bill`) - (SUM((`invoice_bill`)) * SUM((IFNULL(`tax_percent`, 0))/100))) as report_total_tax
      '))
      ->where('status_cargo', 'selesai')
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('date_begin', [$date_begin, $date_end]);
      })
      ->groupBy('costumer_id')
      ->get();
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->getPageSetup()
      ->setPaperSize(PageSetup::PAPERSIZE_A4)
      ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

    $borderLeftRight = [
      'borders' => [
        'left' => [
          'borderStyle' => Border::BORDER_THIN,
        ],
        'right' => [
          'borderStyle' => Border::BORDER_THIN,
        ],
        'vertical' => [
          'borderStyle' => Border::BORDER_THIN,
        ],

      ],
    ];
    $borderBottom = [
      'borders' => [
        'bottom' => [
          'borderStyle' => Border::BORDER_THIN,
        ],
      ],
    ];
    $borderTopBottom = [
      'borders' => [
        'bottom' => [
          'borderStyle' => Border::BORDER_THIN,
        ],
        'top' => [
          'borderStyle' => Border::BORDER_THIN,
        ],
        'vertical' => [
          'borderStyle' => Border::BORDER_THIN,
        ],
      ],
    ];
    $borderAll = [
      'borders' => [
        'allBorders' => [
          'borderStyle' => Border::BORDER_THIN,
        ],
      ],
    ];

    $sheet->mergeCells('A1:C1');
    $sheet->setCellValue('A1', 'Laporan Rekap Job Order');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->mergeCells('A3:C3');
    $sheet->setCellValue('A3', 'Priode: ' . (!empty($date) ? $date : 'All Date'));
    $sheet->mergeCells('D1:G1');
    $sheet->setCellValue('D1', $profile['name']);
    $sheet->mergeCells('D2:G2');
    $sheet->setCellValue('D2', $profile['address']);
    $sheet->mergeCells('D3:G3');
    $sheet->setCellValue('D3', 'Telp: ' . $profile['telp']);
    $sheet->mergeCells('D4:G4');
    $sheet->setCellValue('D4', 'Fax: ' . $profile['fax']);

    $sheet->getColumnDimension('A')->setWidth(3.55);
    $sheet->getColumnDimension('B')->setWidth(40);
    $sheet->getColumnDimension('C')->setWidth(35);
    $sheet->getColumnDimension('D')->setWidth(8);
    $sheet->getColumnDimension('E')->setWidth(20);
    $sheet->getColumnDimension('F')->setWidth(20);

    $sheet->getStyle('A6')->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A6', 'No.');
    $sheet->setCellValue('B6', 'Nama Pelanggan');
    $sheet->setCellValue('C6', 'Alamat');
    $sheet->setCellValue('D6', 'Jumlah JO');
    $sheet->setCellValue('E6', 'Total (Ex. Tax)');
    $sheet->setCellValue('F6', 'Total (Inc. Tax)');

    $startCell = 6;
    $startCellFilter = 6;
    $no = 1;
    $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')
      ->applyFromArray($borderTopBottom)
      ->applyFromArray($borderLeftRight);
    foreach ($data as $item):
      $startCell++;
      $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')->applyFromArray($borderLeftRight);
      $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('center');
      $sheet->getStyle('E' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->getStyle('F' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->setCellValue('A' . $startCell, $no++);
      $sheet->setCellValue('B' . $startCell, $item->costumer->name);
      $sheet->setCellValue('C' . $startCell, $item->costumer->address);
      $sheet->setCellValue('D' . $startCell, $item->report_qty);
      $sheet->setCellValue('E' . $startCell, $item->report_total_basic_price);
      $sheet->setCellValue('F' . $startCell, $item->report_total_tax);
    endforeach;
    $sheet->setAutoFilter('B' . $startCellFilter . ':F' . $startCell);
    $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')->applyFromArray($borderBottom);

    $endForSum = $startCell;
    $startCell++;
    $startCellFilter++;
    $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')->applyFromArray($borderAll);
    $sheet->getStyle('E' . $startCell . ':F' . $startCell . '')->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('right');
    $sheet->setCellValue('A' . $startCell, 'Total Rp.');
    $sheet->mergeCells('A' . $startCell . ':D' . $startCell . '');
    $sheet->getStyle('E' . $startCell . ':F' . $startCell)->getFont()->setBold(true);
    $sheet->setCellValue('E' . $startCell, '=SUM(E' . $startCellFilter . ':E' . $endForSum . ')');
    $sheet->setCellValue('F' . $startCell, '=SUM(F' . $startCellFilter . ':F' . $endForSum . ')');
    $cellBasicPrice = "L" . $startCell;

    $filename = 'Laporan Rekap Job Order ' . $this->dateTimeNow();
    if ($type == 'EXCEL') {
      $writer = new Xlsx($spreadsheet);
      header('Content-Type: application/vnd.ms-excel');
      header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
      header('Cache-Control: max-age=0');
    } elseif ($type == 'PDF') {
      $writer = new Mpdf($spreadsheet);
      header('Content-Type: application/pdf');
      header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
      header('Cache-Control: max-age=0');
    }
    $writer->save('php://output');
    exit();
  }

  public function print(Request $request)
  {
    $date = $request->date;
    $data = JobOrder::with('costumer')
      ->select('*',
        DB::raw(
          'COUNT(`costumer_id`) as report_qty,
        SUM(`invoice_bill`) as report_total_basic_price,
        (SUM(`invoice_bill`) - (SUM((`invoice_bill`)) * SUM((IFNULL(`tax_percent`, 0))/100))) as report_total_tax
      '))
      ->where('status_cargo', 'selesai')
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('date_begin', [$date_begin, $date_end]);
      })
      ->groupBy('costumer_id')
      ->get();
    $config['page_title'] = "Laporan Rekap Job Order";
    $config['page_description'] = "Laporan Rekap Job Order";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Rekap Job Order"],
    ];

    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    return view('backend.report.reportrecapjoborder.print', compact('config', 'page_breadcrumbs', 'profile', 'data', 'date'));
  }
}
