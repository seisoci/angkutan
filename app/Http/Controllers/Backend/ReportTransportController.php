<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cooperation;
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

  function __construct()
  {
    $this->middleware('permission:reporttransports-list|reporttransports-create|reporttransports-edit|reporttransports-delete', ['only' => ['index']]);
  }

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
      $data = Transport::selectRaw('
            transports.*,
            COALESCE(
              (
              SELECT
                CASE
                    WHEN `status_cargo` = "mulai" THEN "Tidak Tersedia"
                    WHEN `status_cargo` = "transfer" THEN "Tidak Tersedia"
                    ELSE "Tersedia"
                END
              FROM `job_orders`
              WHERE `job_orders`.`transport_id` = `transports`.`id`
              ORDER BY `job_orders`.`date_begin` DESC
              LIMIT 1
              ), "Tersedia") AS `status_jo`
        ')->whereNull('another_expedition_id');

      return DataTables::of($data)
        ->addIndexColumn()
        ->editColumn('type_car', function (Transport $transport) {
          return ucwords($transport->type_car);
        })
        ->make(true);
    }
    return view('backend.report.reporttransports.index', compact('config', 'page_breadcrumbs'));
  }

  public function document(Request $request)
  {
    $type = $request->type;
    $cooperationDefault = Cooperation::where('default', '1')->first();
    $data = Transport::selectRaw('
            transports.*,
            COALESCE(
              (
              SELECT
                CASE
                    WHEN `status_cargo` = "mulai" THEN "Tidak Tersedia"
                    WHEN `status_cargo` = "transfer" THEN "Tidak Tersedia"
                    ELSE "Tersedia"
                END
              FROM `job_orders`
              WHERE `job_orders`.`transport_id` = `transports`.`id`
              ORDER BY `job_orders`.`date_begin` DESC
              LIMIT 1
              ), "Tersedia") AS `status_jo`
        ')->whereNull('another_expedition_id')
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
    $sheet->getColumnDimension('G')->setWidth(35);
    $sheet->getColumnDimension('H')->setWidth(35);

    $sheet->getStyle('A6')->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A6', 'No.');
    $sheet->setCellValue('B6', 'No. Polisi');
    $sheet->setCellValue('C6', 'Tahun');
    $sheet->setCellValue('D6', 'STNK');
    $sheet->setCellValue('E6', 'KIR');
    $sheet->setCellValue('F6', 'Jenis Kendaraan');
    $sheet->setCellValue('G6', 'Status Kendaraan');
    $sheet->setCellValue('H6', 'Status Kendaraan JO');

    $startCell = 6;
    $startCellFilter = 6;
    $no = 1;
    $sheet->getStyle('A' . $startCell . ':H' . $startCell . '')
      ->applyFromArray($borderTopBottom)
      ->applyFromArray($borderLeftRight);
    foreach ($data as $item):
      $startCell++;
      $sheet->getStyle('A' . $startCell . ':H' . $startCell . '')->applyFromArray($borderLeftRight);
      $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('center');
      $sheet->setCellValue('A' . $startCell, $no++);
      $sheet->setCellValue('B' . $startCell, $item->num_pol);
      $sheet->setCellValue('C' . $startCell, $item->year);
      $sheet->setCellValue('D' . $startCell, $item->expired_stnk);
      $sheet->setCellValue('E' . $startCell, $item->expired_kir);
      $sheet->setCellValue('F' . $startCell, $item->type);
      $sheet->setCellValue('G' . $startCell, $item->status);
      $sheet->setCellValue('H' . $startCell, $item->status_jo);
    endforeach;
    $sheet->setAutoFilter('B' . $startCellFilter . ':H' . $startCell);
    $sheet->getStyle('A' . $startCell . ':H' . $startCell . '')->applyFromArray($borderBottom);

    $sheet->mergeCells('A1:C1');
    $sheet->setCellValue('A1', 'Laporan Data Pelanggan');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->mergeCells('G1:H1');
    $sheet->setCellValue('G1', $cooperationDefault['nickname']);
    $sheet->mergeCells('G2:H2');
    $sheet->setCellValue('G2', $cooperationDefault['address']);
    $sheet->mergeCells('G3:H3');
    $sheet->setCellValue('G3', 'Telp: ' . $cooperationDefault['phone']);
    $sheet->mergeCells('G4:H4');
    $sheet->setCellValue('E4', 'Fax: ' . $cooperationDefault['fax']);

    $filename = 'Laporan Data Kendaraan ' . $this->dateTimeNow();
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
    $config['page_title'] = "Laporan Data Kendaraan";
    $config['page_description'] = "Laporan Data Kendaraan";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Data Kendaraan"],
    ];

    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = Transport::selectRaw('
            transports.*,
            COALESCE(
              (
              SELECT
                CASE
                    WHEN `status_cargo` = "mulai" THEN "Tidak Tersedia"
                    WHEN `status_cargo` = "transfer" THEN "Tidak Tersedia"
                    ELSE "Tersedia"
                END
              FROM `job_orders`
              WHERE `job_orders`.`transport_id` = `transports`.`id`
              ORDER BY `job_orders`.`date_begin` DESC
              LIMIT 1
              ), "Tersedia") AS `status_jo`
        ')->whereNull('another_expedition_id')
      ->get();

    return view('backend.report.reporttransports.print', compact('config', 'page_breadcrumbs', 'cooperationDefault', 'data'));
  }

}
