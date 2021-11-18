<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cooperation;
use App\Models\Driver;
use App\Models\Kasbon;
use App\Models\PaymentKasbon;
use App\Models\Setting;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class ReportKasbonDriverController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:reportkasbondrivers-list|reportkasbondrivers-create|reportkasbondrivers-edit|reportkasbondrivers-delete', ['only' => ['index']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Data Kasbon Supir";
    $config['page_description'] = "Laporan Data Kasbon Supir";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Data Kasbon Supir"],
    ];
    $config['excel_url'] = 'reportkasbondrivers/document?type=EXCEL';
    $config['pdf_url'] = 'reportkasbondrivers/document?type=PDF';
    $config['print_url'] = 'reportkasbondrivers/print';
    $driver_id = $request->driver_id;
    $status = $request->status;
    $date = $request->date;

    if ($request->ajax()) {
      $data = PaymentKasbon::with('driver:id,name')
        ->when($status, function ($query, $status) {
          return $query->where('payment_kasbons.type', $status);
        })
        ->when($driver_id, function ($query, $driver_id) {
          return $query->where('payment_kasbons.driver_id', $driver_id);
        })
        ->when($date, function ($query, $date) {
          $date_format = explode(" / ", $date);
          $date_begin = $this->toDateServerStart($date_format[0]);
          $date_end = $this->toDateServerEnd($date_format[1]);
          return $query->whereBetween('payment_kasbons.date_payment', [$date_begin, $date_end]);
        })
        ->orderBy('payment_kasbons.date_payment', 'desc');

      return DataTables::of($data)
        ->make(true);
    }
    return view('backend.report.reportkasbondrivers.index', compact('config', 'page_breadcrumbs'));
  }

  public function document(Request $request)
  {
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $type = $request->type;
    $driver_id = $request->driver_id;
    $status = $request->status;
    $date = $request->date;
    $driver = Driver::find($driver_id)->name ?? "All";

    if ($status == 'none') {
      $statusPembayaran = 'Belum Lunas';
    } elseif ($status == '1') {
      $statusPembayaran = 'Dicicil';
    } elseif ($status == '2') {
      $statusPembayaran = "Lunas";
    }

    $data = PaymentKasbon::with('driver:id,name')
      ->when($status, function ($query, $status) {
        return $query->where('payment_kasbons.type', $status);
      })
      ->when($driver_id, function ($query, $driver_id) {
        return $query->where('payment_kasbons.driver_id', $driver_id);
      })
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $this->toDateServerStart($date_format[0]);
        $date_end = $this->toDateServerEnd($date_format[1]);
        return $query->whereBetween('payment_kasbons.date_payment', [$date_begin, $date_end]);
      })
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
    $sheet->setCellValue('A1', 'Laporan Data Kasbon Supir');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->mergeCells('A3:C3');
    $sheet->setCellValue('A3', 'Priode: ' . (!empty($date) ? $date : 'All Date'));
    $sheet->mergeCells('A4:C4');
    $sheet->setCellValue('A4', 'Filter Nama Supir: ' . $driver);
    $sheet->mergeCells('A5:C5');
    $sheet->setCellValue('A5', 'Status: ' . $statusPembayaran);
    $sheet->mergeCells('E1:F1');
    $sheet->setCellValue('E1', $cooperationDefault['nickname']);
    $sheet->mergeCells('E2:F2');
    $sheet->setCellValue('E2', $cooperationDefault['address']);
    $sheet->mergeCells('E3:F3');
    $sheet->setCellValue('E3', 'Telp: ' . $cooperationDefault['phone']);
    $sheet->mergeCells('E4:F4');
    $sheet->setCellValue('E4', 'Fax: ' . $cooperationDefault['fax']);

    $sheet->getColumnDimension('A')->setWidth(3.55);
    $sheet->getColumnDimension('B')->setWidth(16);
    $sheet->getColumnDimension('C')->setWidth(14);
    $sheet->getColumnDimension('D')->setWidth(6.5);
    $sheet->getColumnDimension('E')->setWidth(26);
    $sheet->getColumnDimension('F')->setWidth(19);

    $sheet->getStyle('A7')->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A7', 'No.');
    $sheet->setCellValue('B7', 'Nama Supir');
    $sheet->setCellValue('C7', 'Nominal');
    $sheet->setCellValue('D7', 'Status');
    $sheet->setCellValue('E7', 'Keterangan');
    $sheet->setCellValue('F7', 'Tanggal Pinjam');

    $startCell = 7;
    $startCellFilter = 7;
    $no = 1;
    $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')
      ->applyFromArray($borderTopBottom);
    foreach ($data as $item):
      $startCell++;
      $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')->applyFromArray($borderTopBottom);
      $sheet->getStyle('A' . $startCell . ':' . 'F' . $startCell)->getAlignment()->setVertical('top');
      $sheet->getStyle('C' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->setCellValue('A' . $startCell, $no++);
      $sheet->setCellValue('B' . $startCell, $item->name);
      $sheet->setCellValue('C' . $startCell, $item->amount);
      $sheet->setCellValue('D' . $startCell, $item->status == 1 ? 'Paid' : 'Unpaid');
      $sheet->setCellValue('E' . $startCell, $item->memo);
      $sheet->setCellValue('F' . $startCell, $item->created_at);
    endforeach;
    $sheet->setAutoFilter('B' . $startCellFilter . ':F' . $startCell);
    $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')->applyFromArray($borderBottom);

    $filename = 'Laporan Data Kasbon Supir ' . $this->dateTimeNow();
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
    $config['page_title'] = "Laporan Data Kasbon Supir";
    $config['page_description'] = "Laporan Data Kasbon Supir";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Data Pelanggan"],
    ];

    $cooperationDefault = Cooperation::where('default', '1')->first();

    $driver_id = $request->driver_id;
    $status = $request->status;
    $date = $request->date;
    $driver = Driver::find($driver_id)->name ?? "All";

    if ($status == 'pembayaran') {
      $statusPembayaran = 'Pembayaran';
    } elseif ($status == 'hutang') {
      $statusPembayaran = "Hutang";
    } else {
      $statusPembayaran = "All";
    }

    $data = PaymentKasbon::with('driver:id,name')
      ->when($status, function ($query, $status) {
        return $query->where('payment_kasbons.type', $status);
      })
      ->when($driver_id, function ($query, $driver_id) {
        return $query->where('payment_kasbons.driver_id', $driver_id);
      })
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $this->toDateServerStart($date_format[0]);
        $date_end = $this->toDateServerEnd($date_format[1]);
        return $query->whereBetween('payment_kasbons.date_payment', [$date_begin, $date_end]);
      })
      ->get();
    return view('backend.report.reportkasbondrivers.print', compact('config', 'page_breadcrumbs', 'cooperationDefault', 'data', 'driver', 'statusPembayaran'));
  }

}
