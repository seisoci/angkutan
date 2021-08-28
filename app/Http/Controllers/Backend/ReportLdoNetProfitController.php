<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AnotherExpedition;
use App\Models\Cooperation;
use App\Models\JobOrder;
use App\Models\Setting;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class ReportLdoNetProfitController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:reportldonetprofit-list|reportldonetprofit-create|reportldonetprofit-edit|reportldonetprofit-delete', ['only' => ['index']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Rekap Laba LDO";
    $config['page_description'] = "Rekap Laba LDO";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Rekap Laba LDO"],
    ];
    $config['excel_url'] = 'reportldonetprofit/document?type=EXCEL';
    $config['pdf_url'] = 'reportldonetprofit/document?type=PDF';
    $config['print_url'] = 'reportldonetprofit/print';

    if ($request->ajax()) {
      $another_expedition_id = $request->another_expedition_id;
      $date = $request->date;
      $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])
        ->withSum('operationalexpense', 'amount')
        ->where('type', 'ldo')
        ->when($another_expedition_id, function ($query, $another_expedition_id) {
          return $query->where('another_expedition_id', $another_expedition_id);
        })
        ->when($date, function ($query, $date) {
          $date_format = explode(" / ", $date);
          $date_begin = $date_format[0];
          $date_end = $date_format[1];
          return $query->whereBetween('date_begin', [$date_begin, $date_end]);
        });
      return DataTables::of($data)
        ->make(true);
    }

    return view('backend.report.reportldonetprofit.index', compact('config', 'page_breadcrumbs'));
  }

  public function document(Request $request)
  {
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $type = $request->type;
    $another_expedition_id = $request->another_expedition_id;
    $date = $request->date;
    $anotherExpedition = AnotherExpedition::find($another_expedition_id)->name ?? 'All';
    $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])
      ->withSum('operationalexpense', 'amount')
      ->where('type', 'ldo')
      ->when($another_expedition_id, function ($query, $another_expedition_id) {
        return $query->where('another_expedition_id', $another_expedition_id);
      })
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('date_begin', [$date_begin, $date_end]);
      })
      ->get();

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
    $sheet->setCellValue('A1', 'Laporan Rekap Laba LDO');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->mergeCells('A3:C3');
    $sheet->setCellValue('A3', 'Priode: ' . (!empty($date) ? $date : 'All Date'));
    $sheet->mergeCells('A4:C4');
    $sheet->setCellValue('A4', 'Nama Supir: ' . $anotherExpedition);
    $sheet->mergeCells('A5:C5');
    $sheet->mergeCells('A6:C6');
    $sheet->mergeCells('M1:O1');
    $sheet->setCellValue('M1', $cooperationDefault['nickname']);
    $sheet->mergeCells('M2:O2');
    $sheet->setCellValue('M2', $cooperationDefault['address']);
    $sheet->mergeCells('M3:O3');
    $sheet->setCellValue('M3', 'Telp: ' . $cooperationDefault['phone']);
    $sheet->mergeCells('M4:O4');
    $sheet->setCellValue('M4', 'Fax: ' . $cooperationDefault['fax']);

    $sheet->getColumnDimension('A')->setWidth(3.55);
    $sheet->getColumnDimension('B')->setWidth(16);
    $sheet->getColumnDimension('C')->setWidth(14);
    $sheet->getColumnDimension('D')->setWidth(17);
    $sheet->getColumnDimension('E')->setWidth(20);
    $sheet->getColumnDimension('F')->setWidth(12);
    $sheet->getColumnDimension('G')->setWidth(20);
    $sheet->getColumnDimension('H')->setWidth(15);
    $sheet->getColumnDimension('I')->setWidth(15);
    $sheet->getColumnDimension('J')->setWidth(17);
    $sheet->getColumnDimension('K')->setWidth(17);
    $sheet->getColumnDimension('L')->setWidth(17);
    $sheet->getColumnDimension('M')->setWidth(17);
    $sheet->getColumnDimension('N')->setWidth(17);
    $sheet->getColumnDimension('O')->setWidth(15);
    $sheet->getRowDimension('2')->setRowHeight(30);
    $sheet->getStyle('F2')->getAlignment()->setVertical(Alignment::VERTICAL_DISTRIBUTED);

    $sheet->getStyle('A8')->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A8', 'No.');
    $sheet->setCellValue('B8', 'No. Job Order');
    $sheet->setCellValue('C8', 'Tgl Selesai');
    $sheet->setCellValue('D8', 'LDO');
    $sheet->setCellValue('E8', 'Supir LDO');
    $sheet->setCellValue('F8', 'Pelanggan');
    $sheet->setCellValue('G8', 'Rute Dari');
    $sheet->setCellValue('H8', 'Rute Ke');
    $sheet->setCellValue('I8', 'Muatan');
    $sheet->setCellValue('J8', 'Total Harga Dasar');
    $sheet->setCellValue('K8', 'Total Harga Dasar(Inc. Tax & FEE)');
    $sheet->setCellValue('L8', 'Total Harga Dasar LDO');
    $sheet->setCellValue('M8', 'Pendapatan Kotor');
    $sheet->setCellValue('N8', 'Pendapatan Bersih(Inc. Tax & FEE)');
    $sheet->setCellValue('O8', 'Created At');

    $startCell = 8;
    $startCellFilter = 8;
    $no = 1;
    $sheet->getStyle('A' . $startCell . ':O' . $startCell . '')
      ->applyFromArray($borderTopBottom);
    foreach ($data as $item):
      $startCell++;
      $sheet->getStyle('J' . $startCell . ':N' . $startCell . '')->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->getStyle('A' . $startCell . ':O' . $startCell . '')->applyFromArray($borderTopBottom);
      $sheet->getStyle('A' . $startCell . ':O' . $startCell)->getAlignment()->setVertical('top');
      $sheet->getStyle('G' . $startCell . '')->getAlignment()->setHorizontal('right');
      $sheet->setCellValue('A' . $startCell, $no++);
      $sheet->setCellValue('B' . $startCell, $item->num_prefix);
      $sheet->setCellValue('C' . $startCell, $item->date_begin);
      $sheet->setCellValue('D' . $startCell, $item->date_end);
      $sheet->setCellValue('E' . $startCell, $item->anotherexpedition->name);
      $sheet->setCellValue('F' . $startCell, $item->driver->name);
      $sheet->setCellValue('G' . $startCell, $item->costumer->name);
      $sheet->setCellValue('H' . $startCell, $item->routefrom->name);
      $sheet->setCellValue('I' . $startCell, $item->routeto->name);
      $sheet->setCellValue('J' . $startCell, $item->total_basic_price);
      $sheet->setCellValue('K' . $startCell, $item->total_basic_price_after_thanks);
      $sheet->setCellValue('L' . $startCell, $item->total_basic_price_ldo);
      $sheet->setCellValue('M' . $startCell,'=J' . $startCell.'-L'. $startCell);
      $sheet->setCellValue('N' . $startCell,'=K' . $startCell.'-L'. $startCell);
      $sheet->setCellValue('O' . $startCell, $item->created_at);
    endforeach;
    $sheet->setAutoFilter('B' . $startCellFilter . ':O' . $startCell);
    $sheet->getStyle('A' . $startCell . ':O' . $startCell . '')->applyFromArray($borderBottom);
    $endForSum = $startCell;
    $startCell++;
    $startCellFilter++;
    $sheet->getStyle('A' . $startCell . ':O' . $startCell . '')->applyFromArray($borderTop)->applyFromArray($borderBottom);
    $sheet->getStyle('H' . $startCell . ':O' . $startCell . '')->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('right');
    $sheet->getStyle('G' . $startCell . '')->getAlignment()->setHorizontal('right');
    $sheet->setCellValue('A' . $startCell, 'Total');
    $sheet->mergeCells('A' . $startCell . ':I' . $startCell . '');
    $sheet->getStyle('A' . $startCell . ':O' . $startCell)->getFont()->setBold(true);
    $sheet->setCellValue('J' . $startCell, '=SUM(J' . $startCellFilter . ':J' . $endForSum . ')');
    $sheet->setCellValue('K' . $startCell, '=SUM(K' . $startCellFilter . ':K' . $endForSum . ')');
    $sheet->setCellValue('L' . $startCell, '=SUM(L' . $startCellFilter . ':L' . $endForSum . ')');
    $sheet->setCellValue('M' . $startCell, '=SUM(M' . $startCellFilter . ':M' . $endForSum . ')');
    $sheet->setCellValue('N' . $startCell, '=SUM(N' . $startCellFilter . ':N' . $endForSum . ')');

    $filename = 'Laporan Rekap Laba LDO ' . $this->dateTimeNow();
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
    $config['page_title'] = "Laporan Rekap Laba LDO";
    $config['page_description'] = "Laporan Rekap Laba LDO";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Rekap Laba LDO"],
    ];

    $cooperationDefault = Cooperation::where('default', '1')->first();

    $another_expedition_id = $request->another_expedition_id;
    $date = $request->date;
    $anotherExpedition = AnotherExpedition::find($another_expedition_id)->name ?? 'All';
    $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])
      ->withSum('operationalexpense', 'amount')
      ->where('type', 'ldo')
      ->when($another_expedition_id, function ($query, $another_expedition_id) {
        return $query->where('another_expedition_id', $another_expedition_id);
      })
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('date_begin', [$date_begin, $date_end]);
      })
      ->get();

    return view('backend.report.reportldonetprofit.print', compact('config', 'page_breadcrumbs', 'cooperationDefault', 'data', 'date', 'anotherExpedition'));
  }

}
