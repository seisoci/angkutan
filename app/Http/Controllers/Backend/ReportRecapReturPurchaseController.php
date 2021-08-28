<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cooperation;
use App\Models\InvoiceReturPurchase;
use App\Models\SupplierSparepart;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class ReportRecapReturPurchaseController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:reportrecapreturpurchases-list|reportrecapreturpurchases-create|reportrecapreturpurchases-edit|reportrecapreturpurchases-delete', ['only' => ['index']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Rekap Retur Purchase Order";
    $config['page_description'] = "Laporan Rekap Retur Purchase Order";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Rekap Retur Purchase Order"],
    ];
    $config['excel_url'] = 'reportrecapreturpurchases/document?type=EXCEL';
    $config['pdf_url'] = 'reportrecapreturpurchases/document?type=PDF';
    $config['print_url'] = 'reportrecapreturpurchases/print';

    if ($request->ajax()) {
      $date = $request->date;
      $supplier_id = $request->supplier_id;

      $data = InvoiceReturPurchase::with('supplier')
        ->when($date, function ($query, $date) {
          $date_format = explode(" / ", $date);
          $date_begin = $date_format[0];
          $date_end = $date_format[1];
          return $query->whereBetween('invoice_date', [$date_begin, $date_end]);
        })
        ->when($supplier_id, function ($query, $supplier_id) {
          return $query->where('supplier_sparepart_id', $supplier_id);
        })
        ->orderBy('invoice_date');
      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    return view('backend.report.reportrecapreturpurchases.index', compact('config', 'page_breadcrumbs'));
  }

  public function document(Request $request)
  {
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $type = $request->type;
    $supplier_id = $request->supplier_id;
    $date = $request->date;
    $supplier = SupplierSparepart::find($supplier_id)->name ?? "All";

    $data = InvoiceReturPurchase::with('supplier')
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('invoice_date', [$date_begin, $date_end]);
      })
      ->when($supplier_id, function ($query, $supplier_id) {
        return $query->where('supplier_sparepart_id', $supplier_id);
      })
      ->orderBy('invoice_date')
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
    $sheet->setCellValue('A1', 'Laporan Recap Retur Purchase Order');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->mergeCells('A3:C3');
    $sheet->setCellValue('A3', 'Priode: ' . (!empty($date) ? $date : 'All Date'));
    $sheet->mergeCells('A4:C4');
    $sheet->setCellValue('A4', 'Nama Supplier: ' . $supplier);
    $sheet->mergeCells('D1:E1');
    $sheet->setCellValue('D1', $cooperationDefault['nickname']);
    $sheet->mergeCells('D2:E2');
    $sheet->setCellValue('D2', $cooperationDefault['address']);
    $sheet->mergeCells('D3:E3');
    $sheet->setCellValue('D3', 'Telp: ' . $cooperationDefault['phone']);
    $sheet->mergeCells('D4:E4');
    $sheet->setCellValue('D4', 'Fax: ' . $cooperationDefault['fax']);

    $sheet->getColumnDimension('A')->setWidth(3.55);
    $sheet->getColumnDimension('B')->setWidth(16);
    $sheet->getColumnDimension('C')->setWidth(25);
    $sheet->getColumnDimension('D')->setWidth(30);
    $sheet->getColumnDimension('E')->setWidth(18);
    $sheet->getStyle('F2')->getAlignment()->setVertical(Alignment::VERTICAL_DISTRIBUTED);
    $sheet->getStyle('A7')->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A7', 'No.');
    $sheet->setCellValue('B7', 'No. Retur');
    $sheet->setCellValue('C7', 'Tgl Retur');
    $sheet->setCellValue('D7', 'Nama Supplier');
    $sheet->setCellValue('E7', 'Total Retur');

    $startCell = 7;
    $startCellFilter = 7;
    $no = 1;
    $sheet->getStyle('A' . $startCell . ':E' . $startCell . '')
      ->applyFromArray($borderTopBottom);
    foreach ($data as $item):
      $startCell++;
      $sheet->getStyle('A' . $startCell . ':E' . $startCell . '')->applyFromArray($borderTopBottom);
      $sheet->getStyle('A' . $startCell . ':' . 'E' . $startCell)->getAlignment()->setVertical('top');
      $sheet->getStyle('E' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->setCellValue('A' . $startCell, $no++);
      $sheet->setCellValue('B' . $startCell, $item->num_invoice);
      $sheet->setCellValue('C' . $startCell, $item->invoice_date);
      $sheet->setCellValue('D' . $startCell, $item->supplier->name);
      $sheet->setCellValue('E' . $startCell, $item->total_payment);
    endforeach;
    $sheet->setAutoFilter('B' . $startCellFilter . ':E' . $startCell);
    $sheet->getStyle('A' . $startCell . ':E' . $startCell . '')->applyFromArray($borderBottom);
    $endForSum = $startCell;
    $startCell++;
    $startCellFilter++;
    $sheet->getStyle('A' . $startCell . ':E' . $startCell . '')->applyFromArray($borderTop)->applyFromArray(($borderBottom));
    $sheet->getStyle('E' . $startCell . '')->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('A' . $startCell)->getAlignment()->setHorizontal('right');
    $sheet->setCellValue('E' . $startCell, 'Total Rp.');
    $sheet->mergeCells('A' . $startCell . ':D' . $startCell . '');
    $sheet->getStyle('A' . $startCell . ':E' . $startCell)->getFont()->setBold(true);
    $sheet->setCellValue('E' . $startCell, '=SUM(E' . $startCellFilter . ':E' . $endForSum . ')');

    $filename = 'Laporan Recap Retur Purchase Order ' . $this->dateTimeNow();
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
    $config['page_title'] = "Laporan Rekap Retur Purchase Order";
    $config['page_description'] = "Laporan Rekap Retur Purchase Order";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Rekap Retur Purchase Order"],
    ];

    $cooperationDefault = Cooperation::where('default', '1')->first();

    $supplier_id = $request->supplier_id;
    $date = $request->date;
    $supplier = SupplierSparepart::find($supplier_id)->name ?? "All";

    $data = InvoiceReturPurchase::with('supplier')
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('invoice_date', [$date_begin, $date_end]);
      })
      ->when($supplier_id, function ($query, $supplier_id) {
        return $query->where('supplier_sparepart_id', $supplier_id);
      })
      ->orderBy('invoice_date')
      ->get();
    return view('backend.report.reportrecapreturpurchases.print', compact('config', 'page_breadcrumbs', 'cooperationDefault', 'data', 'supplier', 'date',));
  }
}
