<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cooperation;
use App\Models\InvoiceLdo;
use App\Models\JobOrder;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Yajra\DataTables\Facades\DataTables;

class ReportInvoiceLdoController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:reportinvoiceldo-list|reportinvoiceldo-create|reportinvoiceldo-edit|reportinvoiceldo-delete', ['only' => ['index']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Invoice Ldo";
    $config['page_description'] = "Laporan Invoice Ldo";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Invoice Ldo"],
    ];
    $config['excel_url'] = 'reportinvoiceldo/document?type=EXCEL';
    $config['pdf_url'] = 'reportinvoiceldo/document?type=PDF';
    $config['print_url'] = 'reportinvoiceldo/print';

    if ($request->ajax()) {
      $date = $request->date;
      $status_payment = $request->status_payment;
      $data = InvoiceLdo::with(['anotherexpedition:id,name'])
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
        ->addColumn('details_url', function (Invoiceldo $invoiceldo) {
          return route('backend.reportinvoiceldo.datatabledetail', $invoiceldo->id);
        })
        ->make(true);
    }
    return view('backend.report.reportinvoiceldo.index', compact('config', 'page_breadcrumbs'));
  }

  public function print(Request $request)
  {
    $date = $request->date;
    $status_payment = $request->status_payment;
    $data = InvoiceLdo::with(['anotherexpedition:id,name'])
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

    $config['page_title'] = "Laporan Invoice Ldo";
    $config['page_description'] = "Laporan Invoice Ldo";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Invoice Ldo"],
    ];

    $cooperationDefault = Cooperation::where('default', '1')->first();

    return view('backend.report.reportinvoiceldo.print', compact('config', 'page_breadcrumbs', 'cooperationDefault', 'data', 'date', 'status_payment'));
  }

  public function document(Request $request)
  {
    $cooperationDefault = Cooperation::where('default', '1')->first();
    $type = $request->type;
    $date = $request->date;
    $status_payment = $request->status_payment;
    $data = InvoiceLdo::with(['anotherexpedition:id,name'])
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
    $sheet->setCellValue('A1', 'Laporan Invoice Ldo');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->mergeCells('A3:C3');
    $sheet->setCellValue('A3', 'Priode: ' . (!empty($date) ? $date : 'All Date'));
    $sheet->mergeCells('A4:C4');
    $sheet->setCellValue('A4', 'Status Pembayaran: ' . (!empty($status_pembayaran) ? ucwords($status_pembayaran) : 'All'));
    $sheet->mergeCells('H1:J1');
    $sheet->setCellValue('H1', $cooperationDefault['nickname']);
    $sheet->mergeCells('H2:J2');
    $sheet->setCellValue('H2', $cooperationDefault['address']);
    $sheet->mergeCells('H3:J3');
    $sheet->setCellValue('H3', 'Telp: ' . $cooperationDefault['phone']);
    $sheet->mergeCells('H4:J4');
    $sheet->setCellValue('H4', 'Fax: ' . $cooperationDefault['fax']);

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
      $sheet->getStyle('A' . $startCell . ':J' . $startCell)->applyFromArray($borderTop);
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
      $sheet->setCellValue('A' . $startCell, "Nama LDO");
      $sheet->setCellValue('C' . $startCell, ': ' . $item->anotherexpedition->name);
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
        $sheet->setCellValue('B' . $startCell, $child->num_bill);
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
      $sheet->getStyle('A' . $startCell . ':J' . $startCell)->applyFromArray($borderTop);
    endforeach;
    $filename = 'Laporan Invoice Ldo ' . $this->dateTimeNow();
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

  public function datatabledetail($id)
  {
    $data = JobOrder::with(['driver:id,name', 'routefrom:id,name', 'routeto:id,name', 'cargo:id,name', 'transport:id,num_pol'])
      ->where('invoice_ldo_id', $id);

    return Datatables::of($data)->make(true);
  }

}
