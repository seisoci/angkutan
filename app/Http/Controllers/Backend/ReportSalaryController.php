<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cooperation;
use App\Models\Driver;
use App\Models\JobOrder;
use App\Traits\CarbonTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class ReportSalaryController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:reportsalarydrivers-list|reportsalarydrivers-create|reportsalarydrivers-edit|reportsalarydrivers-delete', ['only' => ['index']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Gaji Supir";
    $config['page_description'] = "Laporan Gaji Supir";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Gaji Supir"],
    ];
    $config['excel_url'] = 'reportsalarydrivers/document?type=EXCEL';
    $config['pdf_url'] = 'reportsalarydrivers/document?type=PDF';
    $config['print_url'] = 'reportsalarydrivers/print';

    if ($request->ajax()) {
      $date = $request->date;
      $driver_id = $request->driver_id;
      $customer_id = $request->customer_id;
      $status_salary = $request->status;
      $data = JobOrder::with(
          'costumer:id,name',
          'driver:id,name',
          'routefrom:id,name',
          'routeto:id,name',
          'cargo:id,name'
        )
        ->where('type', 'self')
        ->where('status_cargo', 'selesai')
        ->when($date, function ($query, $date) {
          $date_begin = $date."-01";
          $date_end = Carbon::createFromFormat('Y-m', $date)->endOfMonth()->toDateString();
          return $query->whereBetween('date_begin', [$date_begin, $date_end]);
        })
        ->when($status_salary, function ($query, $status_salary) {
          if ($status_salary == 'none') {
            return $query->where('job_orders.status_salary', '=', '0');
          } else {
            return $query->where('job_orders.status_salary', $status_salary);
          }
        })
        ->when($driver_id, function ($query, $driver_id) {
          return $query->where('driver_id', $driver_id);
        })
        ->when($customer_id, function ($query, $customer_id) {
          return $query->where('costumer_id', $customer_id);
        });

      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    return view('backend.report.reportsalarydrivers.index', compact('config', 'page_breadcrumbs'));
  }

  public function print(Request $request)
  {
    $date = $request->date;
    $driver_id = $request->driver_id;
    $driver = Driver::find($driver_id);
    $status_salary = $request->status;
    $data = JobOrder::with(
      'costumer:id,name',
      'driver:id,name',
      'routefrom:id,name',
      'routeto:id,name',
      'cargo:id,name'
    )
      ->where('type', 'self')
      ->where('status_cargo', 'selesai')
      ->when($date, function ($query, $date) {
        $date_begin = $date."-01";
        $date_end = Carbon::createFromFormat('Y-m', $date)->endOfMonth()->toDateString();
        return $query->whereBetween('date_begin', [$date_begin, $date_end]);
      })
      ->when($status_salary, function ($query, $status_salary) {
        if ($status_salary == 'none') {
          return $query->where('job_orders.status_salary', '=', '0');
        } else {
          return $query->where('job_orders.status_salary', $status_salary);
        }
      })
      ->when($driver_id, function ($query, $driver_id) {
        return $query->where('driver_id', $driver_id);
      })
      ->get();

    if ($status_salary == 'none') {
      $status_salary = 'Unpaid';
    } elseif ($status_salary == "1") {
      $status_salary = 'Paid';
    } else {
      $status_salary = "All";
    }

    $config['page_title'] = "Laporan Gaji Supir";
    $config['page_description'] = "Laporan Gaji Supir";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Gaji Supir"],
    ];

    $cooperationDefault = Cooperation::where('default', '1')->first();

    return view('backend.report.reportsalarydrivers.print', compact('config', 'page_breadcrumbs', 'cooperationDefault', 'data', 'date', 'driver', 'status_salary'));
  }

  public function document(Request $request)
  {
    $type = $request->type;
    $date = $request->date;
    $driver_id = $request->driver_id;
    $driver = Driver::find($driver_id);
    $status_salary = $request->status;
    $data = JobOrder::with(
        'costumer:id,name',
        'driver:id,name',
        'routefrom:id,name',
        'routeto:id,name',
        'cargo:id,name'
      )
      ->where('type', 'self')
      ->where('status_cargo', 'selesai')
      ->when($date, function ($query, $date) {
        $date_begin = $date."-01";
        $date_end = Carbon::createFromFormat('Y-m', $date)->endOfMonth()->toDateString();
        return $query->whereBetween('date_begin', [$date_begin, $date_end]);
      })
      ->when($status_salary, function ($query, $status_salary) {
        if ($status_salary == 'none') {
          return $query->where('job_orders.status_salary', '=', '0');
        } else {
          return $query->where('job_orders.status_salary', $status_salary);
        }
      })
      ->when($driver_id, function ($query, $driver_id) {
        return $query->where('driver_id', $driver_id);
      })
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
    $sheet->setCellValue('A1', 'Laporan Gaji Supir');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->mergeCells('A3:C3');
    $sheet->setCellValue('A3', 'Priode: ' . (!empty($date) ? $date : 'All Date'));
    $sheet->mergeCells('A4:C4');
    $sheet->setCellValue('A4', 'Supir: ' . (!empty($driver) ? $driver->name : 'ALL'));
    $sheet->mergeCells('A5:C5');
    $sheet->setCellValue('A5', 'Status Gaji: ' . $status_salary);
    $sheet->mergeCells('E1:G1');
    $sheet->setCellValue('E1', $cooperationDefault['nickname']);
    $sheet->mergeCells('E2:G2');
    $sheet->setCellValue('E2', $cooperationDefault['address']);
    $sheet->mergeCells('E3:G3');
    $sheet->setCellValue('E3', 'Telp: ' . $cooperationDefault['phone']);
    $sheet->mergeCells('E4:G4');
    $sheet->setCellValue('E4', 'Fax: ' . $cooperationDefault['fax']);

    $sheet->getColumnDimension('A')->setWidth(3.55);
    $sheet->getColumnDimension('B')->setWidth(20);
    $sheet->getColumnDimension('C')->setWidth(40);
    $sheet->getColumnDimension('D')->setWidth(15);
    $sheet->getColumnDimension('E')->setWidth(15);
    $sheet->getColumnDimension('F')->setWidth(15);
    $sheet->getColumnDimension('G')->setWidth(15);
    $sheet->getColumnDimension('H')->setWidth(15);

    $sheet->getStyle('A7')->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A7', 'No.');
    $sheet->setCellValue('B7', 'Nama Supir');
    $sheet->setCellValue('C7', 'Nama Pelanggan');
    $sheet->setCellValue('D7', 'T. Muat');
    $sheet->setCellValue('E7', 'Dari');
    $sheet->setCellValue('F7', 'Tujuan');
    $sheet->setCellValue('G7', 'Gaji Supir');
    $sheet->setCellValue('H7', 'Status');

    $startCell = 7;
    $startCellFilter = 7;
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
      $sheet->setCellValue('B' . $startCell, $item->driver->name);
      $sheet->setCellValue('C' . $startCell, $item->costumer->name);
      $sheet->setCellValue('D' . $startCell, $item->date_begin);
      $sheet->setCellValue('E' . $startCell, $item->routefrom->name);
      $sheet->setCellValue('F' . $startCell, $item->routeto->name);
      $sheet->setCellValue('G' . $startCell, $item->total_salary);
      $sheet->setCellValue('H' . $startCell, $item->status_salary == 0 ? 'Unpaid' : 'Paid');
    endforeach;
    $sheet->setAutoFilter('B' . $startCellFilter . ':H' . $startCell);
    $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')->applyFromArray($borderBottom);

    $endForSum = $startCell;
    $startCell++;
    $startCellFilter++;
    $sheet->getStyle('A' . $startCell . ':H' . $startCell . '')->applyFromArray($borderAll);
    $sheet->getStyle('E' . $startCell . ':H' . $startCell . '')->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('right');
    $sheet->setCellValue('A' . $startCell, 'Total Rp.');
    $sheet->mergeCells('A' . $startCell . ':D' . $startCell . '');
    $sheet->getStyle('A' . $startCell . ':H' . $startCell)->getFont()->setBold(true);
    $sheet->setCellValue('G' . $startCell, '=SUM(G' . $startCellFilter . ':G' . $endForSum . ')');

    $filename = 'Laporan Gaji Supir ' . $this->dateTimeNow();
    if ($type == 'EXCEL') {
      $writer = new Xlsx($spreadsheet);
      header('Content-Type: application/vnd.ms-excel');
      header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
      header('Cache-Control: max-age=0');
    } elseif ($type == 'PDF') {
      $writer = new Mpdf($spreadsheet);
      header('Content-Type: application/pdf');
      header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
      header('Cache-Control: private');
    }
    $writer->save('php://output');
    exit();
  }
}
