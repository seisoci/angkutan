<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cooperation;
use App\Models\Driver;
use App\Models\Setting;
use App\Models\Sparepart;
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

class ReportUsageItemsController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:reportusageitems-list|reportusageitems-create|reportusageitems-edit|reportusageitems-delete', ['only' => ['index']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Pemakaian Barang";
    $config['page_description'] = "Laporan Pemakaian Barang";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Pemakaian Barang"],
    ];
    $config['excel_url'] = 'reportusageitems/document?type=EXCEL';
    $config['pdf_url'] = 'reportusageitems/document?type=PDF';
    $config['print_url'] = 'reportusageitems/print';

    if ($request->ajax()) {
      $date = $request->date;
      $driver_id = $request->driver_id;
      $transport_id = $request->transport_id;
      $sparepart_id = $request->sparepart_id;

      $data = DB::table('usage_items')
        ->select(DB::raw('
        CONCAT(`invoice_usage_items`.`prefix`,"-",`invoice_usage_items`.`num_bill`) AS num_invoice,
        `spareparts`.`name` AS `sparepart_name`,
        `usage_items`.`qty`,
        `invoice_usage_items`.`invoice_date` AS `invoice_date`,
        `transports`.`num_pol` AS `num_pol`,
        `drivers`.`name` AS `driver_name`,
        `usage_items`.`price` AS `price`,
        (`usage_items`.`price` * `usage_items`.`qty`) AS total_price
        '))
        ->leftJoin('invoice_usage_items', 'invoice_usage_items.id', '=', 'usage_items.invoice_usage_item_id')
        ->leftJoin('spareparts', 'spareparts.id', '=', 'usage_items.sparepart_id')
        ->leftJoin('transports', 'transports.id', '=', 'invoice_usage_items.transport_id')
        ->leftJoin('drivers', 'drivers.id', '=', 'invoice_usage_items.driver_id')
        ->where('invoice_usage_items.type', 'self')
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
        ->when($sparepart_id, function ($query, $sparepart_id) {
          return $query->where('usage_items.sparepart_id', $sparepart_id);
        })
        ->orderBy('invoice_usage_items.invoice_date', 'asc');

      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    return view('backend.report.reportusageitems.index', compact('config', 'page_breadcrumbs'));
  }

  public function document(Request $request)
  {
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $type = $request->type;
    $driver_id = $request->driver_id;
    $transport_id = $request->transport_id;
    $sparepart_id = $request->sparepart_id;
    $date = $request->date;
    $transport = Transport::find($transport_id)->num_pol ?? "All";
    $driver = Driver::find($driver_id)->name ?? "All";
    $sparepart = Sparepart::find($sparepart_id)->name ?? "All";
    $data = DB::table('usage_items')
      ->select(DB::raw('
        CONCAT(`invoice_usage_items`.`prefix`,"-",`invoice_usage_items`.`num_bill`) AS num_invoice,
        `spareparts`.`name` AS `sparepart_name`,
        `usage_items`.`qty`,
        `invoice_usage_items`.`invoice_date` AS `invoice_date`,
        `transports`.`num_pol` AS `num_pol`,
        `drivers`.`name` AS `driver_name`,
        `usage_items`.`price` AS `price`,
        SUM(`usage_items`.`price` * `usage_items`.`qty`) AS total_price
        '))
      ->leftJoin('invoice_usage_items', 'invoice_usage_items.id', '=', 'usage_items.invoice_usage_item_id')
      ->leftJoin('spareparts', 'spareparts.id', '=', 'usage_items.sparepart_id')
      ->leftJoin('transports', 'transports.id', '=', 'invoice_usage_items.transport_id')
      ->leftJoin('drivers', 'drivers.id', '=', 'invoice_usage_items.driver_id')
      ->where('invoice_usage_items.type', 'self')
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
      ->when($sparepart_id, function ($query, $sparepart_id) {
        return $query->where('usage_items.sparepart_id', $sparepart_id);
      })
      ->orderBy('invoice_usage_items.invoice_date', 'asc')
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
    $sheet->setCellValue('A1', 'Laporan Pemakaian Barang');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->mergeCells('A3:C3');
    $sheet->setCellValue('A3', 'Priode: ' . (!empty($date) ? $date : 'All Date'));
    $sheet->mergeCells('A4:C4');
    $sheet->setCellValue('A4', 'Nama Supir: ' . $driver);
    $sheet->mergeCells('A5:C5');
    $sheet->setCellValue('A5', 'No. Polisi: ' . $transport);
    $sheet->mergeCells('A6:C6');
    $sheet->setCellValue('A6', 'Nama Sparepart: ' . $sparepart);
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
//    $sheet->getRowDimension('2')->setRowHeight(30);
    $sheet->getStyle('F2')->getAlignment()->setVertical(Alignment::VERTICAL_DISTRIBUTED);

    $sheet->getStyle('A8')->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A8', 'No.');
    $sheet->setCellValue('B8', 'No. Pemakaian');
    $sheet->setCellValue('C8', 'Tgl Pemakaian');
    $sheet->setCellValue('D8', 'Nama Sparepart');
    $sheet->setCellValue('E8', 'Nama Supir');
    $sheet->setCellValue('F8', 'No. Polisi');
    $sheet->setCellValue('G8', 'Jumlah');
    $sheet->setCellValue('H8', 'Harga');
    $sheet->setCellValue('I8', 'Total');

    $startCell = 8;
    $startCellFilter = 8;
    $no = 1;
    $sheet->getStyle('A' . $startCell . ':I' . $startCell . '')
      ->applyFromArray($borderTopBottom);
    foreach ($data as $item):
      $startCell++;
      $sheet->getStyle('H' . $startCell . ':I' . $startCell . '')->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->getStyle('A' . $startCell . ':G' . $startCell . '')->applyFromArray($borderTopBottom);
      $sheet->getStyle('A' . $startCell . ':' . 'F' . $startCell)->getAlignment()->setVertical('top');
      $sheet->getStyle('G' . $startCell . '')->getAlignment()->setHorizontal('right');
      $sheet->setCellValue('A' . $startCell, $no++);
      $sheet->setCellValue('B' . $startCell, $item->num_invoice);
      $sheet->setCellValue('C' . $startCell, $item->invoice_date);
      $sheet->setCellValue('D' . $startCell, $item->sparepart_name);
      $sheet->setCellValue('E' . $startCell, $item->driver_name);
      $sheet->setCellValue('F' . $startCell, $item->num_pol);
      $sheet->setCellValue('G' . $startCell, $item->qty);
      $sheet->setCellValue('H' . $startCell, $item->price);
      $sheet->setCellValue('I' . $startCell, $item->total_price);
    endforeach;
    $sheet->setAutoFilter('B' . $startCellFilter . ':I' . $startCell);
    $sheet->getStyle('A' . $startCell . ':I' . $startCell . '')->applyFromArray($borderBottom);
    $endForSum = $startCell;
    $startCell++;
    $startCellFilter++;
    $sheet->getStyle('A' . $startCell . ':I' . $startCell . '')->applyFromArray($borderTop)->applyFromArray($borderBottom);
    $sheet->getStyle('H' . $startCell . ':I' . $startCell . '')->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('right');
    $sheet->getStyle('G' . $startCell . '')->getAlignment()->setHorizontal('right');
    $sheet->setCellValue('A' . $startCell, 'Total');
    $sheet->mergeCells('A' . $startCell . ':F' . $startCell . '');
    $sheet->getStyle('A' . $startCell . ':G' . $startCell)->getFont()->setBold(true);
    $sheet->setCellValue('G' . $startCell, '=SUM(G' . $startCellFilter . ':G' . $endForSum . ')');
    $sheet->setCellValue('H' . $startCell, '=SUM(H' . $startCellFilter . ':H' . $endForSum . ')');
    $sheet->setCellValue('I' . $startCell, '=SUM(I' . $startCellFilter . ':I' . $endForSum . ')');

    $filename = 'Laporan Pemakaian Barang ' . $this->dateTimeNow();
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
    $config['page_title'] = "Laporan Pemakaian Barang";
    $config['page_description'] = "Laporan Pemakaian Barang";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Pemakaian Barang"],
    ];

    $cooperationDefault = Cooperation::where('default', '1')->first();

    $driver_id = $request->driver_id;
    $transport_id = $request->transport_id;
    $sparepart_id = $request->sparepart_id;
    $date = $request->date;
    $transport = Transport::find($transport_id)->num_pol ?? "All";
    $driver = Driver::find($driver_id)->name ?? "All";
    $sparepart = Sparepart::find($sparepart_id)->name ?? "All";
    $data = DB::table('usage_items')
      ->select(DB::raw('
        CONCAT(`invoice_usage_items`.`prefix`,"-",`invoice_usage_items`.`num_bill`) AS num_invoice,
        `spareparts`.`name` AS `sparepart_name`,
        `usage_items`.`qty`,
        `invoice_usage_items`.`invoice_date` AS `invoice_date`,
        `transports`.`num_pol` AS `num_pol`,
        `drivers`.`name` AS `driver_name`,
        `usage_items`.`price` AS `price`,
        SUM(`usage_items`.`price` * `usage_items`.`qty`) AS total_price
        '))
      ->leftJoin('invoice_usage_items', 'invoice_usage_items.id', '=', 'usage_items.invoice_usage_item_id')
      ->leftJoin('spareparts', 'spareparts.id', '=', 'usage_items.sparepart_id')
      ->leftJoin('transports', 'transports.id', '=', 'invoice_usage_items.transport_id')
      ->leftJoin('drivers', 'drivers.id', '=', 'invoice_usage_items.driver_id')
      ->where('invoice_usage_items.type', 'self')
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
      ->when($sparepart_id, function ($query, $sparepart_id) {
        return $query->where('usage_items.sparepart_id', $sparepart_id);
      })
      ->orderBy('invoice_usage_items.invoice_date', 'asc')
      ->get();

    return view('backend.report.reportusageitems.print', compact('config', 'page_breadcrumbs', 'cooperationDefault', 'data', 'date', 'transport', 'driver', 'sparepart'));
  }
}
