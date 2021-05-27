<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\JobOrder;
use App\Models\Setting;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;

class ReportSalaryController extends Controller
{

  use CarbonTrait;

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Rekap Gaji Supir";
    $config['page_description'] = "Laporan Rekap Gaji Supir";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Rekap Gaji Supir"],
    ];
    $config['excel_url'] = 'reportsalaries/document?type=EXCEL';
    $config['pdf_url'] = 'reportsalaries/document?type=PDF';
    $config['print_url'] = 'reportsalaries/print';

    if ($request->ajax()) {
      $date = $request->date;
      $driver_id = $request->driver_id;
      $data = JobOrder::with('costumer:id,name', 'driver:id,name')
        ->withSum('operationalexpense', 'amount')
        ->where('status_cargo', '=', 'selesai')
        ->where('type', 'self')
        ->whereNull('invoice_salary_id')
        ->when($date, function ($query, $date) {
          $date_format = explode(" / ", $date);
          $date_begin = $date_format[0];
          $date_end = $date_format[1];
          return $query->whereBetween('date_begin', [$date_begin, $date_end]);
        })
        ->when($driver_id, function ($query, $driver_id) {
          return $query->where('driver_id', $driver_id);
        });

      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    return view('backend.report.reportsalaries.index', compact('config', 'page_breadcrumbs'));
  }

  public function document(Request $request)
  {
    $type = $request->type;
    $date = $request->date;
    $driver_id = $request->driver_id;
    $driver = Driver::find($driver_id);
    $data = JobOrder::with('costumer:id,name', 'driver:id,name')
      ->withSum('operationalexpense', 'amount')
      ->where('status_cargo', '=', 'selesai')
      ->where('type', 'self')
      ->whereNull('invoice_salary_id')
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('date_begin', [$date_begin, $date_end]);
      })
      ->when($driver_id, function ($query, $driver_id) {
        return $query->where('driver_id', $driver_id);
      })
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
    $sheet->setCellValue('C6', 'T. Muat');
    $sheet->setCellValue('D6', 'Sub Total');
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
      $sheet->setCellValue('B' . $startCell, $item->costumer->name);
      $sheet->setCellValue('C' . $startCell, $item->date_begin);
      $sheet->setCellValue('D' . $startCell, $item->total_basic_price_after_thanks);
      $sheet->setCellValue('E' . $startCell, $item->total_operational);
      $sheet->setCellValue('F' . $startCell, $item->total_sparepart);
      $sheet->setCellValue('G' . $startCell, $item->total_salary);
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
    $data = JobOrder::with('costumer:id,name', 'driver:id,name')
      ->withSum('operationalexpense', 'amount')
      ->where('status_cargo', '=', 'selesai')
      ->where('type', 'self')
      ->whereNull('invoice_salary_id')
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('date_begin', [$date_begin, $date_end]);
      })
      ->when($driver_id, function ($query, $driver_id) {
        return $query->where('driver_id', $driver_id);
      })
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

    return view('backend.report.reportsalaries.print', compact('config', 'page_breadcrumbs', 'profile', 'data', 'date', 'driver'));
  }
}
