<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
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

class ReportReturPurchaseController extends Controller
{
  use CarbonTrait;

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Retur Purchase Order";
    $config['page_description'] = "Laporan Retur Purchase Order";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Retur Purchase Order"],
    ];
    $config['excel_url'] = 'reportreturpurchases/document?type=EXCEL';
    $config['pdf_url'] = 'reportreturpurchases/document?type=PDF';
    $config['print_url'] = 'reportreturpurchases/print';

    if ($request->ajax()) {
      $date = $request->date;
      $supplier_id = $request->supplier_id;

      $data = DB::table('retur_purchases')
        ->select(DB::raw('
        CONCAT(`invoice_retur_purchases`.`prefix`,"-",`invoice_retur_purchases`.`num_bill`) AS num_invoice,
        `spareparts`.`name` AS `sparepart_name`,
        `supplier_spareparts`.`name` AS `supplier_name`,
        `retur_purchases`.`qty`,
        `retur_purchases`.`price`,
        (`retur_purchases`.`price` * `retur_purchases`.`qty`) AS `total`,
        `invoice_retur_purchases`.`invoice_date` AS `invoice_date`
        '))
        ->leftJoin('invoice_retur_purchases', 'invoice_retur_purchases.id', '=', 'retur_purchases.invoice_retur_purchase_id')
        ->leftJoin('spareparts', 'spareparts.id', '=', 'retur_purchases.sparepart_id')
        ->leftJoin('supplier_spareparts', 'supplier_spareparts.id', '=', 'retur_purchases.supplier_sparepart_id')
        ->when($date, function ($query, $date) {
          $date_format = explode(" / ", $date);
          $date_begin = $date_format[0];
          $date_end = $date_format[1];
          return $query->whereBetween('invoice_retur_purchases.invoice_date', [$date_begin, $date_end]);
        })
        ->when($supplier_id, function ($query, $supplier_id) {
          return $query->where('invoice_retur_purchases.supplier_sparepart_id', $supplier_id);
        })
        ->when($date, function ($query, $date) {
          $date_format = explode(" / ", $date);
          $date_begin = $date_format[0];
          $date_end = $date_format[1];
          return $query->whereBetween('invoice_retur_purchases.invoice_date', [$date_begin, $date_end]);
        })
        ->orderBy('invoice_retur_purchases.invoice_date');
      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    return view('backend.report.reportpurchaseorders.index', compact('config', 'page_breadcrumbs'));
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

    $data = DB::table('purchases')
      ->select(DB::raw('
        CONCAT(`invoice_purchases`.`prefix`,"-",`invoice_purchases`.`num_bill`) AS num_invoice,
        `spareparts`.`name` AS `sparepart_name`,
        `supplier_spareparts`.`name` AS `supplier_name`,
        `purchases`.`qty`,
        `purchases`.`price`,
        (`purchases`.`price` * `purchases`.`qty`) AS `total`,
        `invoice_purchases`.`invoice_date` AS `invoice_date`
        '))
      ->leftJoin('invoice_purchases', 'invoice_purchases.id', '=', 'purchases.invoice_purchase_id')
      ->leftJoin('spareparts', 'spareparts.id', '=', 'purchases.sparepart_id')
      ->leftJoin('supplier_spareparts', 'supplier_spareparts.id', '=', 'purchases.supplier_sparepart_id')
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('invoice_purchases.invoice_date', [$date_begin, $date_end]);
      })
      ->when($supplier_id, function ($query, $supplier_id) {
        return $query->where('invoice_purchases.supplier_sparepart_id', $supplier_id);
      })
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('invoice_purchases.invoice_date', [$date_begin, $date_end]);
      })
      ->orderBy('invoice_purchases.invoice_date')
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
    $sheet->setCellValue('A1', 'Laporan Retur Purchase Order');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->mergeCells('A3:C3');
    $sheet->setCellValue('A3', 'Priode: ' . (!empty($date) ? $date : 'All Date'));
    $sheet->mergeCells('A4:C4');
    $sheet->setCellValue('A4', 'Nama Supplier: ' . $supplier);
    $sheet->mergeCells('F1:H1');
    $sheet->setCellValue('F1', $profile['name']);
    $sheet->mergeCells('F2:H2');
    $sheet->setCellValue('F2', $profile['address']);
    $sheet->mergeCells('F3:H3');
    $sheet->setCellValue('F3', 'Telp: ' . $profile['telp']);
    $sheet->mergeCells('F4:H4');
    $sheet->setCellValue('F4', 'Fax: ' . $profile['fax']);

    $sheet->getColumnDimension('A')->setWidth(3.55);
    $sheet->getColumnDimension('B')->setWidth(16);
    $sheet->getColumnDimension('C')->setWidth(14);
    $sheet->getColumnDimension('D')->setWidth(26);
    $sheet->getColumnDimension('E')->setWidth(26);
    $sheet->getColumnDimension('F')->setWidth(8);
    $sheet->getColumnDimension('G')->setWidth(15);
    $sheet->getColumnDimension('H')->setWidth(15);
    $sheet->getRowDimension('2')->setRowHeight(30);
    $sheet->getStyle('F2')->getAlignment()->setVertical(Alignment::VERTICAL_DISTRIBUTED);

    $sheet->getStyle('A7')->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A7', 'No.');
    $sheet->setCellValue('B7', 'No. Invoice');
    $sheet->setCellValue('C7', 'Tgl Invoice');
    $sheet->setCellValue('D7', 'Nama Sparepart');
    $sheet->setCellValue('E7', 'Nama Supplier');
    $sheet->setCellValue('F7', 'Jumlah');
    $sheet->setCellValue('G7', 'Harga');
    $sheet->setCellValue('H7', 'Total');

    $startCell = 7;
    $startCellFilter = 7;
    $no = 1;
    $sheet->getStyle('A' . $startCell . ':H' . $startCell . '')
      ->applyFromArray($borderTopBottom);
    foreach ($data as $item):
      $startCell++;
      $sheet->getStyle('A' . $startCell . ':H' . $startCell . '')->applyFromArray($borderTopBottom);
      $sheet->getStyle('A' . $startCell . ':' . 'F' . $startCell)->getAlignment()->setVertical('top');
      $sheet->getStyle('G' . $startCell . ':H' . $startCell . '')->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->setCellValue('A' . $startCell, $no++);
      $sheet->setCellValue('B' . $startCell, $item->num_invoice);
      $sheet->setCellValue('C' . $startCell, $item->invoice_date);
      $sheet->setCellValue('D' . $startCell, $item->sparepart_name);
      $sheet->setCellValue('E' . $startCell, $item->supplier_name);
      $sheet->setCellValue('F' . $startCell, $item->qty);
      $sheet->setCellValue('G' . $startCell, $item->price);
      $sheet->setCellValue('H' . $startCell, $item->total);
    endforeach;
    $sheet->setAutoFilter('B' . $startCellFilter . ':H' . $startCell);
    $sheet->getStyle('A' . $startCell . ':H' . $startCell . '')->applyFromArray($borderBottom);
    $endForSum = $startCell;
    $startCell++;
    $startCellFilter++;
    $sheet->getStyle('A' . $startCell . ':H' . $startCell . '')->applyFromArray($borderTop)->applyFromArray($borderBottom);
    $sheet->getStyle('G' . $startCell . ':H' . $startCell . '')->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('right');
    $sheet->setCellValue('E' . $startCell, 'Total Rp.');
    $sheet->mergeCells('A' . $startCell . ':E' . $startCell . '');
    $sheet->getStyle('A' . $startCell . ':H' . $startCell)->getFont()->setBold(true);
    $sheet->setCellValue('F' . $startCell, '=SUM(F' . $startCellFilter . ':F' . $endForSum . ')');
    $sheet->setCellValue('G' . $startCell, '=SUM(G' . $startCellFilter . ':G' . $endForSum . ')');
    $sheet->setCellValue('H' . $startCell, '=SUM(H' . $startCellFilter . ':H' . $endForSum . ')');

    $filename = 'Laporan Retur Purchase Order ' . $this->dateTimeNow();
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
    $config['page_title'] = "Laporan Retur Purchase Order";
    $config['page_description'] = "Laporan Retur Purchase Order";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Retur Purchase Order"],
    ];

    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });

    $supplier_id = $request->supplier_id;
    $date = $request->date;
    $supplier = SupplierSparepart::find($supplier_id)->name ?? "All";

    $data = DB::table('retur_purchases')
      ->select(DB::raw('
        CONCAT(`invoice_retur_purchases`.`prefix`,"-",`invoice_retur_purchases`.`num_bill`) AS num_invoice,
        `spareparts`.`name` AS `sparepart_name`,
        `supplier_spareparts`.`name` AS `supplier_name`,
        `retur_purchases`.`qty`,
        `retur_purchases`.`price`,
        (`retur_purchases`.`price` * `retur_purchases`.`qty`) AS `total`,
        `invoice_retur_purchases`.`invoice_date` AS `invoice_date`
        '))
      ->leftJoin('invoice_retur_purchases', 'invoice_retur_purchases.id', '=', 'retur_purchases.invoice_retur_purchase_id')
      ->leftJoin('spareparts', 'spareparts.id', '=', 'retur_purchases.sparepart_id')
      ->leftJoin('supplier_spareparts', 'supplier_spareparts.id', '=', 'retur_purchases.supplier_sparepart_id')
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('invoice_retur_purchases.invoice_date', [$date_begin, $date_end]);
      })
      ->when($supplier_id, function ($query, $supplier_id) {
        return $query->where('invoice_retur_purchases.supplier_sparepart_id', $supplier_id);
      })
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('invoice_retur_purchases.invoice_date', [$date_begin, $date_end]);
      })
      ->orderBy('invoice_retur_purchases.invoice_date')
      ->get();
    return view('backend.report.reportpurchaseorders.print', compact('config', 'page_breadcrumbs', 'profile', 'data', 'supplier', 'date',));
  }
}
