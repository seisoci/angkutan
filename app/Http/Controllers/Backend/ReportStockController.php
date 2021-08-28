<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cooperation;
use App\Models\Setting;
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

class ReportStockController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:reportstocks-list|reportstocks-create|reportstocks-edit|reportstocks-delete', ['only' => ['index']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Stok Barang";
    $config['page_description'] = "Laporan Stok Barang";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Stok Barang"],
    ];
    $config['excel_url'] = 'reportstocks/document?type=EXCEL';
    $config['pdf_url'] = 'reportstocks/document?type=PDF';
    $config['print_url'] = 'reportstocks/print';

    if ($request->ajax()) {
      $data = DB::table('stocks')
        ->select(DB::raw('
          `spareparts`.`name` AS `name`,
          SUM(`qty`) AS `qty`
        '))
        ->leftJoin('spareparts', 'spareparts.id', '=', 'stocks.sparepart_id')
        ->groupBy('stocks.sparepart_id')
        ->orderBy('spareparts.name');

      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    return view('backend.report.reportstocks.index', compact('config', 'page_breadcrumbs'));
  }

  public function document(Request $request)
  {
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $type = $request->type;
    $data = DB::table('stocks')
      ->select(DB::raw('
          `spareparts`.`name` AS `name`,
          SUM(`qty`) AS `qty`
        '))
      ->leftJoin('spareparts', 'spareparts.id', '=', 'stocks.sparepart_id')
      ->groupBy('stocks.sparepart_id')
      ->orderBy('spareparts.name')
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

    $sheet->mergeCells('A1:B1');
    $sheet->setCellValue('A1', 'Laporan Stok Barang');
    $sheet->mergeCells('A2:B2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->setCellValue('C1', $cooperationDefault['nickname']);
    $sheet->setCellValue('C2', $cooperationDefault['address']);
    $sheet->setCellValue('C3', 'Telp: ' . $cooperationDefault['phone']);
    $sheet->setCellValue('C4', 'Fax: ' . $cooperationDefault['fax']);

    $sheet->getColumnDimension('A')->setWidth(3);
    $sheet->getColumnDimension('B')->setWidth(60);
    $sheet->getColumnDimension('C')->setWidth(60);
//    $sheet->getRowDimension('2')->setRowHeight(30);
    $sheet->getStyle('F2')->getAlignment()->setVertical(Alignment::VERTICAL_DISTRIBUTED);

    $sheet->getStyle('A6')->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A6', 'No.');
    $sheet->setCellValue('B6', 'Nama Barang');
    $sheet->setCellValue('C6', 'Jumlah');

    $startCell = 6;
    $startCellFilter = 6;
    $no = 1;
    $sheet->getStyle('A' . $startCell . ':C' . $startCell . '')
      ->applyFromArray($borderTopBottom);
    foreach ($data as $item):
      $startCell++;
      $sheet->getStyle('A' . $startCell . ':C' . $startCell . '')->applyFromArray($borderTopBottom);
      $sheet->getStyle('A' . $startCell . ':' . 'C' . $startCell)->getAlignment()->setVertical('top');
      $sheet->getStyle('C' . $startCell . '')->getAlignment()->setHorizontal('left');
      $sheet->setCellValue('A' . $startCell, $no++);
      $sheet->setCellValue('B' . $startCell, $item->name);
      $sheet->setCellValue('C' . $startCell, $item->qty);
    endforeach;
    $sheet->setAutoFilter('B' . $startCellFilter . ':C' . $startCell);
    $sheet->getStyle('A' . $startCell . ':C' . $startCell . '')->applyFromArray($borderBottom);

    $filename = 'Laporan Stok Barang ' . $this->dateTimeNow();
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
    $config['page_title'] = "Laporan Stok Barang";
    $config['page_description'] = "Laporan Stok Barang";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Stok Barang"],
    ];

    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = DB::table('stocks')
      ->select(DB::raw('
          `spareparts`.`name` AS `name`,
          SUM(`qty`) AS `qty`
        '))
      ->leftJoin('spareparts', 'spareparts.id', '=', 'stocks.sparepart_id')
      ->groupBy('stocks.sparepart_id')
      ->orderBy('spareparts.name')
      ->get();

    return view('backend.report.reportstocks.print', compact('config', 'page_breadcrumbs', 'cooperationDefault', 'data'));
  }
}
