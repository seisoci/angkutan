<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cooperation;
use App\Models\JobOrder;
use App\Traits\CarbonTrait;
use Carbon\Carbon;
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

  function __construct()
  {
    $this->middleware('permission:reportrecapsalaries-list|reportrecapsalaries-create|reportrecapsalaries-edit|reportrecapsalaries-delete', ['only' => ['index']]);
  }

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
      $status_salary = $request->status;
      $data = JobOrder::selectRaw('
          COUNT(`job_orders`.`costumer_id`)  AS `report_qty`,
         `costumers`.`name` AS `costumer_name`,
         `drivers`.`name` AS `driver_name`,
         `costumers`.`address` AS `costumer_address`,
         SUM(`total_salary`) AS `total_salary`
        ')
        ->leftJoin('costumers', 'costumers.id', '=', 'job_orders.costumer_id')
        ->leftJoin('drivers', 'drivers.id', '=', 'job_orders.driver_id')
        ->where('type', 'self')
        ->where('job_orders.status_cargo', 'selesai')
        ->when($date, function ($query, $date) {
          $date_begin = $date . "-01";
          $date_end = Carbon::createFromFormat('Y-m', $date)->endOfMonth()->toDateString();
          return $query->whereBetween('date_begin', [$date_begin, $date_end]);
        })
        ->when($driver_id, function ($query, $driver_id) {
          return $query->where('driver_id', $driver_id);
        })
        ->when($status_salary, function ($query, $status_salary) {
          if ($status_salary == 'none') {
            return $query->where('job_orders.status_salary', '=', '0');
          } else {
            return $query->where('job_orders.status_salary', $status_salary);
          }
        })
        ->groupBy('job_orders.driver_id');

      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    return view('backend.report.reportrecapsalaries.index', compact('config', 'page_breadcrumbs'));
  }

  public function document(Request $request)
  {
    $type = $request->type;
    $date = $request->date;
    $driver_id = $request->driver_id;
    $status_salary = $request->status;

    $data = JobOrder::selectRaw('
          COUNT(`job_orders`.`costumer_id`)  AS `report_qty`,
         `costumers`.`name` AS `costumer_name`,
         `drivers`.`name` AS `driver_name`,
         `costumers`.`address` AS `costumer_address`,
         SUM(`total_salary`) AS `total_salary`
      ')
      ->leftJoin('costumers', 'costumers.id', '=', 'job_orders.costumer_id')
      ->leftJoin('drivers', 'drivers.id', '=', 'job_orders.driver_id')
      ->where('type', 'self')
      ->where('job_orders.status_cargo', 'selesai')
      ->when($date, function ($query, $date) {
        $date_begin = $date . "-01";
        $date_end = Carbon::createFromFormat('Y-m', $date)->endOfMonth()->toDateString();
        return $query->whereBetween('date_begin', [$date_begin, $date_end]);
      })
      ->when($driver_id, function ($query, $driver_id) {
        return $query->where('driver_id', $driver_id);
      })
      ->when($status_salary, function ($query, $status_salary) {
        if ($status_salary == 'none') {
          return $query->where('job_orders.status_salary', '=', '0');
        } else {
          return $query->where('job_orders.status_salary', $status_salary);
        }
      })
      ->groupBy('job_orders.driver_id')
      ->get();

    if ($status_salary == 'none') {
      $status_salary = 'Unpaid';
    } elseif ($status_salary == "1") {
      $status_salary = 'Paid';
    } else {
      $status_salary = "All";
    }

    $cooperationDefault = Cooperation::where('default', '1')->first();

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
    $sheet->setCellValue('A1', 'Laporan Rekap Hutang Pelanggan');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->mergeCells('A3:C3');
    $sheet->setCellValue('A3', 'Priode: ' . (!empty($date) ? $date : 'All Date'));
    $sheet->mergeCells('A4:C4');
    $sheet->setCellValue('A4', 'Supir: ' . (!empty($driver) ? $driver->name : 'ALL'));
    $sheet->mergeCells('A5:C5');
    $sheet->setCellValue('A5', 'Supir: ' . $status_salary);
    $sheet->mergeCells('F1:H1');
    $sheet->setCellValue('F1', $cooperationDefault['nickname']);
    $sheet->mergeCells('F2:H2');
    $sheet->setCellValue('F2', $cooperationDefault['address']);
    $sheet->mergeCells('F3:H3');
    $sheet->setCellValue('F3', 'Telp: ' . $cooperationDefault['phone']);
    $sheet->mergeCells('F4:H4');
    $sheet->setCellValue('F4', 'Fax: ' . $cooperationDefault['fax']);

    $sheet->getColumnDimension('A')->setWidth(3.55);
    $sheet->getColumnDimension('B')->setWidth(35);
    $sheet->getColumnDimension('C')->setWidth(5);
    $sheet->getColumnDimension('D')->setWidth(20);

    $sheet->getStyle('A7')->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A7', 'No.');
    $sheet->setCellValue('B7', 'Nama Supir');
    $sheet->setCellValue('C7', 'Jml');
    $sheet->setCellValue('D7', 'Gaji Supir');

    $startCell = 7;
    $startCellFilter = 7;
    $no = 1;
    $sheet->getStyle('A' . $startCell . ':D' . $startCell . '')
      ->applyFromArray($borderTopBottom);
    foreach ($data as $item):
      $startCell++;
      $sheet->getStyle('A' . $startCell)->getAlignment()->setHorizontal('center');
      $sheet->getStyle('C' . $startCell)->getAlignment()->setHorizontal('center');
      $sheet->getStyle('D' . $startCell . ':D' . $startCell . '')->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->setCellValue('A' . $startCell, $no++);
      $sheet->setCellValue('B' . $startCell, $item->driver_name);
      $sheet->setCellValue('C' . $startCell, $item->report_qty);
      $sheet->setCellValue('D' . $startCell, $item->report_salary);
    endforeach;
    $sheet->setAutoFilter('B' . $startCellFilter . ':D' . $startCell);
    $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')->applyFromArray($borderBottom);

    $endForSum = $startCell;
    $startCell++;
    $startCellFilter++;
    $sheet->getStyle('A' . $startCell . ':D' . $startCell . '')->applyFromArray($borderTopBottom);
    $sheet->getStyle('D' . $startCell . ':D' . $startCell . '')->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('right');
    $sheet->setCellValue('A' . $startCell, 'Total Rp.');
    $sheet->mergeCells('A' . $startCell . ':C' . $startCell . '');
    $sheet->getStyle('A' . $startCell . ':D' . $startCell)->getFont()->setBold(true);
    $sheet->setCellValue('D' . $startCell, '=SUM(D' . $startCellFilter . ':D' . $endForSum . ')');

    $filename = 'Laporan Rekap Hutang Pelanggan ' . $this->dateTimeNow();
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
    $status_salary = $request->status;

      $data = JobOrder::selectRaw('
          COUNT(`job_orders`.`costumer_id`)  AS `report_qty`,
         `costumers`.`name` AS `costumer_name`,
         `drivers`.`name` AS `driver_name`,
         `costumers`.`address` AS `costumer_address`,
         SUM(`total_salary`) AS `total_salary`
      ')
      ->leftJoin('costumers', 'costumers.id', '=', 'job_orders.costumer_id')
      ->leftJoin('drivers', 'drivers.id', '=', 'job_orders.driver_id')
      ->where('type', 'self')
      ->where('job_orders.status_cargo', 'selesai')
      ->when($date, function ($query, $date) {
        $date_begin = $date . "-01";
        $date_end = Carbon::createFromFormat('Y-m', $date)->endOfMonth()->toDateString();
        return $query->whereBetween('date_begin', [$date_begin, $date_end]);
      })
      ->when($driver_id, function ($query, $driver_id) {
        return $query->where('driver_id', $driver_id);
      })
      ->when($status_salary, function ($query, $status_salary) {
        if ($status_salary == 'none') {
          return $query->where('job_orders.status_salary', '=', '0');
        } else {
          return $query->where('job_orders.status_salary', $status_salary);
        }
      })
      ->groupBy('job_orders.driver_id')
      ->get();

    if ($status_salary == 'none') {
      $status_salary = 'Unpaid';
    } elseif ($status_salary == "1") {
      $status_salary = 'Paid';
    } else {
      $status_salary = "All";
    }

    $config['page_title'] = "Laporan Rekap Hutang Pelanggan";
    $config['page_description'] = "Laporan Rekap Hutang Pelanggan";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Rekap Hutang Pelanggan"],
    ];

    $cooperationDefault = Cooperation::where('default', '1')->first();

    return view('backend.report.reportrecapsalaries.print', compact('config', 'page_breadcrumbs', 'cooperationDefault', 'data', 'date', 'status_salary'));
  }

}
