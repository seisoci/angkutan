<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cooperation;
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

class ReportRecapUsageItemsController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:reportrecapusageitems-list|reportrecapusageitems-create|reportrecapusageitems-edit|reportrecapusageitems-delete', ['only' => ['index']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Rekap Pemakaian Barang";
    $config['page_description'] = "Laporan Rekap Pemakaian Barang";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Rekap Pemakaian Barang"],
    ];
    $config['excel_url'] = 'reportrecapusageitems/document?type=EXCEL';
    $config['pdf_url'] = 'reportrecapusageitems/document?type=PDF';
    $config['print_url'] = 'reportrecapusageitems/print';

    if ($request->ajax()) {
      $date = $request->date;
      $driver_id = $request->driver_id;
      $transport_id = $request->transport_id;

      $data = InvoiceUsageItem::with(['driver', 'transport'])
        ->withSum('usageitem', 'qty')
        ->where('type', 'self')
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
    return view('backend.report.reportrecapusageitems.index', compact('config', 'page_breadcrumbs'));
  }

  public function document(Request $request)
  {
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $type = $request->type;
    $date = $request->date;
    $driver_id = $request->driver_id;
    $transport_id = $request->transport_id;
    $transport = Transport::find($transport_id)->num_pol ?? "All";
    $driver = Driver::find($driver_id)->name ?? "All";

    $data = InvoiceUsageItem::with(['driver', 'transport'])
      ->withSum('usageitem', 'qty')
      ->where('type', 'self')
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
    $sheet->setCellValue('A1', 'Laporan Rekap Pemakaian Barang');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->mergeCells('A3:C3');
    $sheet->setCellValue('A3', 'Priode: ' . (!empty($date) ? $date : 'All Date'));
    $sheet->mergeCells('A4:C4');
    $sheet->setCellValue('A4', 'Nama Supir: ' . $driver);
    $sheet->mergeCells('A4:C4');
    $sheet->setCellValue('A4', 'No. Polisi: ' . $transport);
    $sheet->mergeCells('F1:G1');
    $sheet->setCellValue('F1', $cooperationDefault['nickname']);
    $sheet->mergeCells('F2:G2');
    $sheet->setCellValue('F2', $cooperationDefault['address']);
    $sheet->mergeCells('F3:G3');
    $sheet->setCellValue('F3', 'Telp: ' . $cooperationDefault['phone']);
    $sheet->mergeCells('F4:G4');
    $sheet->setCellValue('F4', 'Fax: ' . $cooperationDefault['fax']);

    $sheet->getColumnDimension('A')->setWidth(3.55);
    $sheet->getColumnDimension('B')->setWidth(16);
    $sheet->getColumnDimension('C')->setWidth(15);
    $sheet->getColumnDimension('D')->setWidth(30);
    $sheet->getColumnDimension('E')->setWidth(18);
    $sheet->getColumnDimension('F')->setWidth(18);
    $sheet->getColumnDimension('G')->setWidth(18);

    $sheet->getRowDimension('2')->setRowHeight(30);
    $sheet->getStyle('E2')->getAlignment()->setVertical(Alignment::VERTICAL_DISTRIBUTED);
    $sheet->getStyle('A7')->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A7', 'No.');
    $sheet->setCellValue('B7', 'No. Pemakaian');
    $sheet->setCellValue('C7', 'Tgl Pemakaian');
    $sheet->setCellValue('D7', 'Nama Supir');
    $sheet->setCellValue('E7', 'No. Polisi');
    $sheet->setCellValue('F7', 'Total Pemakaian');
    $sheet->setCellValue('G7', 'Total');

    $startCell = 7;
    $startCellFilter = 7;
    $no = 1;
    $sheet->getStyle('A' . $startCell . ':G' . $startCell . '')
      ->applyFromArray($borderTopBottom);
    foreach ($data as $item):
      $startCell++;
      $sheet->getStyle('G' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->getStyle('A' . $startCell . ':G' . $startCell . '')->applyFromArray($borderTopBottom);
      $sheet->getStyle('A' . $startCell . ':G' . $startCell)->getAlignment()->setVertical('top');
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
    $sheet->getStyle('G' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('A' . $startCell . ':G' . $startCell . '')->applyFromArray($borderTop)->applyFromArray(($borderBottom));
    $sheet->getStyle('A' . $startCell)->getAlignment()->setHorizontal('right');
    $sheet->getStyle('F' . $startCell)->getAlignment()->setHorizontal('right');
    $sheet->setCellValue('A' . $startCell, 'Total');
    $sheet->mergeCells('A' . $startCell . ':E' . $startCell . '');
    $sheet->getStyle('A' . $startCell . ':G' . $startCell)->getFont()->setBold(true);
    $sheet->setCellValue('F' . $startCell, '=SUM(F' . $startCellFilter . ':F' . $endForSum . ')');
    $sheet->setCellValue('G' . $startCell, '=SUM(G' . $startCellFilter . ':G' . $endForSum . ')');

    $filename = 'Laporan Rekap Pemakaian Barang ' . $this->dateTimeNow();
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
    $config['page_title'] = "Laporan Rekap Pemakaian Barang";
    $config['page_description'] = "Laporan Rekap Pemakaian Barang";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Rekap Pemakaian Barang"],
    ];

    $cooperationDefault = Cooperation::where('default', '1')->first();

    $date = $request->date;
    $driver_id = $request->driver_id;
    $transport_id = $request->transport_id;
    $transport = Transport::find($transport_id)->num_pol ?? "All";
    $driver = Driver::find($driver_id)->name ?? "All";

    $data = InvoiceUsageItem::with(['driver', 'transport'])
      ->withSum('usageitem', 'qty')
      ->where('type', 'self')
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
    return view('backend.report.reportrecapusageitems.print', compact('config', 'page_breadcrumbs', 'cooperationDefault', 'data', 'date', 'driver', 'transport'));
  }
}
