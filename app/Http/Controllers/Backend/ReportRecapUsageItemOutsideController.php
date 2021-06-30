<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\InvoiceUsageItem;
use App\Models\Setting;
use App\Models\Transport;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class ReportRecapUsageItemOutsideController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:reportrecapusageitemoutside-list|reportrecapusageitemoutside-create|reportrecapusageitemoutside-edit|reportrecapusageitemoutside-delete', ['only' => ['index']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Rekap Pembelian Baranng Diluar";
    $config['page_description'] = "Laporan Rekap Pembelian Baranng Diluar";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Rekap Pembelian Baranng Diluar"],
    ];
    $config['excel_url'] = 'reportrecapusageitemoutside/document?type=EXCEL';
    $config['pdf_url'] = 'reportrecapusageitemoutside/document?type=PDF';
    $config['print_url'] = 'reportrecapusageitemoutside/print';

    if ($request->ajax()) {
      $date = $request->date;
      $driver_id = $request->driver_id;
      $transport_id = $request->transport_id;

      $data = InvoiceUsageItem::with(['driver', 'transport'])
        ->withSum('usageitem', 'qty')
        ->where('type', 'outside')
        ->when($date, function ($query, $date) {
          $date_format = explode(" / ", $date);
          $date_begin = $date_format[0];
          $date_end = $date_format[1];
          return $query->whereBetween('invoice_date', [$date_begin, $date_end]);
        })
        ->when($driver_id, function ($query, $driver_id) {
          return $query->where('driver_id', $driver_id);
        })
        ->when($transport_id, function ($query, $transport_id) {
          return $query->where('transport_id', $transport_id);
        })
        ->orderBy('invoice_date', 'asc');
      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    return view('backend.report.reportrecapusageitemoutside.index', compact('config', 'page_breadcrumbs'));
  }

  public function document(Request $request)
  {
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });

    $type = $request->type;
    $date = $request->date;
    $driver_id = $request->driver_id;
    $transport_id = $request->transport_id;
    $transport = Transport::find($transport_id)->num_pol ?? "All";
    $driver = Driver::find($driver_id)->name ?? "All";

    $data = InvoiceUsageItem::with(['driver', 'transport'])
      ->withSum('usageitem', 'qty')
      ->where('type', 'outside')
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('invoice_date', [$date_begin, $date_end]);
      })
      ->when($driver_id, function ($query, $driver_id) {
        return $query->where('driver_id', $driver_id);
      })
      ->when($transport_id, function ($query, $transport_id) {
        return $query->where('transport_id', $transport_id);
      })
      ->orderBy('invoice_date', 'asc')
      ->get();

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->getPageSetup()
      ->setPaperSize(PageSetup::PAPERSIZE_A4)
      ->setOrientation(PageSetup::ORIENTATION_PORTRAIT);

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
        ]
      ],
    ];
    $borderTop = [
      'borders' => [
        'top' => [
          'borderStyle' => Border::BORDER_THIN,
        ],
      ],
    ];
    $borderOutline = [
      'borders' => [
        'outline' => [
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
    $borderHorizontal = [
      'borders' => [
        'outline' => [
          'borderStyle' => Border::BORDER_THIN,
        ],
      ],
    ];

    $sheet->mergeCells('A1:C1');
    $sheet->setCellValue('A1', 'Laporan Rekap Pembelian Baranng Diluar');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->mergeCells('A3:C3');
    $sheet->setCellValue('A3', 'Priode: ' . (!empty($date) ? $date : 'All Date'));
    $sheet->mergeCells('A4:C4');
    $sheet->setCellValue('A4', 'Nama Supir: ' . $driver);
    $sheet->mergeCells('A4:C4');
    $sheet->setCellValue('A4', 'No. Polisi: ' . $transport);
    $sheet->mergeCells('E1:G1');
    $sheet->setCellValue('E1', $profile['name']);
    $sheet->mergeCells('E2:G2');
    $sheet->setCellValue('E2', $profile['address']);
    $sheet->mergeCells('E3:G3');
    $sheet->setCellValue('E3', 'Telp: ' . $profile['telp']);
    $sheet->mergeCells('E4:G4');
    $sheet->setCellValue('E4', 'Fax: ' . $profile['fax']);

    $sheet->getColumnDimension('A')->setWidth(3.55);
    $sheet->getColumnDimension('B')->setWidth(16);
    $sheet->getColumnDimension('C')->setWidth(15);
    $sheet->getColumnDimension('D')->setWidth(30);
    $sheet->getColumnDimension('E')->setWidth(18);
    $sheet->getColumnDimension('F')->setWidth(18);
    $sheet->getColumnDimension('G')->setWidth(15);
    $sheet->getStyle('A7')->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A7', 'No.');
    $sheet->setCellValue('B7', 'No. Pembelian');
    $sheet->setCellValue('C7', 'Tgl Pembelian');
    $sheet->setCellValue('D7', 'Nama Supir');
    $sheet->setCellValue('E7', 'No. Polisi');
    $sheet->setCellValue('F7', 'Total Barang');
    $sheet->setCellValue('G7', 'Total Harga');

    $startCell = 7;
    $startCellFilter = 7;
    $no = 1;
    $sheet->getStyle('A' . $startCell . ':G' . $startCell . '')
      ->applyFromArray($borderTopBottom);
    foreach ($data as $item):
      $startCell++;
      $sheet->getStyle('A' . $startCell . ':G' . $startCell . '')->applyFromArray($borderTopBottom);
      $sheet->getStyle('A' . $startCell . ':' . 'F' . $startCell)->getAlignment()->setVertical('top');
      $sheet->getStyle('G' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->getStyle('F' . $startCell)->getAlignment()->setVertical('top');
      $sheet->setCellValue('A' . $startCell, $no++);
      $sheet->setCellValue('B' . $startCell, $item->num_invoice);
      $sheet->setCellValue('C' . $startCell, $item->invoice_date);
      $sheet->setCellValue('D' . $startCell, $item->driver->name);
      $sheet->setCellValue('E' . $startCell, $item->transport->num_pol);
      $sheet->setCellValue('F' . $startCell, $item->usageitem_sum_qty);
      $sheet->setCellValue('G' . $startCell, $item->total_payment);
    endforeach;
    $sheet->setAutoFilter('B' . $startCellFilter . ':G' . $startCell);
    $sheet->getStyle('A' . $startCell . ':G' . $startCell . '')->applyFromArray($borderBottom);
    $endForSum = $startCell;
    $startCell++;
    $startCellFilter++;
    $sheet->getStyle('A' . $startCell . ':G' . $startCell . '')->applyFromArray($borderTop)->applyFromArray(($borderBottom));
    $sheet->getStyle('A' . $startCell)->getAlignment()->setHorizontal('right');
    $sheet->getStyle('F' . $startCell)->getAlignment()->setHorizontal('right');
    $sheet->setCellValue('A' . $startCell, 'Total');
    $sheet->mergeCells('A' . $startCell . ':E' . $startCell . '');
    $sheet->getStyle('A' . $startCell . ':G' . $startCell)->getFont()->setBold(true);
    $sheet->getStyle('G' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->setCellValue('F' . $startCell, '=SUM(F' . $startCellFilter . ':F' . $endForSum . ')');
    $sheet->setCellValue('G' . $startCell, '=SUM(G' . $startCellFilter . ':G' . $endForSum . ')');

    $filename = 'Laporan Rekap Pembelian Baranng Diluar ' . $this->dateTimeNow();
    if ($type == 'EXCEL') {
      $writer = new Xlsx($spreadsheet);
      header('Cache-Control: no-store, no-cache, must-revalidate');
      header('Content-Type: application/vnd.ms-excel');
      header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
      header('Cache-Control: max-age=0');
    } elseif ($type == 'PDF') {
      $writer = new Mpdf($spreadsheet);
      header('Cache-Control: no-store, no-cache, must-revalidate');
      header('Content-Type: application/pdf');
      header('Content-Disposition: attachment;filename="' . $filename . '.pdf"');
      header('Cache-Control: max-age=0');
    }
    $writer->save('php://output');
    exit();
  }

  public function print(Request $request)
  {
    $config['page_title'] = "Laporan Rekap Pembelian Baranng Diluar";
    $config['page_description'] = "Laporan Rekap Pembelian Baranng Diluar";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Rekap Pembelian Baranng Diluar"],
    ];

    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    $date = $request->date;
    $driver_id = $request->driver_id;
    $transport_id = $request->transport_id;
    $transport = Transport::find($transport_id)->num_pol ?? "All";
    $driver = Driver::find($driver_id)->name ?? "All";

    $data = InvoiceUsageItem::with(['driver', 'transport'])
      ->withSum('usageitem', 'qty')
      ->where('type', 'outside')
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('invoice_date', [$date_begin, $date_end]);
      })
      ->when($driver_id, function ($query, $driver_id) {
        return $query->where('driver_id', $driver_id);
      })
      ->when($transport_id, function ($query, $transport_id) {
        return $query->where('transport_id', $transport_id);
      })
      ->orderBy('invoice_date', 'asc')
      ->get();
    return view('backend.report.reportrecapusageitemoutside.print', compact('config', 'page_breadcrumbs', 'profile', 'data', 'date', 'driver', 'transport'));
  }
}
