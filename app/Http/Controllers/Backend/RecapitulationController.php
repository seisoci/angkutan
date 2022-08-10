<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cooperation;
use App\Models\Driver;
use App\Models\JobOrder;
use App\Models\Setting;
use App\Models\Transport;
use Illuminate\Http\Request;
use Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Html;

class RecapitulationController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:recapitulation-list|recapitulation-create|recapitulation-edit|recapitulation-delete', ['only' => ['index']]);
  }

  public function index(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'transport_id' => 'integer|nullable',
      'driver_id' => 'integer|nullable',
      'date_begin' => 'date_format:Y-m-d',
      'date_end' => 'date_format:Y-m-d',
    ]);

    $config['page_title'] = "Laporan Rekapitulasi";
    $config['page_description'] = "Laporan Rekapitulasi";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Rekapitulasi"],
    ];
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = array();
    $transport = NULL;
    $driver = NULL;
    if ($validator->passes()) {
      $driver_id = $request->driver_id;
      $transport_id = $request->transport_id;
      $date_begin = $request->date_begin;
      $date_end = $request->date_end;

      if ($request->all() != NULL) {
        $transport = isset($transport_id) || !empty($transport_id) ? Transport::findOrFail($transport_id) : 'Semua Mobil';
        $driver = isset($driver_id) || !empty($driver_id) ? Driver::select('id', 'name')->findOrFail($driver_id) : 'Semua Supir';
        $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name', 'operationalexpense.expense'])
          ->withSum('operationalexpense', 'amount')
          ->where('type', 'self')
          ->where('status_cargo', 'selesai')
          ->when($driver_id, function ($query, $driver_id) {
            return isset($driver_id) ? $query->where('driver_id', $driver_id) : NULL;
          })
          ->when($transport_id, function ($query, $transport_id) {
            return isset($transport_id) ? $query->where('transport_id', $transport_id) : NULL;
          })
          ->whereBetween('date_begin', [$date_begin, $date_end])
          ->get();
      }
    } else {
      return redirect()->back()->withErrors($validator->errors());
    }
    return view('backend.operational.recapitulation.index', compact('config', 'page_breadcrumbs', 'data', 'cooperationDefault', 'date_begin', 'date_end', 'transport_id', 'driver_id', 'driver', 'transport'));
  }

  public function print(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'transport_id' => 'integer|nullable',
      'driver_id' => 'integer|nullable',
      'date_begin' => 'date_format:Y-m-d',
      'date_end' => 'date_format:Y-m-d',
    ]);

    $config['page_title'] = "Laporan Rekapitulasi";
    $config['page_description'] = "Laporan Rekapitulasi";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Rekapitulasi"],
    ];
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $data = array();
    $transport = NULL;
    $driver = NULL;
    if ($validator->passes()) {
      $driver_id = $request->driver_id;
      $transport_id = $request->transport_id;
      $date_begin = $request->date_begin;
      $date_end = $request->date_end;

      if ($request->all() != NULL) {
        $transport = isset($transport_id) || !empty($transport_id) ? Transport::findOrFail($transport_id) : 'Semua Mobil';
        $driver = isset($driver_id) || !empty($driver_id) ? Driver::select('id', 'name')->findOrFail($driver_id) : 'Semua Supir';
        $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name', 'operationalexpense.expense'])
          ->withSum('operationalexpense', 'amount')
          ->where('type', 'self')
          ->where('status_cargo', 'selesai')
          ->when($driver_id, function ($query, $driver_id) {
            return $query->where('driver_id', $driver_id);
          })
          ->when($transport_id, function ($query, $transport_id) {
            return isset($transport_id) ? $query->where('transport_id', $transport_id) : NULL;
          })
          ->whereBetween('date_begin', [$date_begin, $date_end])
          ->get();
      }
    } else {
      return redirect()->back()->withErrors($validator->errors());
    }

    return view('backend.operational.recapitulation.print', compact('config', 'page_breadcrumbs', 'data', 'cooperationDefault', 'date_begin', 'date_end', 'transport_id', 'driver_id', 'driver', 'transport'));
  }

  public function document(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'transport_id' => 'integer|required',
      'driver_id' => 'integer|nullable',
      'date_begin' => 'date_format:Y-m-d|required',
      'date_end' => 'date_format:Y-m-d|required',
    ]);
    if ($validator->passes()) {
      $driver_id = $request->driver_id;
      $transport_id = $request->transport_id;
      $date_begin = $request->date_begin;
      $date_end = $request->date_end;

      $this->toDocument($driver_id, $transport_id, $date_begin, $date_end, $request->type);

    } else {
      return abort(404, 'Data Tidak ditemukan');
    }
  }

  public function toDocument($driver_id = NULL, $transport_id = NULL, $date_begin = NULL, $date_end = NULL, $type)
  {
    $cooperationDefault = Cooperation::where('default', '1')->first();
    $driver = isset($driver_id) || !empty($driver_id) ? Driver::select('id', 'name')->findOrFail($driver_id) : 'Semua Supir';

    $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name', 'operationalexpense.expense'])
      ->withSum('operationalexpense', 'amount')
      ->where('type', 'self')
      ->where('status_cargo', 'selesai')
      ->where('status_document', '1')
      ->when($driver_id, function ($query, $driver_id) {
        return isset($driver_id) ? $query->where('driver_id', $driver_id) : NULL;
      })
      ->when($transport_id, function ($query, $transport_id) {
        return isset($transport_id) ? $query->where('transport_id', $transport_id) : NULL;
      })
      ->whereBetween('date_begin', [$date_begin, $date_end])
      ->get();
    $transport = isset($transport_id) || !empty($transport_id) ? Transport::findOrFail($transport_id) : 'Semua Mobil';

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_LEGAL)->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
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


    $sheet->getColumnDimension('A')->setWidth(3.55);
    $sheet->getColumnDimension('B')->setWidth(14);
    $sheet->getColumnDimension('C')->setWidth(18);
    $sheet->getColumnDimension('D')->setWidth(34);
    $sheet->getColumnDimension('E')->setWidth(21);
    $sheet->getColumnDimension('F')->setWidth(21);
    $sheet->getColumnDimension('G')->setWidth(12);
    $sheet->getColumnDimension('H')->setWidth(14);
    $sheet->getColumnDimension('I')->setWidth(8);
    $sheet->getColumnDimension('J')->setWidth(8);
    $sheet->getColumnDimension('K')->setWidth(8);
    $sheet->getColumnDimension('L')->setWidth(14);

    $sheet->mergeCells('A1:C1');
    $sheet->setCellValue('A1', 'Laporan Pendapatan Mobil');
    $sheet->setCellValue('A2', 'No. Polisi:');
    $sheet->setCellValue('B2', $transport->num_pol);
    $sheet->mergeCells('G1:J1');
    $sheet->setCellValue('G1', $cooperationDefault['nickname']);
    $sheet->mergeCells('G2:J2');
    $sheet->setCellValue('G2', $cooperationDefault['address']);
    $sheet->mergeCells('G3:J3');
    $sheet->setCellValue('G3', 'Telp: ' . $cooperationDefault['phone']);
    $sheet->mergeCells('G4:J4');
    $sheet->setCellValue('G4', 'Fax: ' . $cooperationDefault['fax']);

    $sheet->getStyle('A6')->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A6', 'No.');
    $sheet->setCellValue('B6', 'Tanggal');
    $sheet->setCellValue('C6', 'S. Jalan');
    $sheet->setCellValue('D6', 'Pelanggan');
    $sheet->setCellValue('E6', 'Dari');
    $sheet->setCellValue('F6', 'Tujuan');
    $sheet->setCellValue('G6', 'Jenis Barang');
    $sheet->setCellValue('H6', 'Tarif(Rp.)');
    $sheet->setCellValue('I6', 'Qty(Unit)');
    $sheet->setCellValue('J6', 'Pajak (%)');
    $sheet->setCellValue('K6', 'Fee Pemberian');
    $sheet->setCellValue('L6', 'Total(Rp.)');

    $cellBasicPrice = NULL;
    $arrayBasicPrice = [];
    $startCell = 6;
    $startForSum = 7;
    $endForSum = 6;
    $no = 1;
    $sheet->getStyle('A' . $startCell . ':L' . $startCell . '')->applyFromArray($borderTopBottom)->applyFromArray($borderLeftRight);
    foreach ($data as $item):
      $startCell++;
      $sheet->getStyle('A' . $startCell . ':L' . $startCell . '')->applyFromArray($borderLeftRight);
      $sheet->getStyle('H' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->getStyle('I' . $startCell)->getNumberFormat()->setFormatCode('0.00');
      $sheet->getStyle('J' . $startCell . ':L' . $startCell . '')->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->getStyle('B' . $startCell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDD);
      $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('center');
      $sheet->setCellValue('A' . $startCell, $no++);
      $sheet->setCellValue('B' . $startCell, $item->date_begin);
      $sheet->setCellValue('C' . $startCell, $item->prefix . '-' . $item->num_bill);
      $sheet->setCellValue('D' . $startCell, $item->costumer->name);
      $sheet->setCellValue('E' . $startCell, $item->routefrom->name);
      $sheet->setCellValue('F' . $startCell, $item->routeto->name);
      $sheet->setCellValue('G' . $startCell, $item->cargo->name);
      $sheet->setCellValue('H' . $startCell, $item->basic_price);
      $sheet->setCellValue('I' . $startCell, $item->payload);
      $sheet->setCellValue('J' . $startCell, $item->tax_percent);
      $sheet->setCellValue('K' . $startCell, $item->fee_thanks);
      $sheet->setCellValue('L' . $startCell, $item->total_basic_price_after_thanks);
      $arrayBasicPrice[] = 'L' . $startCell;
    endforeach;
    $sheet->getStyle('A' . $startCell . ':L' . $startCell . '')->applyFromArray($borderBottom);
    //Total Pendapatan Gross Mobil
    $endForSum = $startCell;
    $startCell++;
    $sheet->getStyle('H' . $startCell . ':L' . $startCell . '')->applyFromArray($borderAll);
    $sheet->getStyle('K' . $startCell . ':L' . $startCell . '')->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('H' . $startCell . '')->getAlignment()->setHorizontal('right');
    $sheet->setCellValue('H' . $startCell, 'Total Rp.');
    $sheet->mergeCells('H' . $startCell . ':K' . $startCell . '');
    $sheet->getStyle('H' . $startCell . ':L' . $startCell)->getFont()->setBold(true);
    $sheet->setCellValue('L' . $startCell, '=SUM(L' . $startForSum . ':L' . $endForSum . ')');
    $cellBasicPrice = "L" . $startCell;

    //Laporan Biaya Operasional
    $cellOperational = NULL;
    $totalOperational = "=";
    $arrayOperational = [];
    $startCell += 3;
    $sheet->mergeCells('A' . $startCell . ':C' . $startCell . '');
    $sheet->setCellValue('A' . $startCell, 'Laporan Biaya Operasional');
    $sheet->setCellValue('E' . $startCell, $cooperationDefault['nickname']);
    $sheet->mergeCells('E' . $startCell . ':G' . $startCell . '');
    $sheet->setCellValue('E' . ++$startCell, $cooperationDefault['address']);
    $sheet->mergeCells('E' . $startCell . ':G' . $startCell . '');
    $sheet->setCellValue('E' . ++$startCell, 'Telp: ' . $cooperationDefault['phone']);
    $sheet->setCellValue('E' . ++$startCell, 'Fax: ' . $cooperationDefault['fax']);
    $startCell++;

    //-----------------Operasional-----------------
    foreach ($data as $key => $item):
      $no = 1;
      $endForSum = $startCell;
      $startForSum = ($startCell) + 2;
      $startCell++;
      $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')->applyFromArray($borderTop);
      $sheet->getStyle('E' . $startCell . ':F' . $startCell . '')->getAlignment()->setHorizontal('right');
      $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('center');
      $sheet->setCellValue('A' . $startCell, 'No.');
      $sheet->setCellValue('B' . $startCell, 'Tanggal');
      $sheet->setCellValue('C' . $startCell, 'Master Biaya');
      $sheet->setCellValue('D' . $startCell, 'Keterangan');
      $sheet->setCellValue('E' . $startCell, 'Jumlah');
      $sheet->setCellValue('F' . $startCell, 'S. Jalan');
      $startCell++;
      $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')->applyFromArray($borderTop);
      $sheet->getStyle('B' . $startCell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDD);
      $sheet->getStyle('E' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->getStyle('E' . $startCell . ':F' . $startCell . '')->getAlignment()->setHorizontal('right');
      $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('center');
      $sheet->setCellValue('A' . $startCell, $no++);
      $sheet->setCellValue('B' . $startCell, $item->date_begin);
      $sheet->setCellValue('C' . $startCell, 'UANG JALAN');
      $sheet->setCellValue('E' . $startCell, $item->road_money);
      $sheet->setCellValue('F' . $startCell, $item->prefix . '-' . $item->num_bill);
      foreach ($item->operationalexpense as $itemExpense):
        $startCell++;
        $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')->applyFromArray($borderTop);
        $sheet->getStyle('B' . $startCell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDD);
        $sheet->getStyle('E' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('E' . $startCell . ':F' . $startCell . '')->getAlignment()->setHorizontal('right');
        $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('center');
        $sheet->setCellValue('A' . $startCell, $no++);
        $sheet->setCellValue('B' . $startCell, $item->date_begin);
        $sheet->setCellValue('C' . $startCell, $itemExpense->expense->name);
        $sheet->setCellValue('D' . $startCell, $itemExpense->description);
        $sheet->setCellValue('E' . $startCell, $itemExpense->amount);
        $sheet->setCellValue('F' . $startCell, $item->prefix . '-' . $item->num_bill);
      endforeach;
      //Sub Total Operasional
      $endForSum = $startCell;
      $startCell++;
      $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')->applyFromArray($borderBottom)->applyFromArray($borderTop);
      $sheet->getStyle('E' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->getStyle('D' . $startCell)->getAlignment()->setHorizontal('right');
      $sheet->getStyle('D' . $startCell . ':E' . $startCell)->getFont()->setBold(true);
      $sheet->setCellValue('D' . $startCell, 'Sub Total Rp.');
      $sheet->setCellValue('E' . $startCell, '=SUM(E' . $startForSum . ':E' . $endForSum . ')');
      $totalOperational .= $key == 0 ? 'E' . $startCell : '+E' . $startCell;
      array_push($arrayOperational, 'E' . $startCell);
      $startCell++;
    endforeach;
    //Total Operasional
    $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')->applyFromArray($borderBottom)->applyFromArray($borderTop);
    $sheet->getStyle('E' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('D' . $startCell . ':E' . $startCell)->getFont()->setBold(true);
    $sheet->getStyle('D' . $startCell)->getAlignment()->setHorizontal('right');
    $sheet->setCellValue('D' . $startCell, 'Total Rp.');
    $sheet->setCellValue('E' . $startCell, $totalOperational);
    $cellOperational = "E" . $startCell;
    $startCell++;


    //-----------------Sparepart-----------------
    $cellSparepart = NULL;
    $startCell += 3;
    $arraySparepart = [];
    $sheet->mergeCells('A' . $startCell . ':C' . $startCell . '');
    $sheet->setCellValue('A' . $startCell, 'Laporan Pemakaian SparePart');
    $sheet->setCellValue('E' . $startCell, $cooperationDefault['nickname']);
    $sheet->mergeCells('E' . $startCell . ':G' . $startCell . '');
    $sheet->setCellValue('E' . ++$startCell, $cooperationDefault['address']);
    $sheet->mergeCells('E' . $startCell . ':G' . $startCell . '');
    $sheet->setCellValue('E' . ++$startCell, 'Telp: ' . $cooperationDefault['phone']);
    $sheet->setCellValue('E' . ++$startCell, 'Fax: ' . $cooperationDefault['fax']);
    $no = 1;
    $endForSum = $startCell;
    $startForSum = ($startCell) + 2;
    $startCell++;
    $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')->applyFromArray($borderTop);
    $sheet->getStyle('F' . $startCell)->getAlignment()->setHorizontal('right');
    $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A' . $startCell, 'No.');
    $sheet->setCellValue('B' . $startCell, 'Tanggal');
    $sheet->setCellValue('C' . $startCell, 'S. Jalan');
    $sheet->setCellValue('D' . $startCell, 'Nama Supir');
    $sheet->setCellValue('E' . $startCell, 'No. Polisi');
    $sheet->setCellValue('F' . $startCell, 'Jumlah');
    foreach ($data as $item):
      $startCell++;
      $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')->applyFromArray($borderTop);
      $sheet->getStyle('B' . $startCell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDD);
      $sheet->getStyle('F' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('center');
      $sheet->setCellValue('A' . $startCell, $no++);
      $sheet->setCellValue('B' . $startCell, $item->date_begin);
      $sheet->setCellValue('C' . $startCell, $item->prefix . '-' . $item->num_bill);
      $sheet->setCellValue('D' . $startCell, $item->driver->name);
      $sheet->setCellValue('E' . $startCell, $item->transport->num_pol);
      $sheet->setCellValue('F' . $startCell, '=(' . $arrayBasicPrice[($no) - 2] . '-' . $arrayOperational[($no) - 2] . ')*(' . $item->cut_sparepart_percent . '/100)');
      array_push($arraySparepart, 'F' . $startCell);
    endforeach;
    $endForSum = $startCell;
    $startCell++;
    $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')->applyFromArray($borderBottom)->applyFromArray($borderTop);
    $sheet->getStyle('F' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('E' . $startCell . ':F' . $startCell)->getFont()->setBold(true);
    $sheet->getStyle('E' . $startCell)->getAlignment()->setHorizontal('right');
    $sheet->setCellValue('E' . $startCell, 'Total Rp.');
    $sheet->setCellValue('F' . $startCell, '=SUM(F' . $startForSum . ':F' . $endForSum . ')');
    $cellSparepart = "F" . $startCell;
    $startCell++;

    //-----------------Gaji-----------------
    $cellGaji = NULL;
    $startCell += 3;
    $sheet->mergeCells('A' . $startCell . ':B' . $startCell . '');
    $sheet->setCellValue('A' . $startCell, 'Laporan Gaji');
    $sheet->setCellValue('E' . $startCell, $cooperationDefault['nickname']);
    $sheet->mergeCells('E' . $startCell . ':G' . $startCell . '');
    $sheet->setCellValue('E' . ++$startCell, $cooperationDefault['address']);
    $sheet->mergeCells('E' . $startCell . ':G' . $startCell . '');
    $sheet->setCellValue('E' . ++$startCell, 'Telp: ' . $cooperationDefault['phone']);
    $sheet->setCellValue('E' . ++$startCell, 'Fax: ' . $cooperationDefault['fax']);
    $no = 1;
    $endForSum = $startCell;
    $startForSum = ($startCell) + 2;
    $startCell++;
    $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')->applyFromArray($borderTop);
    $sheet->getStyle('F' . $startCell)->getAlignment()->setHorizontal('right');
    $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('center');
    $sheet->setCellValue('A' . $startCell, 'No.');
    $sheet->setCellValue('B' . $startCell, 'Tanggal');
    $sheet->setCellValue('C' . $startCell, 'S. Jalan');
    $sheet->setCellValue('D' . $startCell, 'Nama Supir');
    $sheet->setCellValue('E' . $startCell, 'No. Polisi');
    $sheet->setCellValue('F' . $startCell, 'Gaji');
    foreach ($data as $item):
      $startCell++;
      $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')->applyFromArray($borderTop);
      $sheet->getStyle('B' . $startCell)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDD);
      $sheet->getStyle('F' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->getStyle('A' . $startCell . '')->getAlignment()->setHorizontal('center');
      $sheet->setCellValue('A' . $startCell, $no++);
      $sheet->setCellValue('B' . $startCell, $item->date_begin);
      $sheet->setCellValue('C' . $startCell, $item->prefix . '-' . $item->num_bill);
      $sheet->setCellValue('D' . $startCell, $item->driver->name);
      $sheet->setCellValue('E' . $startCell, $item->transport->num_pol);
      $sheet->setCellValue('F' . $startCell, '=(' . $arrayBasicPrice[($no) - 2] . '-' . $arrayOperational[($no) - 2] . '-' . $arraySparepart[($no) - 2] . ')*(' . $item->salary_percent . '/100)');
    endforeach;
    $endForSum = $startCell;
    $startCell++;
    $sheet->getStyle('A' . $startCell . ':F' . $startCell . '')->applyFromArray($borderBottom)->applyFromArray($borderTop);
    $sheet->getStyle('F' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->getStyle('E' . $startCell . ':F' . $startCell)->getFont()->setBold(true);
    $sheet->getStyle('E' . $startCell)->getAlignment()->setHorizontal('right');
    $sheet->setCellValue('E' . $startCell, 'Total Rp.');
    $sheet->setCellValue('F' . $startCell, '=SUM(F' . $startForSum . ':F' . $endForSum . ')');
    $cellGaji = "F" . $startCell;
    $startCell += 3;

    //Laporan Settlement
    $sheet->getStyle('A' . $startCell . ':C' . (($startCell + 2)) . '')->applyFromArray($borderOutline);
    $sheet->mergeCells('A' . $startCell . ':B' . $startCell . '');
    $sheet->getStyle('C' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->setCellValue('A' . $startCell, 'Total Pendapatan');
    $sheet->setCellValue('C' . $startCell, '=' . $cellBasicPrice . '');
    $startCell++;
    $sheet->mergeCells('A' . $startCell . ':B' . $startCell . '');
    $sheet->getStyle('C' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->setCellValue('A' . $startCell, 'Total Biaya');
    $sheet->setCellValue('C' . $startCell, '=' . $cellOperational . '+' . $cellSparepart . '+' . $cellGaji . '');
    $startCell++;
    $sheet->mergeCells('A' . $startCell . ':B' . $startCell . '');
    $sheet->getStyle('C' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->setCellValue('A' . $startCell, 'Total Bersih');
    $sheet->setCellValue('C' . $startCell, '=C' . (($startCell) - 2) . '-C' . (($startCell) - 1) . '');

    $filename = 'Laporan_Rekapitulasi';
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
}
