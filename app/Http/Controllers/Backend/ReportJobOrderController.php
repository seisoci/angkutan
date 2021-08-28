<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cooperation;
use App\Models\Costumer;
use App\Models\JobOrder;
use App\Models\Setting;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class ReportJobOrderController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:reportjoborders-list|reportjoborders-create|reportjoborders-edit|reportjoborders-delete', ['only' => ['index']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Tagihan Job Order";
    $config['page_description'] = "Laporan Tagihan Job Order";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Tagihan Job Order"],
    ];
    $config['excel_url'] = 'reportjoborders/document?type=EXCEL';
    $config['pdf_url'] = 'reportjoborders/document?type=PDF';
    $config['print_url'] = 'reportjoborders/print';

    if ($request->ajax()) {
      $date = $request->date;
      $costumer_id = $request->costumer_id;
      $data = JobOrder::with('costumer:id,name', 'routefrom:id,name', 'routeto:id,name',
        'transport:id,num_pol', 'cargo:id,name')
        ->when($date, function ($query, $date) {
          $date_format = explode(" / ", $date);
          $date_begin = $date_format[0];
          $date_end = $date_format[1];
          return $query->whereBetween('date_begin', [$date_begin, $date_end]);
        })
        ->when($costumer_id, function ($query, $costumer_id) {
          return $query->where('costumer_id', $costumer_id);
        })
        ->where('status_payment', '0')
        ->where('status_cargo', 'selesai')
        ->orderBy('date_begin', 'asc');

      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    return view('backend.report.reportjoborders.index', compact('config', 'page_breadcrumbs'));
  }

  public function document(Request $request)
  {
    $type = $request->type;
    $date = $request->date;
    $costumer_id = $request->costumer_id;
    $costumer = Costumer::find($costumer_id);
    $data = JobOrder::with('costumer:id,name', 'routefrom:id,name', 'routeto:id,name',
      'transport:id,num_pol', 'cargo:id,name')
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('date_begin', [$date_begin, $date_end]);
      })
      ->when($costumer_id, function ($query, $costumer_id) {
        return $query->where('costumer_id', $costumer_id);
      })
      ->where('status_payment', '0')
      ->where('status_cargo', 'selesai')
      ->orderBy('date_begin', 'asc')
      ->get();

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
    $sheet->setCellValue('A1', 'Laporan Tagihan Job Order');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->mergeCells('A3:C3');
    $sheet->setCellValue('A3', 'Priode: ' . (!empty($date) ? $date : 'All Date'));
    $sheet->mergeCells('A4:C4');
    $sheet->setCellValue('A4', 'Costumer: ' . (!empty($costumer) ? $costumer->name : 'All'));
    $sheet->mergeCells('L1:O1');
    $sheet->setCellValue('L1', $cooperationDefault['nickname']);
    $sheet->mergeCells('L2:O2');
    $sheet->setCellValue('L2', $cooperationDefault['address']);
    $sheet->mergeCells('L3:O3');
    $sheet->setCellValue('L3', 'Telp: ' . $cooperationDefault['phone']);
    $sheet->mergeCells('L4:O4');
    $sheet->setCellValue('L4', 'Fax: ' . $cooperationDefault['fax']);

    $sheet->getColumnDimension('A')->setWidth(3.55);
    $sheet->getColumnDimension('B')->setWidth(10);
    $sheet->getColumnDimension('C')->setWidth(10);
    $sheet->getColumnDimension('D')->setWidth(15);
    $sheet->getColumnDimension('E')->setWidth(30);
    $sheet->getColumnDimension('F')->setWidth(20);
    $sheet->getColumnDimension('G')->setWidth(20);
    $sheet->getColumnDimension('H')->setWidth(12);
    $sheet->getColumnDimension('I')->setWidth(14);
    $sheet->getColumnDimension('J')->setWidth(8);
    $sheet->getColumnDimension('K')->setWidth(14);
    $sheet->getColumnDimension('L')->setWidth(8);
    $sheet->getColumnDimension('M')->setWidth(14);
    $sheet->getColumnDimension('N')->setWidth(14);
    $sheet->getColumnDimension('O')->setWidth(14);

    $sheet->getStyle('A6')->getAlignment()->setHorizontal('center');
    $sheet->getStyle('I6:O6')->getAlignment()->setHorizontal('right');
    $sheet->getStyle('J6')->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A6', 'No.');
    $sheet->setCellValue('B6', 'Tanggal.');
    $sheet->setCellValue('C6', 'No. Polisi');
    $sheet->setCellValue('D6', 'No. Prefix');
    $sheet->setCellValue('E6', 'Nama Pelanggan');
    $sheet->setCellValue('F6', 'Rute Dari');
    $sheet->setCellValue('G6', 'Rute Tujuan');
    $sheet->setCellValue('H6', 'Jenis Barang');
    $sheet->setCellValue('I6', 'Tarif (Rp.)');
    $sheet->setCellValue('J6', 'Qty');
    $sheet->setCellValue('K6', 'Total');
    $sheet->setCellValue('L6', 'Tax (%)');
    $sheet->setCellValue('M6', 'Total (Inc. Tax)');
    $sheet->setCellValue('N6', 'Fee Thanks');
    $sheet->setCellValue('O6', 'Total (Inc. Tax, Thanks))');

    $startCell = 6;
    $startCellFilter = 6;
    $no = 1;
    $sheet->getStyle('A' . $startCell . ':O' . $startCell . '')
      ->applyFromArray($borderTopBottom)
      ->applyFromArray($borderLeftRight);
    foreach ($data as $item):
      $startCell++;
      $sheet->getStyle('A' . $startCell . ':O' . $startCell . '')->applyFromArray($borderLeftRight);
      $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('center');
      $sheet->getStyle('I' . $startCell . ':O' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->setCellValue('A' . $startCell, $no++);
      $sheet->setCellValue('B' . $startCell, $item->date_begin);
      $sheet->setCellValue('C' . $startCell, $item->transport->num_pol);
      $sheet->setCellValue('D' . $startCell, $item->num_prefix);
      $sheet->setCellValue('E' . $startCell, $item->costumer->name);
      $sheet->setCellValue('F' . $startCell, $item->routefrom->name);
      $sheet->setCellValue('G' . $startCell, $item->routeto->name);
      $sheet->setCellValue('H' . $startCell, $item->cargo->name);
      $sheet->setCellValue('I' . $startCell, $item->basic_price);
      $sheet->setCellValue('J' . $startCell, $item->payload);
      $sheet->setCellValue('K' . $startCell, $item->total_basic_price);
      $sheet->setCellValue('L' . $startCell, $item->tax_percent);
      $sheet->setCellValue('M' . $startCell, $item->total_basic_price_after_tax);
      $sheet->setCellValue('N' . $startCell, $item->fee_thanks);
      $sheet->setCellValue('O' . $startCell, $item->total_basic_price_after_thanks);
    endforeach;
    $sheet->setAutoFilter('B' . $startCellFilter . ':O' . $startCell);
    $sheet->getStyle('A' . $startCell . ':O' . $startCell . '')->applyFromArray($borderBottom);

    $endForSum = $startCell;
    $startCell++;
    $startCellFilter++;
    $sheet->getStyle('A' . $startCell . ':O' . $startCell . '')->applyFromArray($borderAll);
    $sheet->getStyle('E' . $startCell . ':O' . $startCell . '')->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('right');
    $sheet->setCellValue('A' . $startCell, 'Total Rp.');
    $sheet->mergeCells('A' . $startCell . ':H' . $startCell . '');
    $sheet->getStyle('A' . $startCell . ':O' . $startCell)->getFont()->setBold(true);
    $sheet->setCellValue('I' . $startCell, '=SUM(I' . $startCellFilter . ':I' . $endForSum . ')');
    $sheet->setCellValue('J' . $startCell, '=SUM(J' . $startCellFilter . ':J' . $endForSum . ')');
    $sheet->setCellValue('K' . $startCell, '=SUM(K' . $startCellFilter . ':K' . $endForSum . ')');
    $sheet->setCellValue('L' . $startCell, '=SUM(L' . $startCellFilter . ':L' . $endForSum . ')');
    $sheet->setCellValue('M' . $startCell, '=SUM(M' . $startCellFilter . ':M' . $endForSum . ')');
    $sheet->setCellValue('N' . $startCell, '=SUM(N' . $startCellFilter . ':N' . $endForSum . ')');
    $sheet->setCellValue('O' . $startCell, '=SUM(O' . $startCellFilter . ':O' . $endForSum . ')');

    $filename = 'Laporan Tagihan Job Order ' . $this->dateTimeNow();
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
    $costumer_id = $request->costumer_id;
    $costumer = Costumer::find($costumer_id);
    $data = JobOrder::with('costumer:id,name', 'routefrom:id,name', 'routeto:id,name',
      'transport:id,num_pol', 'cargo:id,name')
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('date_begin', [$date_begin, $date_end]);
      })
      ->when($costumer_id, function ($query, $costumer_id) {
        return $query->where('costumer_id', $costumer_id);
      })
      ->where('status_payment', '0')
      ->where('status_cargo', 'selesai')
      ->orderBy('date_begin', 'asc')
      ->get();
    $config['page_title'] = "Laporan Tagihan Job Order";
    $config['page_description'] = "Laporan Tagihan Job Order";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Tagihan Job Order"],
    ];

    $cooperationDefault = Cooperation::where('default', '1')->first();

    return view('backend.report.reportjoborders.print', compact('config', 'page_breadcrumbs', 'cooperationDefault', 'data', 'date', 'costumer'));
  }

}
