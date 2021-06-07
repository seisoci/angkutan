<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\InvoicePurchase;
use App\Models\Setting;
use App\Models\SupplierSparepart;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class ReportRecapPurchaseOrderController extends Controller
{
  use CarbonTrait;

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Rekap Purchase Order";
    $config['page_description'] = "Laporan Rekap Purchase Order";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Rekap Purchase Order"],
    ];
    $config['excel_url'] = 'reportrecappurchaseorders/document?type=EXCEL';
    $config['pdf_url'] = 'reportrecappurchaseorders/document?type=PDF';
    $config['print_url'] = 'reportrecappurchaseorders/print';

    if ($request->ajax()) {
      $date = $request->date;
      $supplier_id = $request->supplier_id;

      $data = InvoicePurchase::with('supplier')
        ->when($date, function ($query, $date) {
          $date_format = explode(" / ", $date);
          $date_begin = $date_format[0];
          $date_end = $date_format[1];
          return $query->whereBetween('invoice_purchases.invoice_date', [$date_begin, $date_end]);
        })
        ->when($supplier_id, function ($query, $supplier_id) {
          return $query->where('invoice_purchases.supplier_sparepart_id', $supplier_id);
        })
        ->orderBy('invoice_date');
      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    return view('backend.report.reportrecappurchaseorders.index', compact('config', 'page_breadcrumbs'));
  }

  public function document(Request $request)
  {
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });

    $type = $request->type;
    $supplier_id = $request->supplier_id;
    $date = $request->date;
    $supplier = SupplierSparepart::find($supplier_id)->name ?? "All";

    $data = InvoicePurchase::with('supplier')
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('invoice_purchases.invoice_date', [$date_begin, $date_end]);
      })
      ->when($supplier_id, function ($query, $supplier_id) {
        return $query->where('invoice_purchases.supplier_sparepart_id', $supplier_id);
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
    $sheet->setCellValue('A1', 'Laporan Recap Purchase Order');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->mergeCells('A3:C3');
    $sheet->setCellValue('A3', 'Priode: ' . (!empty($date) ? $date : 'All Date'));
    $sheet->mergeCells('A4:C4');
    $sheet->setCellValue('A4', 'Nama Supplier: ' . $supplier);
    $sheet->mergeCells('G1:J1');
    $sheet->setCellValue('G1', $profile['name']);
    $sheet->mergeCells('G2:J2');
    $sheet->setCellValue('G2', $profile['address']);
    $sheet->mergeCells('G3:J3');
    $sheet->setCellValue('G3', 'Telp: ' . $profile['telp']);
    $sheet->mergeCells('G4:J4');
    $sheet->setCellValue('G4', 'Fax: ' . $profile['fax']);

    $sheet->getColumnDimension('A')->setWidth(3.55);
    $sheet->getColumnDimension('B')->setWidth(16);
    $sheet->getColumnDimension('C')->setWidth(25);
    $sheet->getColumnDimension('D')->setWidth(10);
    $sheet->getColumnDimension('E')->setWidth(12);
    $sheet->getColumnDimension('F')->setWidth(14);
    $sheet->getColumnDimension('G')->setWidth(14);
    $sheet->getColumnDimension('H')->setWidth(14);
    $sheet->getColumnDimension('I')->setWidth(14);
    $sheet->getColumnDimension('J')->setWidth(8);
    $sheet->getStyle('F2')->getAlignment()->setVertical(Alignment::VERTICAL_DISTRIBUTED);
    $sheet->getStyle('A7')->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A7', 'No.');
    $sheet->setCellValue('B7', 'No. Invoice');
    $sheet->setCellValue('C7', 'Nama Supplier');
    $sheet->setCellValue('D7', 'Tgl Invoice');
    $sheet->setCellValue('E7', 'Tgl Jth Tempo');
    $sheet->setCellValue('F7', 'Total Tagihan');
    $sheet->setCellValue('G7', 'Total Pembayaran');
    $sheet->setCellValue('H7', 'Diskon');
    $sheet->setCellValue('I7', 'Sisa Pembayaran');
    $sheet->setCellValue('J7', 'Metode');

    $startCell = 7;
    $startCellFilter = 7;
    $no = 1;
    $sheet->getStyle('A' . $startCell . ':J' . $startCell . '')
      ->applyFromArray($borderTopBottom);
    foreach ($data as $item):
      $startCell++;
      $sheet->getStyle('A' . $startCell . ':J' . $startCell . '')->applyFromArray($borderTopBottom);
      $sheet->getStyle('A' . $startCell . ':' . 'J' . $startCell)->getAlignment()->setVertical('top');
      $sheet->getStyle('F' . $startCell . ':J' . $startCell . '')->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->setCellValue('A' . $startCell, $no++);
      $sheet->setCellValue('B' . $startCell, $item->num_invoice);
      $sheet->setCellValue('C' . $startCell, $item->supplier->name);
      $sheet->setCellValue('D' . $startCell, $item->invoice_date);
      $sheet->setCellValue('E' . $startCell, $item->due_date);
      $sheet->setCellValue('F' . $startCell, $item->total_bill);
      $sheet->setCellValue('G' . $startCell, $item->total_payment);
      $sheet->setCellValue('H' . $startCell, $item->discount);
      $sheet->setCellValue('I' . $startCell, $item->rest_payment);
      $sheet->setCellValue('J' . $startCell, $item->method_payment);
    endforeach;
    $sheet->setAutoFilter('B' . $startCellFilter . ':J' . $startCell);
    $sheet->getStyle('A' . $startCell . ':J' . $startCell . '')->applyFromArray($borderBottom);
    $endForSum = $startCell;
    $startCell++;
    $startCellFilter++;
    $sheet->getStyle('A' . $startCell . ':J' . $startCell . '')->applyFromArray($borderTop)->applyFromArray(($borderBottom));
    $sheet->getStyle('F' . $startCell . ':J' . $startCell . '')->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('right');
    $sheet->setCellValue('E' . $startCell, 'Total Rp.');
    $sheet->mergeCells('A' . $startCell . ':E' . $startCell . '');
    $sheet->getStyle('A' . $startCell . ':J' . $startCell)->getFont()->setBold(true);
    $sheet->setCellValue('F' . $startCell, '=SUM(F' . $startCellFilter . ':F' . $endForSum . ')');
    $sheet->setCellValue('G' . $startCell, '=SUM(G' . $startCellFilter . ':G' . $endForSum . ')');
    $sheet->setCellValue('H' . $startCell, '=SUM(H' . $startCellFilter . ':H' . $endForSum . ')');
    $sheet->setCellValue('I' . $startCell, '=SUM(I' . $startCellFilter . ':I' . $endForSum . ')');

    $filename = 'Laporan Recap Purchase Order ' . $this->dateTimeNow();
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
    $config['page_title'] = "Laporan Rekap Purchase Order";
    $config['page_description'] = "Laporan Rekap Purchase Order";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Rekap Purchase Order"],
    ];

    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });

    $supplier_id = $request->supplier_id;
    $date = $request->date;
    $supplier = SupplierSparepart::find($supplier_id)->name ?? "All";

    $data = InvoicePurchase::with('supplier')
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('invoice_purchases.invoice_date', [$date_begin, $date_end]);
      })
      ->when($supplier_id, function ($query, $supplier_id) {
        return $query->where('invoice_purchases.supplier_sparepart_id', $supplier_id);
      })
      ->orderBy('invoice_date')
      ->get();
    return view('backend.report.reportrecappurchaseorders.print', compact('config', 'page_breadcrumbs', 'profile', 'data', 'date', 'supplier',));
  }
}
