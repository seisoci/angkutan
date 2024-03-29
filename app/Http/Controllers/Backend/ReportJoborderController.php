<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cooperation;
use App\Models\Costumer;
use App\Models\JobOrder;
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

class ReportJoborderController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:reportjoborders-list|reportjoborders-create|reportjoborders-edit|reportjoborders-delete', ['only' => ['index']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Tagihan Job Order";
    $config['page_description'] = "Laporan Tagihan Job Order";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Tagihan Job Order"],
    ];
    $config['excel_url'] = 'reportjoborders/document?type=EXCEL';
    $config['pdf_url'] = 'reportjoborders/document?type=PDF';
    $config['print_url'] = 'reportjoborders/print';

    if ($request->ajax()) {
      $date = $request['date'];
      $costumer_id = $request['costumer_id'];
      $transport_id = $request['transport_id'];
      $data = JobOrder::with('costumer:id,name', 'routefrom:id,name', 'routeto:id,name',
        'transport:id,num_pol', 'cargo:id,name')
        ->when($date, function ($query, $date) {
          $date_format = explode(" / ", $date);
          $date_begin = $date_format[0];
          $date_end = $date_format[1];
          return $query->whereBetween('date_begin', [$date_begin, $date_end]);
        })
        ->when($costumer_id, function ($query, $costumer_id) {
          return $query->where('costumer_id', $costumer_id);
        })
        ->when($transport_id, function ($query, $transport_id) {
          return $query->where('transport_id', $transport_id);
        })
        ->where('status_cargo', '!=','batal')
        ->orderBy('date_begin', 'asc');
      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    return view('backend.report.reportjoborders.index', compact('config', 'page_breadcrumbs'));
  }

  public function document(Request $request)
  {
    $type = $request->type;
    $date = $request['date'];
    $costumer_id = $request['costumer_id'];
    $transport_id = $request['transport_id'];
    $costumer = Costumer::find($costumer_id);
    $transport = Transport::find($transport_id);
    $data = JobOrder::with('costumer:id,name', 'routefrom:id,name', 'routeto:id,name',
      'transport:id,num_pol', 'cargo:id,name')
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('date_begin', [$date_begin, $date_end]);
      })
      ->when($costumer_id, function ($query, $costumer_id) {
        return $query->where('costumer_id', $costumer_id);
      })
      ->when($transport_id, function ($query, $transport_id) {
        return $query->where('transport_id', $transport_id);
      })
      ->where('status_cargo', '!=','batal')
      ->orderBy('date_begin', 'asc')
      ->get();

    $cooperationDefault = Cooperation::where('default', '1')->first();

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
    $sheet->setCellValue('A1', 'Laporan Tagihan Job Order');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->mergeCells('A3:C3');
    $sheet->setCellValue('A3', 'Priode: ' . (!empty($date) ? $date : 'All Date'));
    $sheet->mergeCells('A4:C4');
    $sheet->setCellValue('A4', 'Costumer: ' . (!empty($costumer) ? $costumer->name : 'All'));
    $sheet->mergeCells('A5:C5');
    $sheet->setCellValue('A5', 'No. Polisi: ' . (!empty($transport) ? $transport->num_pol : 'All'));
    $sheet->mergeCells('I1:M1');
    $sheet->setCellValue('I1', $cooperationDefault['nickname']);
    $sheet->mergeCells('I2:M2');
    $sheet->setCellValue('I2', $cooperationDefault['address']);
    $sheet->mergeCells('I3:M3');
    $sheet->setCellValue('I3', 'Telp: ' . $cooperationDefault['phone']);
    $sheet->mergeCells('I4:M4');
    $sheet->setCellValue('I4', 'Fax: ' . $cooperationDefault['fax']);

    $sheet->getColumnDimension('A')->setWidth(3.55);
    $sheet->getColumnDimension('B')->setWidth(10);
    $sheet->getColumnDimension('C')->setWidth(10);
    $sheet->getColumnDimension('D')->setWidth(15);
    $sheet->getColumnDimension('E')->setWidth(30);
    $sheet->getColumnDimension('F')->setWidth(20);
    $sheet->getColumnDimension('G')->setWidth(20);
    $sheet->getColumnDimension('H')->setWidth(12);
    $sheet->getColumnDimension('I')->setWidth(14);
    $sheet->getColumnDimension('J')->setWidth(8);
    $sheet->getColumnDimension('K')->setWidth(14);
    $sheet->getColumnDimension('L')->setWidth(8);
    $sheet->getColumnDimension('M')->setWidth(14);
//    $sheet->getColumnDimension('N')->setWidth(14);
//    $sheet->getColumnDimension('O')->setWidth(14);

    $sheet->getStyle('A7')->getAlignment()->setHorizontal('center');
    $sheet->getStyle('I7:O7')->getAlignment()->setHorizontal('right');
    $sheet->getStyle('J7')->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A7', 'No.');
    $sheet->setCellValue('B7', 'Tanggal.');
    $sheet->setCellValue('C7', 'No. Polisi');
    $sheet->setCellValue('D7', 'No. Prefix');
    $sheet->setCellValue('E7', 'No. SJ');
    $sheet->setCellValue('F7', 'No. Shipment');
    $sheet->setCellValue('G7', 'Nama Pelanggan');
    $sheet->setCellValue('H7', 'Rute Dari');
    $sheet->setCellValue('I7', 'Rute Tujuan');
    $sheet->setCellValue('J7', 'Jenis Barang');
    $sheet->setCellValue('K7', 'Tarif (Rp.)');
    $sheet->setCellValue('L7', 'Qty');
    $sheet->setCellValue('M7', 'Total');
//    $sheet->setCellValue('N7', 'Tax (%)');
//    $sheet->setCellValue('O7', 'Total (Inc. Tax)');
//    $sheet->setCellValue('P7', 'Fee Thanks');
//    $sheet->setCellValue('Q7', 'Total (Inc. Tax, Thanks))');

    $startCell = 7;
    $startCellFilter = 7;
    $no = 1;
    $sheet->getStyle('A' . $startCell . ':M' . $startCell . '')
      ->applyFromArray($borderTopBottom)
      ->applyFromArray($borderLeftRight);
    foreach ($data as $item):
      $startCell++;
      $sheet->getStyle('A' . $startCell . ':M' . $startCell . '')->applyFromArray($borderLeftRight);
      $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('center');
      $sheet->getStyle('I' . $startCell . ':M' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->setCellValue('A' . $startCell, $no++);
      $sheet->setCellValue('B' . $startCell, $item->date_begin);
      $sheet->setCellValue('C' . $startCell, $item->transport->num_pol);
      $sheet->setCellValue('D' . $startCell, $item->num_bill);
      $sheet->setCellValue('E' . $startCell, $item->no_sj);
      $sheet->setCellValue('F' . $startCell, $item->no_shipment);
      $sheet->setCellValue('G' . $startCell, $item->costumer->name);
      $sheet->setCellValue('H' . $startCell, $item->routefrom->name);
      $sheet->setCellValue('I' . $startCell, $item->routeto->name);
      $sheet->setCellValue('J' . $startCell, $item->cargo->name);
      $sheet->setCellValue('K' . $startCell, $item->basic_price);
      $sheet->setCellValue('L' . $startCell, $item->payload);
      $sheet->setCellValue('M' . $startCell, $item->total_basic_price);
//      $sheet->setCellValue('N' . $startCell, $item->tax_percent);
//      $sheet->setCellValue('O' . $startCell, $item->total_basic_price_after_tax);
//      $sheet->setCellValue('P' . $startCell, $item->fee_thanks);
//      $sheet->setCellValue('Q' . $startCell, $item->total_basic_price_after_thanks);
    endforeach;
    $sheet->setAutoFilter('B' . $startCellFilter . ':M' . $startCell);
    $sheet->getStyle('A' . $startCell . ':M' . $startCell . '')->applyFromArray($borderBottom);

    $endForSum = $startCell;
    $startCell++;
    $startCellFilter++;
    $sheet->getStyle('A' . $startCell . ':M' . $startCell . '')->applyFromArray($borderAll);
    $sheet->getStyle('E' . $startCell . ':M' . $startCell . '')->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('right');
    $sheet->setCellValue('A' . $startCell, 'Total Rp.');
    $sheet->mergeCells('A' . $startCell . ':J' . $startCell . '');
    $sheet->getStyle('A' . $startCell . ':M' . $startCell)->getFont()->setBold(true);
    $sheet->setCellValue('K' . $startCell, '=SUM(K' . $startCellFilter . ':K' . $endForSum . ')');
    $sheet->setCellValue('L' . $startCell, '=SUM(L' . $startCellFilter . ':L' . $endForSum . ')');
    $sheet->setCellValue('M' . $startCell, '=SUM(M' . $startCellFilter . ':M' . $endForSum . ')');
//    $sheet->setCellValue('N' . $startCell, '=SUM(N' . $startCellFilter . ':N' . $endForSum . ')');
//    $sheet->setCellValue('O' . $startCell, '=SUM(O' . $startCellFilter . ':O' . $endForSum . ')');
//    $sheet->setCellValue('P' . $startCell, '=SUM(P' . $startCellFilter . ':P' . $endForSum . ')');
//    $sheet->setCellValue('Q' . $startCell, '=SUM(Q' . $startCellFilter . ':Q' . $endForSum . ')');

    $filename = 'Laporan Tagihan Job Order ' . $this->dateTimeNow();
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
    $date = $request['date'];
    $costumer_id = $request['costumer_id'];
    $transport_id = $request['transport_id'];
    $costumer = Costumer::find($costumer_id);
    $transport = Transport::find($transport_id);
    $data = JobOrder::with('costumer:id,name', 'routefrom:id,name', 'routeto:id,name',
      'transport:id,num_pol', 'cargo:id,name')
      ->when($date, function ($query, $date) {
        $date_format = explode(" / ", $date);
        $date_begin = $date_format[0];
        $date_end = $date_format[1];
        return $query->whereBetween('date_begin', [$date_begin, $date_end]);
      })
      ->when($costumer_id, function ($query, $costumer_id) {
        return $query->where('costumer_id', $costumer_id);
      })
      ->when($transport_id, function ($query, $transport_id) {
        return $query->where('transport_id', $transport_id);
      })
      ->where('status_cargo', '!=','batal')
      ->orderBy('date_begin', 'asc')
      ->get();
    $config['page_title'] = "Laporan Tagihan Job Order";
    $config['page_description'] = "Laporan Tagihan Job Order";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Tagihan Job Order"],
    ];

    $cooperationDefault = Cooperation::where('default', '1')->first();

    return view('backend.report.reportjoborders.print', compact('config', 'page_breadcrumbs', 'cooperationDefault', 'data', 'date', 'costumer', 'transport'));
  }

}
