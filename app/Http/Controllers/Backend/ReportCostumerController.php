<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cooperation;
use App\Models\Costumer;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class ReportCostumerController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:reportcostumers-list|reportcostumers-create|reportcostumers-edit|reportcostumers-delete', ['only' => ['index']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Data Pelanggan";
    $config['page_description'] = "Data Pelanggan";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Data Pelanggan"],
    ];
    $config['excel_url'] = 'reportcostumers/document?type=EXCEL';
    $config['pdf_url'] = 'reportcostumers/document?type=PDF';
    $config['print_url'] = 'reportcostumers/print';

    if ($request->ajax()) {
      $data = Costumer::orderBy('name', 'asc');
      return DataTables::of($data)
        ->make(true);
    }
    return view('backend.report.reportcostumers.index', compact('config', 'page_breadcrumbs'));
  }

  public function document(Request $request)
  {
    $type = $request->type;
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = Costumer::orderBy('name', 'asc')->get();
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

    $sheet->getColumnDimension('A')->setWidth(3.55);
    $sheet->getColumnDimension('B')->setWidth(37);
    $sheet->getColumnDimension('C')->setWidth(21);
    $sheet->getColumnDimension('D')->setWidth(15);
    $sheet->getColumnDimension('E')->setWidth(30);
    $sheet->getColumnDimension('F')->setWidth(14);
    $sheet->getColumnDimension('G')->setWidth(9);

    $sheet->getStyle('A6')->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A6', 'No.');
    $sheet->setCellValue('B6', 'Nama Pelanggan');
    $sheet->setCellValue('C6', 'Alamat');
    $sheet->setCellValue('D6', 'No. Telp');
    $sheet->setCellValue('E6', 'Nama Darurat');
    $sheet->setCellValue('F6', 'No. Telp Darurat');
    $sheet->setCellValue('G6', 'Kerjasama');

    $startCell = 6;
    $startCellFilter = 6;
    $no = 1;
    $sheet->getStyle('A' . $startCell . ':G' . $startCell . '')
      ->applyFromArray($borderTopBottom)
      ->applyFromArray($borderLeftRight);
    foreach ($data as $item):
      $startCell++;
      $sheet->getStyle('A' . $startCell . ':G' . $startCell . '')->applyFromArray($borderLeftRight);
      $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('center');
      $sheet->setCellValue('A' . $startCell, $no++);
      $sheet->setCellValue('B' . $startCell, $item->name);
      $sheet->setCellValue('C' . $startCell, $item->address);
      $sheet->setCellValue('D' . $startCell, $item->phone);
      $sheet->setCellValue('E' . $startCell, $item->emergency_name);
      $sheet->setCellValue('F' . $startCell, $item->emergency_phone);
      $sheet->setCellValue('G' . $startCell, $item->cooperation);
    endforeach;
    $sheet->setAutoFilter('B' . $startCellFilter . ':G' . $startCell);
    $sheet->getStyle('A' . $startCell . ':G' . $startCell . '')->applyFromArray($borderBottom);

    $sheet->mergeCells('A1:C1');
    $sheet->setCellValue('A1', 'Laporan Data Pelanggan');
    $sheet->mergeCells('A2:B2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->mergeCells('E1:G1');
    $sheet->setCellValue('E1', $cooperationDefault['nickname']);
    $sheet->mergeCells('E2:G2');
    $sheet->setCellValue('E2', $cooperationDefault['address']);
    $sheet->mergeCells('E3:G3');
    $sheet->setCellValue('E3', 'Telp: ' . $cooperationDefault['phone']);
    $sheet->mergeCells('E4:G4');
    $sheet->setCellValue('E4', 'Fax: ' . $cooperationDefault['fax']);

    $filename = 'Laporan Pelanggan';
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

  public function print()
  {
    $config['page_title'] = "Laporan Data Pelanggan";
    $config['page_description'] = "Data Pelanggan";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Data Pelanggan"],
    ];

    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = Costumer::orderBy('name', 'asc')->get();
    return view('backend.report.reportcostumers.print', compact('config', 'page_breadcrumbs', 'cooperationDefault', 'data'));
  }

}
