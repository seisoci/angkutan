<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Driver;
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
  use CarbonTrait;

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Rekap Gaji Supir";
    $config['page_description'] = "Laporan Rekap Gaji Supir";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Rekap Gaji Supir"],
    ];
    $config['excel_url'] = 'reportrecapsalaries/document?type=EXCEL';
    $config['pdf_url'] = 'reportrecapsalaries/document?type=PDF';
    $config['print_url'] = 'reportrecapsalaries/print';

    if ($request->ajax()) {
      $date = $request->date;
      $driver_id = $request->driver_id;
      $data = DB::table('job_orders')
        ->select(
          DB::raw(
            'COUNT(`job_orders`.`costumer_id`)                                                          AS `report_qty`,
       `costumers`.`name`                                                                         AS `costumer_name`,
       `drivers`.`name`                                                                           AS `driver_name`,
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
                                                                                                  AS `report_basic_price_after_thanks`,
       @total_sparepart := ((SUM(`job_orders`.`invoice_bill` -
                                 (`job_orders`.`invoice_bill` *
                                  (IFNULL(`job_orders`.`tax_percent`, 0) / 100)) -
                                 IFNULL(`job_orders`.`fee_thanks`, 0))) -
                            (SUM(`job_orders`.`road_money`) +
                             SUM(IFNULL(`operational_expenses`.`amount`, 0)))) *
                           (IFNULL(`job_orders`.`cut_sparepart_percent`, 0) / 100)                AS `report_total_sparepart`,
       @total_gaji := (((SUM(`job_orders`.`invoice_bill` -
                             (`job_orders`.`invoice_bill` *
                              (IFNULL(`job_orders`.`tax_percent`, 0) / 100)) -
                             IFNULL(`job_orders`.`fee_thanks`, 0))) -
                        (SUM(`job_orders`.`road_money`) +
                         SUM(IFNULL(`operational_expenses`.`amount`, 0)))) -
                       ((SUM(`job_orders`.`invoice_bill` -
                             (`job_orders`.`invoice_bill` *
                              (IFNULL(`job_orders`.`tax_percent`, 0) / 100)) -
                             IFNULL(`job_orders`.`fee_thanks`, 0))) -
                        (SUM(`job_orders`.`road_money`) +
                         SUM(IFNULL(`operational_expenses`.`amount`, 0)))) *
                       (IFNULL(`job_orders`.`cut_sparepart_percent`, 0) / 100)) *
                      (IFNULL(`job_orders`.`salary_percent`, 0) / 100)                            AS `report_salary`
        '))
        ->leftJoin('costumers', 'costumers.id', '=', 'job_orders.costumer_id')
        ->leftJoin('operational_expenses', 'operational_expenses.job_order_id', '=', 'job_orders.id')
        ->leftJoin('drivers', 'drivers.id', '=', 'job_orders.driver_id')
        ->where('job_orders.status_cargo', 'selesai')
        ->where('type', 'self')
        ->where('status_payment', '0')
        ->when($date, function ($query, $date) {
          $date_format = explode(" / ", $date);
          $date_begin = $date_format[0];
          $date_end = $date_format[1];
          return $query->whereBetween('date_begin', [$date_begin, $date_end]);
        })
        ->when($driver_id, function ($query, $driver_id) {
          return $query->where('driver_id', $driver_id);
        })
        ->groupBy('job_orders.costumer_id');

      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('total_netto', '{{$report_basic_price_after_thanks - $report_operational - $report_total_sparepart - $report_salary }}')
        ->make(true);
    }
    return view('backend.report.reportrecapsalaries.index', compact('config', 'page_breadcrumbs'));
  }

  public function document(Request $request)
  {
    $type = $request->type;
    $date = $request->date;
    $driver_id = $request->driver_id;
    $driver = Driver::find($driver_id);
    $data = DB::table('job_orders')
      ->select(
        DB::raw(
          'COUNT(`job_orders`.`costumer_id`)                                                          AS `report_qty`,
       `costumers`.`name`                                                                         AS `costumer_name`,
       `drivers`.`name`                                                                           AS `driver_name`,
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
                                                                                                  AS `report_basic_price_after_thanks`,
       @total_sparepart := ((SUM(`job_orders`.`invoice_bill` -
                                 (`job_orders`.`invoice_bill` *
                                  (IFNULL(`job_orders`.`tax_percent`, 0) / 100)) -
                                 IFNULL(`job_orders`.`fee_thanks`, 0))) -
                            (SUM(`job_orders`.`road_money`) +
                             SUM(IFNULL(`operational_expenses`.`amount`, 0)))) *
                           (IFNULL(`job_orders`.`cut_sparepart_percent`, 0) / 100)                AS `report_total_sparepart`,
       @total_gaji := (((SUM(`job_orders`.`invoice_bill` -
                             (`job_orders`.`invoice_bill` *
                              (IFNULL(`job_orders`.`tax_percent`, 0) / 100)) -
                             IFNULL(`job_orders`.`fee_thanks`, 0))) -
                        (SUM(`job_orders`.`road_money`) +
                         SUM(IFNULL(`operational_expenses`.`amount`, 0)))) -
                       ((SUM(`job_orders`.`invoice_bill` -
                             (`job_orders`.`invoice_bill` *
                              (IFNULL(`job_orders`.`tax_percent`, 0) / 100)) -
                             IFNULL(`job_orders`.`fee_thanks`, 0))) -
                        (SUM(`job_orders`.`road_money`) +
                         SUM(IFNULL(`operational_expenses`.`amount`, 0)))) *
                       (IFNULL(`job_orders`.`cut_sparepart_percent`, 0) / 100)) *
                      (IFNULL(`job_orders`.`salary_percent`, 0) / 100)                            AS `report_salary`
        '))
      ->leftJoin('costumers', 'costumers.id', '=', 'job_orders.costumer_id')
      ->leftJoin('operational_expenses', 'operational_expenses.job_order_id', '=', 'job_orders.id')
      ->leftJoin('drivers', 'drivers.id', '=', 'job_orders.driver_id')
      ->where('job_orders.status_cargo', 'selesai')
      ->where('type', 'self')
      ->where('status_payment', '0')
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('date_begin', [$date_begin, $date_end]);
      })
      ->when($driver_id, function ($query, $driver_id) {
        return $query->where('driver_id', $driver_id);
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
    $sheet->setCellValue('A1', 'Laporan Rekap Gaji Supir');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->mergeCells('A3:C3');
    $sheet->setCellValue('A3', 'Priode: ' . (!empty($date) ? $date : 'All Date'));
    $sheet->mergeCells('A4:C4');
    $sheet->setCellValue('A4', 'Supir: ' . (!empty($driver) ? $driver->name : 'ALL'));
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
    $sheet->getColumnDimension('C')->setWidth(15);
    $sheet->getColumnDimension('D')->setWidth(15);
    $sheet->getColumnDimension('E')->setWidth(15);
    $sheet->getColumnDimension('F')->setWidth(15);
    $sheet->getColumnDimension('G')->setWidth(15);
    $sheet->getColumnDimension('H')->setWidth(15);

    $sheet->getStyle('A6')->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A6', 'No.');
    $sheet->setCellValue('B6', 'Nama Pelanggan');
    $sheet->setCellValue('C6', 'Jml');
    $sheet->setCellValue('D6', 'Sub Total (Inc. Tax, Fee)');
    $sheet->setCellValue('E6', 'Biaya Operasional');
    $sheet->setCellValue('F6', 'Spare Part');
    $sheet->setCellValue('G6', 'Gaji Supir');
    $sheet->setCellValue('H6', 'Sisa Bersih');

    $startCell = 6;
    $startCellFilter = 6;
    $no = 1;
    $sheet->getStyle('A' . $startCell . ':H' . $startCell . '')
      ->applyFromArray($borderTopBottom)
      ->applyFromArray($borderLeftRight);
    foreach ($data as $item):
      $startCell++;
      $sheet->getStyle('A' . $startCell . ':H' . $startCell . '')->applyFromArray($borderLeftRight);
      $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('center');
      $sheet->getStyle('D' . $startCell . ':H' . $startCell . '')->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->setCellValue('A' . $startCell, $no++);
      $sheet->setCellValue('B' . $startCell, $item->costumer_name);
      $sheet->setCellValue('C' . $startCell, $item->report_qty);
      $sheet->setCellValue('D' . $startCell, $item->report_basic_price_after_tax);
      $sheet->setCellValue('E' . $startCell, $item->report_operational);
      $sheet->setCellValue('F' . $startCell, $item->report_total_sparepart);
      $sheet->setCellValue('G' . $startCell, $item->report_salary);
      $sheet->setCellValue('H' . $startCell, '=D' . $startCell . '-E' . $startCell . '-F' . $startCell . '-G' . $startCell . '');
    endforeach;
    $sheet->setAutoFilter('B' . $startCellFilter . ':F' . $startCell);
    $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')->applyFromArray($borderBottom);

    $endForSum = $startCell;
    $startCell++;
    $startCellFilter++;
    $sheet->getStyle('A' . $startCell . ':H' . $startCell . '')->applyFromArray($borderAll);
    $sheet->getStyle('D' . $startCell . ':H' . $startCell . '')->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('right');
    $sheet->setCellValue('A' . $startCell, 'Total Rp.');
    $sheet->mergeCells('A' . $startCell . ':C' . $startCell . '');
    $sheet->getStyle('A' . $startCell . ':H' . $startCell)->getFont()->setBold(true);
    $sheet->setCellValue('D' . $startCell, '=SUM(D' . $startCellFilter . ':D' . $endForSum . ')');
    $sheet->setCellValue('E' . $startCell, '=SUM(E' . $startCellFilter . ':E' . $endForSum . ')');
    $sheet->setCellValue('F' . $startCell, '=SUM(F' . $startCellFilter . ':F' . $endForSum . ')');
    $sheet->setCellValue('G' . $startCell, '=SUM(G' . $startCellFilter . ':G' . $endForSum . ')');
    $sheet->setCellValue('H' . $startCell, '=SUM(H' . $startCellFilter . ':H' . $endForSum . ')');

    $filename = 'Laporan Rekap Gaji Supir ' . $this->dateTimeNow();
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
    $driver_id = $request->driver_id;
    $driver = Driver::find($driver_id);
    $data = DB::table('job_orders')
      ->select(
        DB::raw(
          'COUNT(`job_orders`.`costumer_id`)                                                          AS `report_qty`,
       `costumers`.`name`                                                                         AS `costumer_name`,
       `drivers`.`name`                                                                           AS `driver_name`,
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
                                                                                                  AS `report_basic_price_after_thanks`,
       @total_sparepart := ((SUM(`job_orders`.`invoice_bill` -
                                 (`job_orders`.`invoice_bill` *
                                  (IFNULL(`job_orders`.`tax_percent`, 0) / 100)) -
                                 IFNULL(`job_orders`.`fee_thanks`, 0))) -
                            (SUM(`job_orders`.`road_money`) +
                             SUM(IFNULL(`operational_expenses`.`amount`, 0)))) *
                           (IFNULL(`job_orders`.`cut_sparepart_percent`, 0) / 100)                AS `report_total_sparepart`,
       @total_gaji := (((SUM(`job_orders`.`invoice_bill` -
                             (`job_orders`.`invoice_bill` *
                              (IFNULL(`job_orders`.`tax_percent`, 0) / 100)) -
                             IFNULL(`job_orders`.`fee_thanks`, 0))) -
                        (SUM(`job_orders`.`road_money`) +
                         SUM(IFNULL(`operational_expenses`.`amount`, 0)))) -
                       ((SUM(`job_orders`.`invoice_bill` -
                             (`job_orders`.`invoice_bill` *
                              (IFNULL(`job_orders`.`tax_percent`, 0) / 100)) -
                             IFNULL(`job_orders`.`fee_thanks`, 0))) -
                        (SUM(`job_orders`.`road_money`) +
                         SUM(IFNULL(`operational_expenses`.`amount`, 0)))) *
                       (IFNULL(`job_orders`.`cut_sparepart_percent`, 0) / 100)) *
                      (IFNULL(`job_orders`.`salary_percent`, 0) / 100)                            AS `report_salary`
        '))
      ->leftJoin('costumers', 'costumers.id', '=', 'job_orders.costumer_id')
      ->leftJoin('operational_expenses', 'operational_expenses.job_order_id', '=', 'job_orders.id')
      ->leftJoin('drivers', 'drivers.id', '=', 'job_orders.driver_id')
      ->where('job_orders.status_cargo', 'selesai')
      ->where('type', 'self')
      ->where('status_payment', '0')
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('date_begin', [$date_begin, $date_end]);
      })
      ->when($driver_id, function ($query, $driver_id) {
        return $query->where('driver_id', $driver_id);
      })
      ->groupBy('job_orders.costumer_id')
      ->get();

    $config['page_title'] = "Laporan Rekap Gaji Supir";
    $config['page_description'] = "Laporan Rekap Gaji Supir";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Rekap Gaji Supir"],
    ];

    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });

    return view('backend.report.reportrecapsalaries.print', compact('config', 'page_breadcrumbs', 'profile', 'data', 'date', 'driver'));
  }

}
