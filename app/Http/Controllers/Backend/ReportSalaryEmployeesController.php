<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\MonthlySalaryDetail;
use App\Models\Setting;
use App\Traits\CarbonTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class ReportSalaryEmployeesController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:reportsalaryemployees-list|reportsalaryemployees-create|reportsalaryemployees-edit|reportsalaryemployees-delete', ['only' => ['index']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Gaji Karyawaan";
    $config['page_description'] = "Laporan Gaji Karyawaan";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Gaji Karyawaan"],
    ];
    $config['excel_url'] = 'reportsalaryemployees/document?type=EXCEL';
    $config['pdf_url'] = 'reportsalaryemployees/document?type=PDF';
    $config['print_url'] = 'reportsalaryemployees/print';

    if ($request->ajax()) {
      $date_begin = $request->dateBegin;
      $date_end = $request->dateEnd;
      $employee_id = $request->employee_id;
      $status_payment = $request->status_payment;
      $data = MonthlySalaryDetail::select(DB::raw('
       monthly_salary_details.id,
       employees.name AS employee_name,
       monthly_salaries.name AS monthly_name,
       SUM(monthly_salary_detail_employees.amount) AS total_salary,
       monthly_salary_details.status
       '))
        ->leftJoin('employees', 'employees.id', '=', 'monthly_salary_details.employee_id')
        ->leftJoin('monthly_salaries', 'monthly_salaries.id', '=', 'monthly_salary_details.monthly_salary_id')
        ->leftJoin('monthly_salary_detail_employees', 'monthly_salary_detail_employees.monthly_salary_detail_id', '=', 'monthly_salary_details.id')
        ->when($date_begin, function ($query, $date_begin) {
          return $query->whereYear('monthly_salaries.name', '>=', $this->convertToYear($date_begin))
            ->whereMonth('monthly_salaries.name', '>=', $this->convertToMonth($date_begin));
        })
        ->when($date_end, function ($query, $date_end) {
          return $query->whereYear('monthly_salaries.name', '<=', $this->convertToYear($date_end))
            ->whereMonth('monthly_salaries.name', '<=', $this->convertToMonth($date_end));
        })
        ->when($employee_id, function ($query, $employee_id) {
          return $query->where('monthly_salary_details.employee_id', $employee_id);
        })
        ->when($status_payment, function ($query, $status_payment) {
          if ($status_payment == 'unpaid') {
            return $query->where('monthly_salary_details.status', '=', '0');
          } elseif ($status_payment == 'paid') {
            return $query->where('monthly_salary_details.status', '=', '1');
          }
        })
        ->groupBy('monthly_salary_details.id')
        ->orderBy('monthly_salaries.name', 'asc');

      return DataTables::of($data)
        ->addIndexColumn()
        ->editColumn('monthly_name', '{{ \Carbon\Carbon::parse($monthly_name)->timezone("Asia/Jakarta")->format("Y F") }}')
        ->make(true);
    }
    return view('backend.report.reportsalaryemployees.index', compact('config', 'page_breadcrumbs'));
  }

  public function document(Request $request)
  {
    $type = $request->type;
    $date_begin = $request->dateBegin;
    $date_end = $request->dateEnd;
    $employee_id = $request->employee_id;
    $status_payment = $request->status_payment;

    if (!empty($date_begin) && !empty($date_end)) {
      $date = Carbon::parse($date_begin)->timezone("Asia/Jakarta")->format("Y F") . " sd " . Carbon::parse($date_end)->timezone("Asia/Jakarta")->format("Y F");
    } elseif (empty($date_begin) && !empty($date_end)) {
      $date = "Tgl Dulu sd " . Carbon::parse($date_end)->timezone("Asia/Jakarta")->format("Y F");
    } elseif (!empty($date_begin) && empty($date_end)) {
      $date = Carbon::parse($date_begin)->timezone("Asia/Jakarta")->format("Y F") . " sd Tgl Skrg";
    } else {
      $date = "All";
    }

    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });

    $data = MonthlySalaryDetail::select(DB::raw('
       monthly_salary_details.id,
       employees.name AS employee_name,
       monthly_salaries.name AS monthly_name,
       SUM(monthly_salary_detail_employees.amount) AS total_salary,
       monthly_salary_details.status
       '))
      ->leftJoin('employees', 'employees.id', '=', 'monthly_salary_details.employee_id')
      ->leftJoin('monthly_salaries', 'monthly_salaries.id', '=', 'monthly_salary_details.monthly_salary_id')
      ->leftJoin('monthly_salary_detail_employees', 'monthly_salary_detail_employees.monthly_salary_detail_id', '=', 'monthly_salary_details.id')
      ->when($date_begin, function ($query, $date_begin) {
        return $query->whereYear('monthly_salaries.name', '>=', $this->convertToYear($date_begin))
          ->whereMonth('monthly_salaries.name', '>=', $this->convertToMonth($date_begin));
      })
      ->when($date_end, function ($query, $date_end) {
        return $query->whereYear('monthly_salaries.name', '<=', $this->convertToYear($date_end))
          ->whereMonth('monthly_salaries.name', '<=', $this->convertToMonth($date_end));
      })
      ->when($employee_id, function ($query, $employee_id) {
        return $query->where('monthly_salary_details.employee_id', $employee_id);
      })
      ->when($status_payment, function ($query, $status_payment) {
        if ($status_payment == 'unpaid') {
          return $query->where('monthly_salary_details.status', '=', '0');
        } elseif ($status_payment == 'paid') {
          return $query->where('monthly_salary_details.status', '=', '1');
        }
      })
      ->groupBy('monthly_salary_details.id')
      ->orderBy('monthly_salaries.name', 'asc')
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
    $sheet->setCellValue('A1', 'Laporan Gaji Karyawaan');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->mergeCells('A3:C3');
    $sheet->setCellValue('A3', 'Priode: ' . $date);
    $sheet->mergeCells('A4:C4');
    $sheet->setCellValue('A4', 'Supir: ' . (!empty($employee) ? $employee->name : 'ALL'));
    $sheet->mergeCells('D1:E1');
    $sheet->setCellValue('D1', $profile['name']);
    $sheet->mergeCells('D2:E2');
    $sheet->setCellValue('D2', $profile['address']);
    $sheet->mergeCells('D3:E3');
    $sheet->setCellValue('D3', 'Telp: ' . $profile['telp']);
    $sheet->mergeCells('D4:E4');
    $sheet->setCellValue('D4', 'Fax: ' . $profile['fax']);

    $sheet->getColumnDimension('A')->setWidth(3.55);
    $sheet->getColumnDimension('B')->setWidth(20);
    $sheet->getColumnDimension('C')->setWidth(40);
    $sheet->getColumnDimension('D')->setWidth(15);
    $sheet->getColumnDimension('E')->setWidth(15);
    $sheet->getRowDimension('2')->setRowHeight(30);
    $sheet->getStyle('D2')->getAlignment()->setVertical(Alignment::VERTICAL_DISTRIBUTED);


    $sheet->getStyle('A6')->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A6', 'No.');
    $sheet->setCellValue('B6', 'Gaji Bulan');
    $sheet->setCellValue('C6', 'Nama Karyawaan');
    $sheet->setCellValue('D6', 'Status');
    $sheet->setCellValue('E6', 'Total gaji');


    $startCell = 6;
    $startCellFilter = 6;
    $no = 1;
    $sheet->getStyle('A' . $startCell . ':E' . $startCell . '')
      ->applyFromArray($borderTopBottom)
      ->applyFromArray($borderLeftRight);
    foreach ($data as $item):
      $startCell++;
      $sheet->getStyle('A' . $startCell . ':E' . $startCell . '')->applyFromArray($borderLeftRight);
      $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('center');
      $sheet->getStyle('E' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->setCellValue('A' . $startCell, $no++);
      $sheet->setCellValue('B' . $startCell, $item->monthly_name);
      $sheet->setCellValue('C' . $startCell, $item->employee_name);
      $sheet->setCellValue('D' . $startCell, $item->status == "1" ? "Paid" : "Unpaid");
      $sheet->setCellValue('E' . $startCell, !empty($item->total_salary) ? $item->total_salary : 0);
    endforeach;
    $sheet->setAutoFilter('B' . $startCellFilter . ':E' . $startCell);
    $sheet->getStyle('A' . $startCell . ':E' . $startCell . '')->applyFromArray($borderBottom);

    $endForSum = $startCell;
    $startCell++;
    $startCellFilter++;
    $sheet->getStyle('A' . $startCell . ':E' . $startCell . '')->applyFromArray($borderAll);
    $sheet->getStyle('E' . $startCell . ':E' . $startCell . '')->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('right');
    $sheet->setCellValue('A' . $startCell, 'Total Rp.');
    $sheet->mergeCells('A' . $startCell . ':D' . $startCell . '');
    $sheet->getStyle('A' . $startCell . ':E' . $startCell)->getFont()->setBold(true);
    $sheet->setCellValue('E' . $startCell, '=SUM(E' . $startCellFilter . ':E' . $endForSum . ')');

    $filename = 'Laporan Gaji Karyawaan ' . $this->dateTimeNow();
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
    $date_begin = $request->dateBegin;
    $date_end = $request->dateEnd;
    $employee_id = $request->employee_id;
    $status_payment = $request->status_payment;
    if (!empty($date_begin) && !empty($date_end)) {
      $date = Carbon::parse($date_begin)->timezone("Asia/Jakarta")->format("Y F") . " sd " . Carbon::parse($date_end)->timezone("Asia/Jakarta")->format("Y F");
    } elseif (empty($date_begin) && !empty($date_end)) {
      $date = "Tgl Dulu sd " . Carbon::parse($date_end)->timezone("Asia/Jakarta")->format("Y F");
    } elseif (!empty($date_begin) && empty($date_end)) {
      $date = Carbon::parse($date_begin)->timezone("Asia/Jakarta")->format("Y F") . " sd Tgl Skrg";
    } else {
      $date = "All";
    }
    $config['page_title'] = "Laporan Gaji Karyawaan";
    $config['page_description'] = "Laporan Gaji Karyawaan";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Gaji Karyawaan"],
    ];

    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });


    $driver = Employee::find($employee_id);
    $data = MonthlySalaryDetail::select(DB::raw('
       monthly_salary_details.id,
       employees.name AS employee_name,
       monthly_salaries.name AS monthly_name,
       SUM(monthly_salary_detail_employees.amount) AS total_salary,
       monthly_salary_details.status
       '))
      ->leftJoin('employees', 'employees.id', '=', 'monthly_salary_details.employee_id')
      ->leftJoin('monthly_salaries', 'monthly_salaries.id', '=', 'monthly_salary_details.monthly_salary_id')
      ->leftJoin('monthly_salary_detail_employees', 'monthly_salary_detail_employees.monthly_salary_detail_id', '=', 'monthly_salary_details.id')
      ->when($date_begin, function ($query, $date_begin) {
        return $query->whereYear('monthly_salaries.name', '>=', $this->convertToYear($date_begin))
          ->whereMonth('monthly_salaries.name', '>=', $this->convertToMonth($date_begin));
      })
      ->when($date_end, function ($query, $date_end) {
        return $query->whereYear('monthly_salaries.name', '<=', $this->convertToYear($date_end))
          ->whereMonth('monthly_salaries.name', '<=', $this->convertToMonth($date_end));
      })
      ->when($employee_id, function ($query, $employee_id) {
        return $query->where('monthly_salary_details.employee_id', $employee_id);
      })
      ->when($status_payment, function ($query, $status_payment) {
        if ($status_payment == 'unpaid') {
          return $query->where('monthly_salary_details.status', '=', '0');
        } elseif ($status_payment == 'paid') {
          return $query->where('monthly_salary_details.status', '=', '1');
        }
      })
      ->groupBy('monthly_salary_details.id')
      ->orderBy('monthly_salaries.name', 'asc')
      ->get();


    return view('backend.report.reportsalaryemployees.print', compact('config', 'page_breadcrumbs', 'profile', 'data', 'date', 'driver'));
  }
}
