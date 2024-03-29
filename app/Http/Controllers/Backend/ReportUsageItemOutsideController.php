<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cooperation;
use App\Models\Driver;
use App\Models\Transport;
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

class ReportUsageItemOutsideController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:reportusageitemoutside-list|reportusageitemoutside-create|reportusageitemoutside-edit|reportusageitemoutside-delete', ['only' => ['index']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Pembelian Barang Diluar";
    $config['page_description'] = "Laporan Pembelian Barang Diluar";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Pembelian Barang Diluar"],
    ];
    $config['excel_url'] = 'reportusageitemoutside/document?type=EXCEL';
    $config['pdf_url'] = 'reportusageitemoutside/document?type=PDF';
    $config['print_url'] = 'reportusageitemoutside/print';

    if ($request->ajax()) {
      $date = $request->date;
      $driver_id = $request->driver_id;
      $transport_id = $request->transport_id;

      $data = DB::table('usage_items')
        ->select(DB::raw('
        CONCAT(`invoice_usage_items`.`prefix`,"-",`invoice_usage_items`.`num_bill`) AS num_invoice,
        `usage_items`.`name` AS `sparepart_name`,
        `usage_items`.`description`,
        `usage_items`.`qty`,
        `usage_items`.`price`,
        (`usage_items`.`qty` * `usage_items`.`price`) AS `total_price`,
        `invoice_usage_items`.`invoice_date` AS `invoice_date`,
        `transports`.`num_pol` AS `num_pol`,
        `drivers`.`name` AS `driver_name`
        '))
        ->leftJoin('invoice_usage_items', 'invoice_usage_items.id', '=', 'usage_items.invoice_usage_item_id')
        ->leftJoin('spareparts', 'spareparts.id', '=', 'usage_items.sparepart_id')
        ->leftJoin('transports', 'transports.id', '=', 'invoice_usage_items.transport_id')
        ->leftJoin('drivers', 'drivers.id', '=', 'invoice_usage_items.driver_id')
        ->whereNull('sparepart_id')
        ->when($date, function ($query, $date) {
          $date_format = explode(" / ", $date);
          $date_begin = $date_format[0];
          $date_end = $date_format[1];
          return $query->whereBetween('invoice_usage_items.invoice_date', [$date_begin, $date_end]);
        })
        ->when($driver_id, function ($query, $driver_id) {
          return $query->where('invoice_usage_items.driver_id', $driver_id);
        })
        ->when($transport_id, function ($query, $transport_id) {
          return $query->where('invoice_usage_items.transport_id', $transport_id);
        })
        ->orderBy('invoice_usage_items.invoice_date', 'desc');

      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    return view('backend.report.reportusageitemoutside.index', compact('config', 'page_breadcrumbs'));
  }

  public function document(Request $request)
  {
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $type = $request->type;
    $driver_id = $request->driver_id;
    $transport_id = $request->transport_id;
    $date = $request->date;
    $transport = Transport::find($transport_id)->num_pol ?? "All";
    $driver = Driver::find($driver_id)->name ?? "All";

    $data = DB::table('usage_items')
      ->select(DB::raw('
        CONCAT(`invoice_usage_items`.`prefix`,"-",`invoice_usage_items`.`num_bill`) AS num_invoice,
        `usage_items`.`name` AS `sparepart_name`,
        `usage_items`.`description`,
        `usage_items`.`qty`,
        `usage_items`.`price`,
        (`usage_items`.`qty` * `usage_items`.`price`) AS `total_price`,
        `invoice_usage_items`.`invoice_date` AS `invoice_date`,
        `transports`.`num_pol` AS `num_pol`,
        `drivers`.`name` AS `driver_name`
        '))
      ->leftJoin('invoice_usage_items', 'invoice_usage_items.id', '=', 'usage_items.invoice_usage_item_id')
      ->leftJoin('spareparts', 'spareparts.id', '=', 'usage_items.sparepart_id')
      ->leftJoin('transports', 'transports.id', '=', 'invoice_usage_items.transport_id')
      ->leftJoin('drivers', 'drivers.id', '=', 'invoice_usage_items.driver_id')
      ->whereNull('sparepart_id')
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('invoice_usage_items.invoice_date', [$date_begin, $date_end]);
      })
      ->when($driver_id, function ($query, $driver_id) {
        return $query->where('invoice_usage_items.driver_id', $driver_id);
      })
      ->when($transport_id, function ($query, $transport_id) {
        return $query->where('invoice_usage_items.transport_id', $transport_id);
      })
      ->orderBy('invoice_usage_items.invoice_date', 'desc')
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

    $sheet->mergeCells('A1:C1');
    $sheet->setCellValue('A1', 'Laporan Pembelian Barang Diluar');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->mergeCells('A3:C3');
    $sheet->setCellValue('A3', 'Priode: ' . (!empty($date) ? $date : 'All Date'));
    $sheet->mergeCells('A4:C4');
    $sheet->setCellValue('A4', 'Nama Supir: ' . $driver);
    $sheet->mergeCells('A5:C5');
    $sheet->setCellValue('A5', 'No. Polisi: ' . $transport);
    $sheet->mergeCells('G1:I1');
    $sheet->setCellValue('G1', $cooperationDefault['nickname']);
    $sheet->mergeCells('G2:I2');
    $sheet->setCellValue('G2', $cooperationDefault['address']);
    $sheet->mergeCells('G3:I3');
    $sheet->setCellValue('G3', 'Telp: ' . $cooperationDefault['phone']);
    $sheet->mergeCells('G4:I4');
    $sheet->setCellValue('G4', 'Fax: ' . $cooperationDefault['fax']);

    $sheet->getColumnDimension('A')->setWidth(3.55);
    $sheet->getColumnDimension('B')->setWidth(16);
    $sheet->getColumnDimension('C')->setWidth(14);
    $sheet->getColumnDimension('D')->setWidth(17);
    $sheet->getColumnDimension('E')->setWidth(26);
    $sheet->getColumnDimension('F')->setWidth(12);
    $sheet->getColumnDimension('G')->setWidth(15);
    $sheet->getColumnDimension('H')->setWidth(15);
    $sheet->getColumnDimension('I')->setWidth(15);
    $sheet->getColumnDimension('J')->setWidth(15);
    $sheet->getStyle('F2')->getAlignment()->setVertical(Alignment::VERTICAL_DISTRIBUTED);

    $sheet->getStyle('A7')->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A7', 'No.');
    $sheet->setCellValue('B7', 'No. Invoice');
    $sheet->setCellValue('C7', 'Tgl Invoice');
    $sheet->setCellValue('D7', 'Nama Sparepart');
    $sheet->setCellValue('E7', 'Nama Supir');
    $sheet->setCellValue('F7', 'No. Polisi');
    $sheet->setCellValue('G7', 'Keterangan');
    $sheet->setCellValue('H7', 'Jumlah');
    $sheet->setCellValue('I7', 'Harga');
    $sheet->setCellValue('J7', 'Total');

    $startCell = 7;
    $startCellFilter = 7;
    $no = 1;
    $sheet->getStyle('A' . $startCell . ':J' . $startCell . '')
      ->applyFromArray($borderTopBottom);
    foreach ($data as $item):
      $startCell++;
      $sheet->getStyle('A' . $startCell . ':J' . $startCell . '')->applyFromArray($borderTopBottom);
      $sheet->getStyle('A' . $startCell . ':' . 'F' . $startCell)->getAlignment()->setVertical('top');
      $sheet->getStyle('G' . $startCell . '')->getAlignment()->setHorizontal('right');
      $sheet->getStyle('H' . $startCell . ':J' . $startCell . '')->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->setCellValue('A' . $startCell, $no++);
      $sheet->setCellValue('B' . $startCell, $item->num_invoice);
      $sheet->setCellValue('C' . $startCell, $item->invoice_date);
      $sheet->setCellValue('D' . $startCell, $item->sparepart_name);
      $sheet->setCellValue('E' . $startCell, $item->driver_name);
      $sheet->setCellValue('F' . $startCell, $item->num_pol);
      $sheet->setCellValue('G' . $startCell, $item->description);
      $sheet->setCellValue('H' . $startCell, $item->qty);
      $sheet->setCellValue('I' . $startCell, $item->price);
      $sheet->setCellValue('J' . $startCell, $item->total_price);
    endforeach;
    $sheet->setAutoFilter('B' . $startCellFilter . ':J' . $startCell);
    $sheet->getStyle('A' . $startCell . ':J' . $startCell . '')->applyFromArray($borderBottom);
    $endForSum = $startCell;
    $startCell++;
    $startCellFilter++;
    $sheet->getStyle('A' . $startCell . ':J' . $startCell . '')->applyFromArray($borderTop)->applyFromArray($borderBottom);
    $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('right');
    $sheet->getStyle('G' . $startCell . '')->getAlignment()->setHorizontal('right');
    $sheet->setCellValue('A' . $startCell, 'Total');
    $sheet->mergeCells('A' . $startCell . ':F' . $startCell . '');
    $sheet->getStyle('H' . $startCell . ':J' . $startCell . '')->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('A' . $startCell . ':J' . $startCell)->getFont()->setBold(true);
    $sheet->setCellValue('J' . $startCell, '=SUM(J' . $startCellFilter . ':J' . $endForSum . ')');
    $sheet->setCellValue('I' . $startCell, '=SUM(I' . $startCellFilter . ':I' . $endForSum . ')');
    $sheet->setCellValue('J' . $startCell, '=SUM(J' . $startCellFilter . ':J' . $endForSum . ')');

    $filename = 'Laporan Pembelian Barang Diluar ' . $this->dateTimeNow();
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
    $config['page_title'] = "Laporan Pembelian Barang Diluar";
    $config['page_description'] = "Laporan Pembelian Barang Diluar";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Pembelian Barang Diluar"],
    ];

    $cooperationDefault = Cooperation::where('default', '1')->first();

    $driver_id = $request->driver_id;
    $transport_id = $request->transport_id;
    $date = $request->date;
    $transport = Transport::find($transport_id)->num_pol ?? "All";
    $driver = Driver::find($driver_id)->name ?? "All";

    $data = DB::table('usage_items')
      ->select(DB::raw('
        CONCAT(`invoice_usage_items`.`prefix`,"-",`invoice_usage_items`.`num_bill`) AS num_invoice,
        `usage_items`.`name` AS `sparepart_name`,
        `usage_items`.`description`,
        `usage_items`.`qty`,
        `usage_items`.`price`,
        (`usage_items`.`qty` * `usage_items`.`price`) AS `total_price`,
        `invoice_usage_items`.`invoice_date` AS `invoice_date`,
        `transports`.`num_pol` AS `num_pol`,
        `drivers`.`name` AS `driver_name`
        '))
      ->leftJoin('invoice_usage_items', 'invoice_usage_items.id', '=', 'usage_items.invoice_usage_item_id')
      ->leftJoin('spareparts', 'spareparts.id', '=', 'usage_items.sparepart_id')
      ->leftJoin('transports', 'transports.id', '=', 'invoice_usage_items.transport_id')
      ->leftJoin('drivers', 'drivers.id', '=', 'invoice_usage_items.driver_id')
      ->whereNull('sparepart_id')
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('invoice_usage_items.invoice_date', [$date_begin, $date_end]);
      })
      ->when($driver_id, function ($query, $driver_id) {
        return $query->where('invoice_usage_items.driver_id', $driver_id);
      })
      ->when($transport_id, function ($query, $transport_id) {
        return $query->where('invoice_usage_items.transport_id', $transport_id);
      })
      ->orderBy('invoice_usage_items.invoice_date', 'desc')
      ->get();

    return view('backend.report.reportusageitemoutside.print', compact('config', 'page_breadcrumbs', 'cooperationDefault', 'data', 'date', 'transport', 'driver'));
  }
}
