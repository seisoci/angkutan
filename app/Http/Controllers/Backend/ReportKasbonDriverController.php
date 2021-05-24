<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Kasbon;
use App\Models\Setting;
use App\Traits\CarbonTrait;
use Carbon\Carbon;
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

  public function index(Request $request)
  {
    $config['page_title'] = "laporan Data Kasbon Supir";
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
      $data = Kasbon::join('drivers', 'kasbons.driver_id', '=', 'drivers.id')
        ->select(['kasbons.*', 'drivers.id', 'drivers.name'])
        ->when($status, function ($query, $status) {
          if ($status == 'none') {
            return $query->where('kasbons.status', '=', '0');
          } else {
            return $query->where('kasbons.status', $status);
          }
        })
        ->when($driver_id, function ($query, $driver_id) {
          return $query->where('kasbons.driver_id', $driver_id);
        })
        ->when($date, function ($query, $date) {
          $date_format = explode(" / ", $date);
          $date_begin = $this->toDateServer($date_format[0]);
          $date_end = $this->toDateServer($date_format[1]);
          return $query->whereBetween('kasbons.created_at', [$date_begin, $date_end]);
        });
      return DataTables::of($data)
        ->make(true);
    }
    return view('backend.report.reportkasbondrivers.index', compact('config', 'page_breadcrumbs'));
  }

  public function document(Request $request)
  {
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });

    $type = $request->type;
    $driver_id = $request->driver_id;
    $status = $request->status;
    $date = $request->date;
    $driver = Driver::find($driver_id)->name ?? "All";

    if($status == 'none'){
      $statusPembayaran = 'Unpiad';
    }elseif ($status == "1"){
      $statusPembayaran = 'Paid';
    }else{
      $statusPembayaran = "All";
    }

    $data = Kasbon::join('drivers', 'kasbons.driver_id', '=', 'drivers.id')
      ->select(['kasbons.*', 'drivers.id', 'drivers.name'])
      ->when($status, function ($query, $status) {
        if ($status == 'none') {
          return $query->where('kasbons.status', '=', '0');
        } else {
          return $query->where('kasbons.status', $status);
        }
      })
      ->when($driver_id, function ($query, $driver_id) {
        return $query->where('kasbons.driver_id', $driver_id);
      })
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $this->toDateServer($date_format[0]);
        $date_end = $this->toDateServer($date_format[1]);
        return $query->whereBetween('kasbons.created_at', [$date_begin, $date_end]);
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
    $sheet->setCellValue('A1', 'Laporan Kasbon Supir');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->mergeCells('A3:C3');
    $sheet->setCellValue('A3', 'Priode: ' . (!empty($date) ? $date : 'All Date'));
    $sheet->mergeCells('A4:C4');
    $sheet->setCellValue('A4', 'Filter Nama Supir: ' . $driver);
    $sheet->mergeCells('A5:C5');
    $sheet->setCellValue('A5', 'Status: ' . $statusPembayaran);
    $sheet->mergeCells('E1:F1');
    $sheet->setCellValue('E1', $profile['name']);
    $sheet->mergeCells('E2:F2');
    $sheet->setCellValue('E2', $profile['address']);
    $sheet->mergeCells('E3:F3');
    $sheet->setCellValue('E3', 'Telp: ' . $profile['telp']);
    $sheet->mergeCells('E4:F4');
    $sheet->setCellValue('E4', 'Fax: ' . $profile['fax']);

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

    $filename = 'Laporan Kasbon Supir ' . $this->dateTimeNow();
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

  public function print(Request $request){
    $config['page_title'] = "Laporan Data Pelanggan";
    $config['page_description'] = "Data Pelanggan";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Data Pelanggan"],
    ];

    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });

    $driver_id = $request->driver_id;
    $status = $request->status;
    $date = $request->date;
    $driver = Driver::find($driver_id)->name ?? "All";
    if($status == 'none'){
      $statusPembayaran = 'Unpiad';
    }elseif ($status == "1"){
      $statusPembayaran = 'Paid';
    }else{
      $statusPembayaran = "All";
    }

    $data = Kasbon::join('drivers', 'kasbons.driver_id', '=', 'drivers.id')
      ->select(['kasbons.*', 'drivers.id', 'drivers.name'])
      ->when($status, function ($query, $status) {
        if ($status == 'none') {
          return $query->where('kasbons.status', '=', '0');
        } else {
          return $query->where('kasbons.status', $status);
        }
      })
      ->when($driver_id, function ($query, $driver_id) {
        return $query->where('kasbons.driver_id', $driver_id);
      })
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $this->toDateServer($date_format[0]);
        $date_end = $this->toDateServer($date_format[1]);
        return $query->whereBetween('kasbons.created_at', [$date_begin, $date_end]);
      })
      ->get();
    return view('backend.report.reportkasbondrivers.print', compact('config', 'page_breadcrumbs', 'profile', 'data', 'driver', 'statusPembayaran', ));
  }

}
