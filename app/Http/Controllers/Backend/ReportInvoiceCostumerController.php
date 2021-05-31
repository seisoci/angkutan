<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\InvoiceCostumer;
use App\Models\JobOrder;
use App\Models\Setting;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class ReportInvoiceCostumerController extends Controller
{
  use CarbonTrait;

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Invoice Pelanggan & Fee";
    $config['page_description'] = "Laporan Invoice Pelanggan & Fee";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Invoice Pelanggan & Fee"],
    ];
    $config['excel_url'] = 'reportinvoicecostumers/document?type=EXCEL';
    $config['pdf_url'] = 'reportinvoicecostumers/document?type=PDF';
    $config['print_url'] = 'reportinvoicecostumers/print';

    if ($request->ajax()) {
      $date = $request->date;
      $status_payment = $request->status_payment;
      $data = InvoiceCostumer::with(['costumer:id,name'])
        ->when($date, function ($query, $date) {
          $date_format = explode(" / ", $date);
          $date_begin = $date_format[0];
          $date_end = $date_format[1];
          return $query->whereBetween('created_at', [$date_begin, $date_end]);
        })
        ->when($status_payment, function ($query, $status_payment) {
          if ($status_payment == 'unpaid') {
            return $query->where('rest_payment', '!=', '0');
          } elseif ($status_payment == 'paid') {
            return $query->where('rest_payment', '=', '0');
          }
        })
        ->orderBy('invoice_date', 'asc');

      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('details_url', function (InvoiceCostumer $invoiceCostumer) {
          return route('backend.reportinvoicecostumers.datatabledetail', $invoiceCostumer->id);
        })
        ->make(true);
    }
    return view('backend.report.reportinvoicecostumers.index', compact('config', 'page_breadcrumbs'));
  }

  public function document(Request $request)
  {
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });

    $type = $request->type;
    $date = $request->date;
    $status_payment = $request->status_payment;
    $data = InvoiceCostumer::with(['costumer:id,name', 'joborders.driver:id,name', 'joborders.routefrom:id,name', 'joborders.routeto:id,name', 'joborders.cargo:id,name', 'joborders.transport:id,num_pol'])
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('created_at', [$date_begin, $date_end]);
      })
      ->when($status_payment, function ($query, $status_payment) {
        if ($status_payment == 'unpaid') {
          return $query->where('rest_payment', '!=', '0');
        } elseif ($status_payment == 'paid') {
          return $query->where('rest_payment', '=', '0');
        }
      })
      ->orderBy('invoice_date', 'asc')
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
    $borderLeft = [
      'borders' => [
        'left' => [
          'borderStyle' => Border::BORDER_THIN,
        ],
      ],
    ];
    $borderRight = [
      'borders' => [
        'right' => [
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
    $sheet->setCellValue('A1', 'Laporan Invoice Pelanggan & Fee');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->mergeCells('A3:C3');
    $sheet->setCellValue('A3', 'Priode: ' . (!empty($date) ? $date : 'All Date'));
    $sheet->mergeCells('A4:C4');
    $sheet->setCellValue('A4', 'Status Pembayaran: ' . (!empty($status_pembayaran) ? ucwords($status_pembayaran) : 'All'));
    $sheet->mergeCells('H1:J1');
    $sheet->setCellValue('H1', $profile['name']);
    $sheet->mergeCells('H2:J2');
    $sheet->setCellValue('H2', $profile['address']);
    $sheet->mergeCells('H3:J3');
    $sheet->setCellValue('H3', 'Telp: ' . $profile['telp']);
    $sheet->mergeCells('H4:J4');
    $sheet->setCellValue('H4', 'Fax: ' . $profile['fax']);

    $sheet->getColumnDimension('A')->setWidth(3.55);
    $sheet->getColumnDimension('B')->setWidth(14.45);
    $sheet->getColumnDimension('C')->setWidth(11.18);
    $sheet->getColumnDimension('D')->setWidth(10.73);
    $sheet->getColumnDimension('E')->setWidth(20);
    $sheet->getColumnDimension('F')->setWidth(16.82);
    $sheet->getColumnDimension('G')->setWidth(16.82);
    $sheet->getColumnDimension('H')->setWidth(13);
    $sheet->getColumnDimension('I')->setWidth(12.27);
    $sheet->getColumnDimension('J')->setWidth(19);

    $startCell = 6;

    foreach ($data as $item):
      $startCell++;
      $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
      $sheet->getStyle('A' . $startCell.':J' . $startCell)->applyFromArray($borderTop);
      $sheet->getStyle('J' . $startCell)->applyFromArray($borderRight);
      $sheet->getStyle('A' . $startCell)->getAlignment()->setHorizontal('left');
      $sheet->getStyle('J' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->mergeCells('A' . $startCell . ':B' . $startCell);
      $sheet->mergeCells('H' . $startCell . ':I' . $startCell);
      $sheet->setCellValue('A' . $startCell, "Invoice Number");
      $sheet->setCellValue('C' . $startCell, ': ' . $item->num_invoice);
      $sheet->setCellValue('H' . $startCell, "Total Tagihan");
      $sheet->setCellValue('J' . $startCell, $item->total_bill);
      $startCell++;
      $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
      $sheet->getStyle('J' . $startCell)->applyFromArray($borderRight);
      $sheet->getStyle('A' . $startCell)->getAlignment()->setHorizontal('left');
      $sheet->getStyle('J' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->mergeCells('A' . $startCell . ':B' . $startCell);
      $sheet->mergeCells('H' . $startCell . ':I' . $startCell);
      $sheet->setCellValue('A' . $startCell, "Tgl. Invoice");
      $sheet->setCellValue('C' . $startCell, ': ' . $item->date_invoice);
      $sheet->setCellValue('H' . $startCell, "Total Pembayaran");
      $sheet->setCellValue('J' . $startCell, $item->total_payment);
      $startCell++;
      $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
      $sheet->getStyle('J' . $startCell)->applyFromArray($borderRight);
      $sheet->getStyle('A' . $startCell)->getAlignment()->setHorizontal('left');
      $sheet->getStyle('J' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->mergeCells('A' . $startCell . ':B' . $startCell);
      $sheet->mergeCells('H' . $startCell . ':I' . $startCell);
      $sheet->setCellValue('A' . $startCell, "Tgl Jth Tempo Invoice");
      $sheet->setCellValue('C' . $startCell, ': ' . $item->due_date);
      $sheet->setCellValue('H' . $startCell, "Potongan");
      $sheet->setCellValue('J' . $startCell, $item->total_cut);
      $sheet->getStyle('J' . $startCell)->getFont()->setColor(new Color(Color::COLOR_RED));
      $startCell++;
      $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
      $sheet->getStyle('J' . $startCell)->applyFromArray($borderRight);
      $sheet->getStyle('A' . $startCell)->getAlignment()->setHorizontal('left');
      $sheet->getStyle('J' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->mergeCells('A' . $startCell . ':B' . $startCell);
      $sheet->mergeCells('H' . $startCell . ':I' . $startCell);
      $sheet->setCellValue('A' . $startCell, "Nama Pelanggan");
      $sheet->setCellValue('C' . $startCell, ': ' . $item->costumer->name);
      $sheet->setCellValue('H' . $startCell, "Sisa Tagihan");
      $sheet->setCellValue('J' . $startCell, $item->rest_payment);

      $no = 1;
      $startCell++;
      $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
      $sheet->getStyle('J' . $startCell)->applyFromArray($borderRight);
      $sheet->setCellValue('A' . $startCell, '#');
      $sheet->setCellValue('B' . $startCell, 'No. Surat Jalan');
      $sheet->setCellValue('C' . $startCell, 'Tgl Muat');
      $sheet->setCellValue('D' . $startCell, 'No. Polisi');
      $sheet->setCellValue('E' . $startCell, 'Nama Supir');
      $sheet->setCellValue('F' . $startCell, 'Rute Dari');
      $sheet->setCellValue('G' . $startCell, 'Rute Tujuan');
      $sheet->setCellValue('H' . $startCell, 'Muatan');
      $sheet->setCellValue('I' . $startCell, 'Fee Thanks');
      $sheet->setCellValue('J' . $startCell, 'Total Tagihan (Inc. Tax)');
      $startCell++;
      foreach ($item->joborders as $child):
        $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
        $sheet->getStyle('J' . $startCell)->applyFromArray($borderRight);
        $sheet->getStyle('I' . $startCell . ':' . 'J' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->setCellValue('A' . $startCell, $no++);
        $sheet->setCellValue('B' . $startCell, $child->num_prefix);
        $sheet->setCellValue('C' . $startCell, $child->date_begin);
        $sheet->setCellValue('D' . $startCell, $child->transport->num_pol);
        $sheet->setCellValue('E' . $startCell, $child->driver->name);
        $sheet->setCellValue('F' . $startCell, $child->routefrom->name);
        $sheet->setCellValue('G' . $startCell, $child->routeto->name);
        $sheet->setCellValue('H' . $startCell, $child->cargo->name);
        $sheet->setCellValue('I' . $startCell, $child->fee_thanks);
        $sheet->setCellValue('J' . $startCell, $child->total_basic_price_after_tax);
        $startCell++;
      endforeach;
      $sheet->getStyle('A' . $startCell.':J' . $startCell)->applyFromArray($borderTop);
    endforeach;
    $filename = 'Laporan Invoice Pelanggan & Fee ' . $this->dateTimeNow();
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
    $date = $request->date;
    $status_payment = $request->status_payment;
    $data = InvoiceCostumer::with(['costumer:id,name', 'joborders.driver:id,name', 'joborders.routefrom:id,name', 'joborders.routeto:id,name', 'joborders.cargo:id,name', 'joborders.transport:id,num_pol'])
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('created_at', [$date_begin, $date_end]);
      })
      ->when($status_payment, function ($query, $status_payment) {
        if ($status_payment == 'unpaid') {
          return $query->where('rest_payment', '!=', '0');
        } elseif ($status_payment == 'paid') {
          return $query->where('rest_payment', '=', '0');
        }
      })
      ->orderBy('invoice_date', 'asc')
      ->get();

    $config['page_title'] = "Laporan Invoice Pelanggan & Fee";
    $config['page_description'] = "Laporan Invoice Pelanggan & Fee";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Invoice Pelanggan & Fee"],
    ];

    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });

    return view('backend.report.reportinvoicecostumers.print', compact('config', 'page_breadcrumbs', 'profile', 'data', 'date', 'status_payment'));
  }


  public function datatabledetail($id)
  {
    $data = JobOrder::with(['driver:id,name', 'routefrom:id,name', 'routeto:id,name', 'cargo:id,name', 'transport:id,num_pol'])
      ->where('invoice_costumer_id', $id);

    return Datatables::of($data)->make(true);
  }
}
