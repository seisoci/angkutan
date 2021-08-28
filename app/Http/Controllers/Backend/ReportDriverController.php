<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cooperation;
use App\Models\Driver;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class ReportDriverController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:reportdrivers-list|reportdrivers-create|reportdrivers-edit|reportdrivers-delete', ['only' => ['index']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "laporan Data Supir";
    $config['page_description'] = "Laporan Data Supir";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Data Supir"],
    ];
    $config['excel_url'] = 'reportdrivers/document?type=EXCEL';
    $config['pdf_url'] = 'reportdrivers/document?type=PDF';
    $config['print_url'] = 'reportdrivers/print';

    if ($request->ajax()) {
      $data = Driver::where('another_expedition_id', NULL)
        ->orderBy('name', 'asc');
      return DataTables::of($data)
        ->addIndexColumn()
        ->editColumn('image', function (Driver $data) {
          return !empty($data->photo) ? asset("/images/thumbnail/$data->photo") : asset('media/users/blank.png');
        })->make(true);
    }
    return view('backend.report.reportdrivers.index', compact('config', 'page_breadcrumbs'));
  }

  public function document(Request $request)
  {
    $type = $request->type;
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = Driver::where('another_expedition_id', NULL)
      ->orderBy('name', 'asc')
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
    $sheet->setCellValue('A1', 'Laporan Data Supir');
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

    $sheet->getColumnDimension('A')->setWidth(3.55);
    $sheet->getColumnDimension('B')->setWidth(20);
    $sheet->getColumnDimension('C')->setWidth(21);
    $sheet->getColumnDimension('D')->setWidth(15);
    $sheet->getColumnDimension('E')->setWidth(14);
    $sheet->getColumnDimension('F')->setWidth(14);
    $sheet->getColumnDimension('G')->setWidth(22);

    $sheet->getStyle('A6')->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A6', 'No.');
    $sheet->setCellValue('B6', 'Nama Supir');
    $sheet->setCellValue('C6', 'Alamat');
    $sheet->setCellValue('D6', 'No. Telp');
    $sheet->setCellValue('E6', 'Expired SIM');
    $sheet->setCellValue('F6', 'Status');
    $sheet->setCellValue('G6', 'Foto');

    $startCell = 6;
    $startCellFilter = 6;
    $no = 1;
    $sheet->getStyle('A' . $startCell . ':G' . $startCell . '')
      ->applyFromArray($borderTopBottom);
    foreach ($data as $item):
      $startCell++;
      $drawing = new MemoryDrawing();
      $image = !empty($item->photo) ? asset("/images/thumbnail/$item->photo") : asset('media/users/blank.png');
      $gdImage = strpos($image, '.jpg') ? imagecreatefromjpeg($image) : imagecreatefrompng($image);
      $sheet->getRowDimension($startCell)->setRowHeight(100);
      $drawing->setName($item->name);
      $drawing->setImageResource($gdImage);
      $drawing->setRenderingFunction(MemoryDrawing::RENDERING_PNG);
      $drawing->setMimeType(MemoryDrawing::MIMETYPE_DEFAULT);
      $drawing->setWidth(10);
      $drawing->setHeight(100);
      $drawing->setOffsetX(30);
      $drawing->setOffsetY(14);
      $drawing->setCoordinates('G' . $startCell);
      $drawing->setWorksheet($sheet);


      $sheet->getStyle('A' . $startCell . ':G' . $startCell . '')->applyFromArray($borderTopBottom);
      $sheet->getStyle('A' . $startCell . ':' . 'F' . $startCell)->getAlignment()->setVertical('top');
      $sheet->setCellValue('A' . $startCell, $no++);
      $sheet->setCellValue('B' . $startCell, $item->name);
      $sheet->setCellValue('C' . $startCell, $item->address);
      $sheet->setCellValue('D' . $startCell, $item->phone);
      $sheet->setCellValue('E' . $startCell, $item->expired_sim);
      $sheet->setCellValue('F' . $startCell, $item->status);
    endforeach;
    $sheet->setAutoFilter('B' . $startCellFilter . ':G' . $startCell);
    $sheet->getStyle('A' . $startCell . ':G' . $startCell . '')->applyFromArray($borderBottom);

    $filename = 'Laporan Data Supir ' . $this->dateTimeNow();
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
    $config['page_title'] = "Laporan Data Supir";
    $config['page_description'] = "Data Supir";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Data Supir"],
    ];

    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = Driver::where('another_expedition_id', NULL)
      ->orderBy('name', 'asc')
      ->get();
    return view('backend.report.reportdrivers.print', compact('config', 'page_breadcrumbs', 'cooperationDefault', 'data'));
  }
}
