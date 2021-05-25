<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Transport;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class ReportTransportController extends Controller
{
  use CarbonTrait;

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Data Kendaraan";
    $config['page_description'] = "Laporan Data Kendaraan";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Data Kendaraan"],
    ];
    $config['excel_url'] = 'reporttransports/document?type=EXCEL';
    $config['pdf_url'] = 'reporttransports/document?type=PDF';
    $config['print_url'] = 'reporttransports/print';

    if ($request->ajax()) {
      $data = Transport::where('another_expedition_id', NULL);
      return DataTables::of($data)
        ->addIndexColumn()
        ->editColumn('type_car', function(Transport $transport) {
          return ucwords($transport->type_car);
        })
        ->make(true);
    }
    return view('backend.report.reporttransports.index', compact('config', 'page_breadcrumbs'));
  }

  public function document(Request $request)
  {
    $type = $request->type;
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });
    $data = Transport::all();
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
        ],
        'vertical' => [
          'borderStyle' => Border::BORDER_THIN,
        ],
      ],
    ];

    $sheet->getColumnDimension('A')->setWidth(3.55);
    $sheet->getColumnDimension('B')->setWidth(18);
    $sheet->getColumnDimension('C')->setWidth(14);
    $sheet->getColumnDimension('D')->setWidth(15);
    $sheet->getColumnDimension('E')->setWidth(15);
    $sheet->getColumnDimension('F')->setWidth(35);

    $sheet->getStyle('A6')->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A6', 'No.');
    $sheet->setCellValue('B6', 'No. Polisi');
    $sheet->setCellValue('C6', 'Tahun');
    $sheet->setCellValue('D6', 'STNK');
    $sheet->setCellValue('E6', 'KIR');
    $sheet->setCellValue('F6', 'Jenis Kendaraan');

    $startCell = 6;
    $startCellFilter = 6;
    $no = 1;
    $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')
      ->applyFromArray($borderTopBottom)
      ->applyFromArray($borderLeftRight);
    foreach ($data as $item):
      $startCell++;
      $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')->applyFromArray($borderLeftRight);
      $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('center');
      $sheet->setCellValue('A' . $startCell, $no++);
      $sheet->setCellValue('B' . $startCell, $item->num_pol);
      $sheet->setCellValue('C' . $startCell, $item->year);
      $sheet->setCellValue('D' . $startCell, $item->expired_stnk);
      $sheet->setCellValue('E' . $startCell, $item->expired_kir);
      $sheet->setCellValue('F' . $startCell, $item->type);
    endforeach;
    $sheet->setAutoFilter('B' . $startCellFilter . ':F' . $startCell);
    $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')->applyFromArray($borderBottom);

    $sheet->mergeCells('A1:C1');
    $sheet->setCellValue('A1', 'Laporan Data Pelanggan');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->mergeCells('E1:G1');
    $sheet->setCellValue('E1', $profile['name']);
    $sheet->mergeCells('E2:G2');
    $sheet->setCellValue('E2', $profile['address']);
    $sheet->mergeCells('E3:G3');
    $sheet->setCellValue('E3', 'Telp: ' . $profile['telp']);
    $sheet->mergeCells('E4:G4');
    $sheet->setCellValue('E4', 'Fax: ' . $profile['fax']);

    $filename = 'Laporan Data Kendaraan '. $this->dateTimeNow();
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

  public function print(Request $request){
    $status = $request->status;
    $config['page_title'] = "Laporan Data Kendaraan";
    $config['page_description'] = "Laporan Data Kendaraan";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Data Kendaraan"],
    ];

    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });

    $data = Transport::where('another_expedition_id', NULL)
      ->orderBy('num_pol', 'asc')
      ->get();
    return view('backend.report.reporttransports.print', compact('config', 'page_breadcrumbs', 'profile', 'data'));
  }

}
