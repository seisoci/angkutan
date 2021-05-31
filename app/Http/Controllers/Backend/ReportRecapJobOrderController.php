<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
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

class ReportRecapJobOrderController extends Controller
{
  use CarbonTrait;

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Rekap Tagihan Job Order";
    $config['page_description'] = "Laporan Rekap Tagihan Job Order";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Rekap Tagihan Job Order"],
    ];
    $config['excel_url'] = 'reportrecapjoborders/document?type=EXCEL';
    $config['pdf_url'] = 'reportrecapjoborders/document?type=PDF';
    $config['print_url'] = 'reportrecapjoborders/print';

    if ($request->ajax()) {
      $date = $request->date;
      $data = DB::table('job_orders')
        ->select(
          DB::raw(
            'COUNT(`job_orders`.`costumer_id`)                                               AS `report_qty`,
       `costumers`.`name`                                                                         AS `costumer_name`,
       `costumers`.`address`                                                                      AS `costumer_address`,
       @basic_price := SUM(`job_orders`.`invoice_bill`)                                           AS `report_basic_price`,
       @total_operational := (SUM(`job_orders`.`road_money`) +
                              SUM(IFNULL(`operational_expenses`.`amount`, 0)))                    AS `report_operational`,
       @total_tax := SUM(`job_orders`.`invoice_bill` *
                         (IFNULL(`job_orders`.`tax_percent`, 0) / 100))                           AS `total_tax`,
       @percent_tax := SUM(IFNULL(`job_orders`.`tax_percent`, 0))                                 AS `tax_percent`,
       @total_basic_price_after_tax :=
               SUM(`job_orders`.`invoice_bill` - (`job_orders`.`invoice_bill` *
                                                  (IFNULL(`job_orders`.`tax_percent`, 0) / 100))) AS `report_basic_price_after_tax`,
       @fee_thanks := SUM(IFNULL(`job_orders`.`fee_thanks`, 0))                                   AS `fee_thanks`,
       @total_basic_price_after_thanks :=
               SUM(`job_orders`.`invoice_bill` - (`job_orders`.`invoice_bill` *
                                                  (IFNULL(`job_orders`.`tax_percent`, 0) / 100)) -
                   IFNULL(`job_orders`.`fee_thanks`, 0))
                                                                                                  AS `report_basic_price_after_thanks`
        '))
        ->leftJoin('costumers', 'costumers.id', '=', 'job_orders.costumer_id')
        ->leftJoin('operational_expenses', 'operational_expenses.job_order_id', '=', 'job_orders.id')
        ->where('job_orders.status_cargo', 'selesai')
        ->whereNull('job_orders.invoice_costumer_id')
        ->when($date, function ($query, $date) {
          $date_format = explode(" / ", $date);
          $date_begin = $date_format[0];
          $date_end = $date_format[1];
          return $query->whereBetween('date_begin', [$date_begin, $date_end]);
        })
        ->groupBy('job_orders.costumer_id');

      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    return view('backend.report.reportrecapjoborders.index', compact('config', 'page_breadcrumbs'));
  }

  public function document(Request $request)
  {
    $type = $request->type;
    $date = $request->date;
    $data = DB::table('job_orders')
      ->select(
        DB::raw(
          'COUNT(`job_orders`.`costumer_id`)                                               AS `report_qty`,
       `costumers`.`name`                                                                         AS `costumer_name`,
       `costumers`.`address`                                                                      AS `costumer_address`,
       @basic_price := SUM(`job_orders`.`invoice_bill`)                                           AS `report_basic_price`,
       @total_operational := (SUM(`job_orders`.`road_money`) +
                              SUM(IFNULL(`operational_expenses`.`amount`, 0)))                    AS `report_operational`,
       @total_tax := SUM(`job_orders`.`invoice_bill` *
                         (IFNULL(`job_orders`.`tax_percent`, 0) / 100))                           AS `total_tax`,
       @percent_tax := SUM(IFNULL(`job_orders`.`tax_percent`, 0))                                 AS `tax_percent`,
       @total_basic_price_after_tax :=
               SUM(`job_orders`.`invoice_bill` - (`job_orders`.`invoice_bill` *
                                                  (IFNULL(`job_orders`.`tax_percent`, 0) / 100))) AS `report_basic_price_after_tax`,
       @fee_thanks := SUM(IFNULL(`job_orders`.`fee_thanks`, 0))                                   AS `fee_thanks`,
       @total_basic_price_after_thanks :=
               SUM(`job_orders`.`invoice_bill` - (`job_orders`.`invoice_bill` *
                                                  (IFNULL(`job_orders`.`tax_percent`, 0) / 100)) -
                   IFNULL(`job_orders`.`fee_thanks`, 0))
                                                                                                  AS `report_basic_price_after_thanks`
        '))
      ->leftJoin('costumers', 'costumers.id', '=', 'job_orders.costumer_id')
      ->leftJoin('operational_expenses', 'operational_expenses.job_order_id', '=', 'job_orders.id')
      ->where('job_orders.status_cargo', 'selesai')
      ->whereNull('job_orders.invoice_costumer_id')
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('date_begin', [$date_begin, $date_end]);
      })
      ->groupBy('job_orders.costumer_id')
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
    $sheet->setCellValue('A1', 'Laporan Rekap Tagihan Job Order');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->mergeCells('A3:C3');
    $sheet->setCellValue('A3', 'Priode: ' . (!empty($date) ? $date : 'All Date'));
    $sheet->mergeCells('F1:H1');
    $sheet->setCellValue('F1', $profile['name']);
    $sheet->mergeCells('F2:H2');
    $sheet->setCellValue('F2', $profile['address']);
    $sheet->mergeCells('F3:H3');
    $sheet->setCellValue('F3', 'Telp: ' . $profile['telp']);
    $sheet->mergeCells('F4:H4');
    $sheet->setCellValue('F4', 'Fax: ' . $profile['fax']);

    $sheet->getColumnDimension('A')->setWidth(3.55);
    $sheet->getColumnDimension('B')->setWidth(40);
    $sheet->getColumnDimension('C')->setWidth(35);
    $sheet->getColumnDimension('D')->setWidth(8);
    $sheet->getColumnDimension('E')->setWidth(20);
    $sheet->getColumnDimension('F')->setWidth(20);
    $sheet->getColumnDimension('G')->setWidth(20);
    $sheet->getColumnDimension('H')->setWidth(20);

    $sheet->getStyle('A6')->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A6', 'No.');
    $sheet->setCellValue('B6', 'Nama Pelanggan');
    $sheet->setCellValue('C6', 'Alamat');
    $sheet->setCellValue('D6', 'Jumlah JO');
    $sheet->setCellValue('E6', 'Total');
    $sheet->setCellValue('F6', 'Total (Inc. Tax)');
    $sheet->setCellValue('G6', 'Fee Thanks');
    $sheet->setCellValue('H6', 'Total (Inc. Tax, Thanks)');

    $startCell = 6;
    $startCellFilter = 6;
    $no = 1;
    $sheet->getStyle('A' . $startCell . ':H' . $startCell . '')
      ->applyFromArray($borderTopBottom)
      ->applyFromArray($borderLeftRight);
    foreach ($data as $item):
      $startCell++;
      $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')->applyFromArray($borderLeftRight);
      $sheet->getStyle('D' . $startCell . ':H'.$startCell)->getAlignment()->setHorizontal('right');
      $sheet->getStyle('A' . $startCell)->getAlignment()->setHorizontal('center');
      $sheet->getStyle('D' . $startCell.':H'. $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->setCellValue('A' . $startCell, $no++);
      $sheet->setCellValue('B' . $startCell, $item->costumer_name);
      $sheet->setCellValue('C' . $startCell, $item->costumer_address);
      $sheet->setCellValue('D' . $startCell, $item->report_qty);
      $sheet->setCellValue('E' . $startCell, $item->report_basic_price);
      $sheet->setCellValue('F' . $startCell, $item->report_basic_price_after_tax);
      $sheet->setCellValue('G' . $startCell, $item->fee_thanks);
      $sheet->setCellValue('H' . $startCell, $item->report_basic_price_after_thanks);
    endforeach;
    $sheet->setAutoFilter('B' . $startCellFilter . ':F' . $startCell);
    $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')->applyFromArray($borderBottom);

    $endForSum = $startCell;
    $startCell++;
    $startCellFilter++;
    $sheet->getStyle('A' . $startCell . ':H' . $startCell . '')->applyFromArray($borderAll);
    $sheet->getStyle('E' . $startCell . ':H' . $startCell . '')->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('right');
    $sheet->setCellValue('A' . $startCell, 'Total Rp.');
    $sheet->mergeCells('A' . $startCell . ':C' . $startCell . '');
    $sheet->getStyle('A' . $startCell . ':H' . $startCell)->getFont()->setBold(true);
    $sheet->setCellValue('D' . $startCell, '=SUM(D' . $startCellFilter . ':D' . $endForSum . ')');
    $sheet->setCellValue('E' . $startCell, '=SUM(E' . $startCellFilter . ':E' . $endForSum . ')');
    $sheet->setCellValue('F' . $startCell, '=SUM(F' . $startCellFilter . ':F' . $endForSum . ')');
    $sheet->setCellValue('G' . $startCell, '=SUM(G' . $startCellFilter . ':G' . $endForSum . ')');
    $sheet->setCellValue('H' . $startCell, '=SUM(H' . $startCellFilter . ':H' . $endForSum . ')');
    $cellBasicPrice = "L" . $startCell;

    $filename = 'Laporan Rekap Tagihan Job Order ' . $this->dateTimeNow();
    if ($type == 'EXCEL') {
      $writer = new Xlsx($spreadsheet);
      header('Content-Type: application/vnd.ms-excel');
      header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
      header('Cache-Control: max-age=0');
    } elseif ($type == 'PDF') {
      $writer = new Mpdf($spreadsheet);
      header('Content-Type: application/pdf');
      header('Content-Disposition: inline;filename="' . $filename . '.pdf"');
      header('Cache-Control: max-age=0');
    }
    $writer->save('php://output');
    exit();
  }

  public function print(Request $request)
  {
    $date = $request->date;
    $data = DB::table('job_orders')
      ->select(
        DB::raw(
          'COUNT(`job_orders`.`costumer_id`)                                               AS `report_qty`,
       `costumers`.`name`                                                                         AS `costumer_name`,
       `costumers`.`address`                                                                      AS `costumer_address`,
       @basic_price := SUM(`job_orders`.`invoice_bill`)                                           AS `report_basic_price`,
       @total_operational := (SUM(`job_orders`.`road_money`) +
                              SUM(IFNULL(`operational_expenses`.`amount`, 0)))                    AS `report_operational`,
       @total_tax := SUM(`job_orders`.`invoice_bill` *
                         (IFNULL(`job_orders`.`tax_percent`, 0) / 100))                           AS `total_tax`,
       @percent_tax := SUM(IFNULL(`job_orders`.`tax_percent`, 0))                                 AS `tax_percent`,
       @total_basic_price_after_tax :=
               SUM(`job_orders`.`invoice_bill` - (`job_orders`.`invoice_bill` *
                                                  (IFNULL(`job_orders`.`tax_percent`, 0) / 100))) AS `report_basic_price_after_tax`,
       @fee_thanks := SUM(IFNULL(`job_orders`.`fee_thanks`, 0))                                   AS `fee_thanks`,
       @total_basic_price_after_thanks :=
               SUM(`job_orders`.`invoice_bill` - (`job_orders`.`invoice_bill` *
                                                  (IFNULL(`job_orders`.`tax_percent`, 0) / 100)) -
                   IFNULL(`job_orders`.`fee_thanks`, 0))
                                                                                                  AS `report_basic_price_after_thanks`
        '))
      ->leftJoin('costumers', 'costumers.id', '=', 'job_orders.costumer_id')
      ->leftJoin('operational_expenses', 'operational_expenses.job_order_id', '=', 'job_orders.id')
      ->where('job_orders.status_cargo', 'selesai')
      ->whereNull('job_orders.invoice_costumer_id')
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('date_begin', [$date_begin, $date_end]);
      })
      ->groupBy('job_orders.costumer_id')
      ->get();
    $config['page_title'] = "Laporan Rekap Tagihan Job Order";
    $config['page_description'] = "Laporan Rekap Tagihan Job Order";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Rekap Tagihan Job Order"],
    ];

    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    return view('backend.report.reportrecapjoborders.print', compact('config', 'page_breadcrumbs', 'profile', 'data', 'date'));
  }
}
