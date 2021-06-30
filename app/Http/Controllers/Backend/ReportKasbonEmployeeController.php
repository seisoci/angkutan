<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Employee;
use App\Models\KasbonEmployee;
use App\Models\Setting;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class ReportKasbonEmployeeController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:reportkasbonemployees-list|reportkasbonemployees-create|reportkasbonemployees-edit|reportkasbonemployees-delete', ['only' => ['index']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Data Kasbon Karyawaan";
    $config['page_description'] = "Laporan Data Kasbon Karyawaan";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Data Kasbon Karyawaan"],
    ];
    $config['excel_url'] = 'reportkasbonemployees/document?type=EXCEL';
    $config['pdf_url'] = 'reportkasbonemployees/document?type=PDF';
    $config['print_url'] = 'reportkasbonemployees/print';
    $employee_id = $request->employee_id;
    $status = $request->status;
    $date = $request->date;

    if ($request->ajax()) {
      $data = KasbonEmployee::join('employees', 'kasbon_employees.employee_id', '=', 'employees.id')
        ->select(['kasbon_employees.*', 'employees.name'])
        ->when($status, function ($query, $status) {
          if ($status == 'none') {
            return $query->where('kasbon_employees.status', '=', '0');
          } else {
            return $query->where('kasbon_employees.status', $status);
          }
        })
        ->when($employee_id, function ($query, $employee_id) {
          return $query->where('kasbon_employees.employee_id', $employee_id);
        })
        ->when($date, function ($query, $date) {
          $date_format = explode(" / ", $date);
          $date_begin = $this->toDateServerStart($date_format[0]);
          $date_end = $this->toDateServerEnd($date_format[1]);
          return $query->whereBetween('kasbon_employees.created_at', [$date_begin, $date_end]);
        })
        ->orderBy('kasbon_employees.created_at', 'asc');

      return DataTables::of($data)
        ->make(true);
    }
    return view('backend.report.reportkasbonemployees.index', compact('config', 'page_breadcrumbs'));
  }

  public function document(Request $request)
  {
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });

    $type = $request->type;
    $employee_id = $request->employee_id;
    $status = $request->status;
    $date = $request->date;
    $employee = Driver::find($employee_id)->name ?? "All";

    if ($status == 'none') {
      $statusPembayaran = 'Unpiad';
    } elseif ($status == "1") {
      $statusPembayaran = 'Paid';
    } else {
      $statusPembayaran = "All";
    }

    $data = KasbonEmployee::join('employees', 'kasbon_employees.employee_id', '=', 'employees.id')
      ->select(['kasbon_employees.*', 'employees.name'])
      ->when($status, function ($query, $status) {
        if ($status == 'none') {
          return $query->where('kasbon_employees.status', '=', '0');
        } else {
          return $query->where('kasbon_employees.status', $status);
        }
      })
      ->when($employee_id, function ($query, $employee_id) {
        return $query->where('kasbon_employees.employee_id', $employee_id);
      })
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $this->toDateServerStart($date_format[0]);
        $date_end = $this->toDateServerEnd($date_format[1]);
        return $query->whereBetween('kasbon_employees.created_at', [$date_begin, $date_end]);
      })
      ->orderBy('kasbon_employees.created_at', 'asc')
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
    $sheet->setCellValue('A1', 'Laporan Data Kasbon Karyawaan');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->mergeCells('A3:C3');
    $sheet->setCellValue('A3', 'Priode: ' . (!empty($date) ? $date : 'All Date'));
    $sheet->mergeCells('A4:C4');
    $sheet->setCellValue('A4', 'Filter Nama Supir: ' . $employee);
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

    $filename = 'Laporan Data Kasbon Karyawaan ' . $this->dateTimeNow();
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
    $config['page_title'] = "Laporan Data Kasbon Karyawaan";
    $config['page_description'] = "Laporan Data Kasbon Karyawaan";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Data Pelanggan"],
    ];

    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });

    $employee_id = $request->employee_id;
    $status = $request->status;
    $date = $request->date;
    $employee = Employee::find($employee_id)->name ?? "All";
    if ($status == 'none') {
      $statusPembayaran = 'Unpiad';
    } elseif ($status == "1") {
      $statusPembayaran = 'Paid';
    } else {
      $statusPembayaran = "All";
    }

    $data = KasbonEmployee::join('employees', 'kasbon_employees.employee_id', '=', 'employees.id')
      ->select(['kasbon_employees.*', 'employees.name'])
      ->when($status, function ($query, $status) {
        if ($status == 'none') {
          return $query->where('kasbon_employees.status', '=', '0');
        } else {
          return $query->where('kasbon_employees.status', $status);
        }
      })
      ->when($employee_id, function ($query, $employee_id) {
        return $query->where('kasbon_employees.employee_id', $employee_id);
      })
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $this->toDateServerStart($date_format[0]);
        $date_end = $this->toDateServerEnd($date_format[1]);
        return $query->whereBetween('kasbon_employees.created_at', [$date_begin, $date_end]);
      })
      ->orderBy('kasbon_employees.created_at', 'asc')
      ->get();
    return view('backend.report.reportkasbonemployees.print', compact('config', 'page_breadcrumbs', 'profile', 'data', 'employee', 'statusPembayaran',));
  }
}
