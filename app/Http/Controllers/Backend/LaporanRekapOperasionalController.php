<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cooperation;
use App\Models\Driver;
use App\Models\JobOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanRekapOperasionalController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:laporan-rekap-gaji-bulanan-list|laporan-rekap-gaji-bulanan-create|laporan-rekap-gaji-bulanan-edit|laporan-rekap-gaji-bulanan-delete',
      ['only' => ['index']]);
    $this->middleware('permission:laporan-rekap-gaji-bulanan-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:laporan-rekap-gaji-bulanan-edit', ['only' => ['edit', 'update']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Rekap Operasional";
    $config['page_description'] = "Daftar List Laporan Rekap Operasional";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Laporan Rekap Operasional"],
    ];

    if ($request->ajax()) {
      return $this->table($this->dataJobOrder($request));
    }

    return view('backend.report.laporan-rekap-operasional.index', compact('config', 'page_breadcrumbs'));
  }

  private function table($data)
  {
    $html = '
      <div class="table-responsive">
        <table class="table table-striped">
          <tbody>
    ';

    foreach ($data ?? [] as $item):
      $html .= "
          <tr>
            <th>Ongkos</th>
            <th style='min-width: 100px'>Tgl</th>
            <th>Supir</th>
            <th>Kubikasi</th>
            <th style='min-width: 100px'>Tgl Transaksi</th>
            <th class='text-right' style='min-width: 100px'>Uang Jalan</th>
            <th class='text-right' style='min-width: 100px'>Nominal</th>
            <th style='min-width: 250px'>Keterangan</th>
            <th style='min-width: 200px'>Pelanggan</th>
            <th></th>
            <th class='text-right' style='min-width: 100px'>Total</th>
            <th class='text-right' style='min-width: 100px'>Total Stlh Pot. Pajak</th>
            <th class='text-right' style='min-width: 100px'>Hasil Kotor</th>
            <th class='text-right' style='min-width: 100px'>Fee</th>
            <th class='text-right' style='min-width: 100px'>Sparepart</th>
            <th class='text-right' style='min-width: 100px'>Gaji Supir</th>
            <th class='text-right' style='min-width: 100px'>Sisa Hasil</th>
          </tr>
          <tr>
            <td><div class='autoNumeric text-right'>{$item['basic_price']}</div></td>
            <td>{$item['date_begin']}</td>
            <td>{$item['driver']['name']}</td>
            <td>{$item['payload']}</td>
            <td></td>
            <td></td>
            <td><div class='autoNumeric text-right'>{$item['road_money']}</div></td>
            <td>{$item['routefrom']['name']} - {$item['routeto']['name']}</td>
            <td>{$item['costumer']['name']}</td>
            <td></td>
            <td><div class='autoNumeric text-right'>{$item['total_basic_price']}</div></td>
            <td><div class='autoNumeric text-right'>{$item['total_basic_price_after_tax']}</div></td>
            <td><div class='autoNumeric text-right'>".($item['total_basic_price_after_tax'] - $item['total_operational'])."</div></td>
            <td><div class='autoNumeric text-right'>{$item['fee_thanks']}</div></td>
            <td><div class='autoNumeric text-right'>{$item['total_sparepart']}</div></td>
            <td><div class='autoNumeric text-right'>{$item['total_salary']}</div></td>
            <td><div class='autoNumeric text-right'>{$item['total_clean_summary']}</div></td>
          </tr>
        ";

      /* Rincian Uang Jalan*/
      foreach ($item['roadmoneydetail'] ?? [] as $itemOperional):
        $html .= "
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>".Carbon::parse($itemOperional['created_at'])->format('Y-m-d')."</td>
            <td><div class='autoNumeric text-right'>{$itemOperional['amount']}</div></td>
            <td></td>
            <td>{$itemOperional['description']}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
        ";
      endforeach;

      /* Rincian Operasional*/
      foreach ($item['operationalexpense'] ?? [] as $itemOperional):
        $html .= "
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>".Carbon::parse($itemOperional['created_at'])->format('Y-m-d')."</td>
            <td></td>
            <td><div class='autoNumeric text-right'>{$itemOperional['amount']}</div></td>
            <td>{$itemOperional['description']}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
        ";
      endforeach;

      /* Total Operasional */
      $html .= "
          <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th class='fw-bold'>Total</th>
            <th><div class='text-right autoNumeric'>".$item['roadmoneydetail_sum_amount']."</div></th>
            <th><div class='text-right autoNumeric'>".($item['road_money'] + $item['operationalexpense_sum_amount'])."</div></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
          </tr>
        ";
    endforeach;


    $html .= '</tbody></table></div>';
    return $html;
  }

  public function export(Request $request)
  {
    $spreadsheet = new Spreadsheet();
    $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
    $spreadsheet->getDefaultStyle()->getFont()->setSize(8)->setBold(false);

    $sheet = $spreadsheet->getActiveSheet();
    $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_LEGAL)->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);


    $cooperationDefault = Cooperation::where('default', '1')->first();
    $driver =  Driver::find($request['driver_id']);

    $sheet->mergeCells('A1:C1');
    $sheet->setCellValue('A1', 'Laporan Rekap Operasional');
    $sheet->setCellValue('A2', 'Supir:');
    $sheet->setCellValue('B2', $driver['name'] ?? '');
    $sheet->mergeCells('A3:B3');
    $sheet->setCellValue('A3', 'Tgl Mulai(Dari):');
    $sheet->setCellValue('C3', $request['date_begin']);
    $sheet->mergeCells('A4:B4');
    $sheet->setCellValue('A4', 'Tgl Mulai(Sampai):');
    $sheet->setCellValue('C4', $request['date_end']);

    $sheet->mergeCells('L1:Q1');
    $sheet->setCellValue('L1', $cooperationDefault['nickname']);
    $sheet->mergeCells('L2:Q2');
    $sheet->setCellValue('L2', $cooperationDefault['address']);
    $sheet->mergeCells('L3:Q3');
    $sheet->setCellValue('L3', 'Telp: ' . $cooperationDefault['phone']);
    $sheet->mergeCells('L4:Q4');
    $sheet->setCellValue('L4', 'Fax: ' . $cooperationDefault['fax']);

    $data = $this->dataJobOrder($request);
    $sheet->getColumnDimension('A')->setWidth(9.5);
    $sheet->getColumnDimension('B')->setWidth(13);
    $sheet->getColumnDimension('C')->setWidth(12);
    $sheet->getColumnDimension('D')->setWidth(9);
    $sheet->getColumnDimension('E')->setWidth(12);
    $sheet->getColumnDimension('F')->setWidth(12);
    $sheet->getColumnDimension('G')->setWidth(12);
    $sheet->getColumnDimension('H')->setWidth(25);
    $sheet->getColumnDimension('I')->setWidth(20);
    $sheet->getColumnDimension('J')->setWidth(8);
    $sheet->getColumnDimension('K')->setWidth(12);
    $sheet->getColumnDimension('L')->setWidth(19);
    $sheet->getColumnDimension('M')->setWidth(12);
    $sheet->getColumnDimension('N')->setWidth(12);
    $sheet->getColumnDimension('O')->setWidth(12);
    $sheet->getColumnDimension('P')->setWidth(12);
    $sheet->getColumnDimension('Q')->setWidth(12);

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

    $borderTopBottom = [
      'borders' => [
        'bottom' => [
          'borderStyle' => Border::BORDER_THIN,
        ],
        'top' => [
          'borderStyle' => Border::BORDER_THIN,
        ],
      ],
    ];


    $startCell = 6;
    $startMerge = 0;
    foreach ($data ?? [] as $item):
      $startCell++;
      /* Header */
      $sheet->getStyle("A{$startCell}:Q{$startCell}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('88bef5');
      $sheet->getStyle("A{$startCell}:Q{$startCell}")->applyFromArray($borderTopBottom)->applyFromArray($borderLeftRight);
      $sheet->getStyle("B{$startCell}")->getAlignment()->setHorizontal('right');
      $sheet->getStyle("K{$startCell}:Q{$startCell}")->getAlignment()->setHorizontal('right');
      $sheet->setCellValue("A{$startCell}", 'Ongkos');
      $sheet->setCellValue("B{$startCell}", 'Tanggal');
      $sheet->setCellValue("C{$startCell}", 'Supir');
      $sheet->setCellValue("D{$startCell}", 'Kubikasi');
      $sheet->setCellValue("E{$startCell}", 'Tgl Transaksi');
      $sheet->setCellValue("F{$startCell}", 'Uang Jalan');
      $sheet->setCellValue("G{$startCell}", 'Nominal');
      $sheet->setCellValue("H{$startCell}", 'Keterangan');
      $sheet->setCellValue("I{$startCell}", 'Pelanggan');
      $sheet->setCellValue("J{$startCell}", '');
      $sheet->setCellValue("K{$startCell}", 'Total');
      $sheet->setCellValue("L{$startCell}", 'Total Stlh Pot. Pajak');
      $sheet->setCellValue("M{$startCell}", 'Hasil Kotor');
      $sheet->setCellValue("N{$startCell}", 'Fee');
      $sheet->setCellValue("O{$startCell}", 'Sparepart');
      $sheet->setCellValue("P{$startCell}", 'Gaji Supir');
      $sheet->setCellValue("Q{$startCell}", 'Sisa Hasil');

      /* Job Order */
      $startCell++;
      $startMerge = $startCell;
      $sheet->getStyle("A{$startCell}:Q{$startCell}")->applyFromArray($borderLeftRight);
      $sheet->getStyle("A{$startCell}:Q{$startCell}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffffff');
      $sheet->getStyle("A{$startCell}")->getNumberFormat()->setFormatCode('#,##');
      $sheet->getStyle("B{$startCell}")->getAlignment()->setHorizontal('right');
      $sheet->getStyle("B{$startCell}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDD);
      $sheet->getStyle("G{$startCell}")->getNumberFormat()->setFormatCode('#,##');
      $sheet->getStyle("K{$startCell}:Q{$startCell}")->getNumberFormat()->setFormatCode('#,##');
      $sheet->getStyle("C{$startCell}")->getAlignment()->setWrapText(true)->setHorizontal('left')->setVertical('top');
      $sheet->getStyle("I{$startCell}")->getAlignment()->setWrapText(true)->setHorizontal('left')->setVertical('top');

      $sheet->setCellValue("A{$startCell}", $item['basic_price']);
      $sheet->setCellValue("B{$startCell}", $item['date_begin']);
      $sheet->setCellValue("C{$startCell}", $item['driver']['name']);
      $sheet->setCellValue("D{$startCell}", $item['payload']);
      $sheet->setCellValue("G{$startCell}", $item['road_money']);
      $sheet->setCellValue("H{$startCell}", "{$item['routefrom']['name']} - {$item['routeto']['name']}");
      $sheet->setCellValue("I{$startCell}", $item['costumer']['name']);
      $sheet->setCellValue("K{$startCell}", $item['total_basic_price']);
      $sheet->setCellValue("L{$startCell}", $item['total_basic_price_after_tax']);
      $sheet->setCellValue("M{$startCell}", ($item['total_basic_price_after_tax'] - $item['total_operational']));
      $sheet->setCellValue("N{$startCell}", $item['fee_thanks']);
      $sheet->setCellValue("O{$startCell}", $item['total_sparepart']);
      $sheet->setCellValue("P{$startCell}", $item['total_salary']);
      $sheet->setCellValue("Q{$startCell}", $item['total_clean_summary']);

      /* Rincian Uang Jalan*/
      foreach ($item['roadmoneydetail'] ?? [] as $itemOperional):
        $startCell++;
        $sheet->getStyle("A{$startCell}:Q{$startCell}")->applyFromArray($borderLeftRight);
        $sheet->getStyle("A{$startCell}:Q{$startCell}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffffff');
        $sheet->getStyle("E{$startCell}")->getAlignment()->setHorizontal('right');
        $sheet->getStyle("F{$startCell}")->getNumberFormat()->setFormatCode('#,##');
        $sheet->setCellValue("E{$startCell}", Carbon::parse($itemOperional['created_at'])->format('Y-m-d'));
        $sheet->setCellValue("F{$startCell}", $itemOperional['amount']);
        $sheet->setCellValue("H{$startCell}", $itemOperional['description']);
      endforeach;

      /* Rincian Operasional*/
      foreach ($item['operationalexpense'] ?? [] as $itemOperional):
        $startCell++;
        $sheet->getStyle("A{$startCell}:Q{$startCell}")->applyFromArray($borderLeftRight);
        $sheet->getStyle("A{$startCell}:Q{$startCell}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ffffff');
        $sheet->getStyle("E{$startCell}")->getAlignment()->setHorizontal('right');
        $sheet->getStyle("G{$startCell}")->getNumberFormat()->setFormatCode('#,##');
        $sheet->setCellValue("E{$startCell}", Carbon::parse($itemOperional['created_at'])->format('Y-m-d'));
        $sheet->setCellValue("G{$startCell}", $itemOperional['amount']);
        $sheet->setCellValue("H{$startCell}", $itemOperional['description']);
      endforeach;

      /* Merge Cell */
      $sheet->mergeCells("C{$startMerge}:C{$startCell}");
      $sheet->mergeCells("I{$startMerge}:I{$startCell}");

      /* Total */
      $startCell++;
      $sheet->getStyle("A{$startCell}:Q{$startCell}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('66d37e');
      $sheet->getStyle("A{$startCell}:Q{$startCell}")->getFont()->setBold(true);
      $sheet->getStyle("A{$startCell}:Q{$startCell}")->applyFromArray($borderLeftRight);
      $sheet->getStyle("F{$startCell}:G{$startCell}")->getNumberFormat()->setFormatCode('#,##');
      $sheet->setCellValue("E{$startCell}", "Total");
      $sheet->setCellValue("F{$startCell}", $item['roadmoneydetail_sum_amount']);
      $sheet->setCellValue("G{$startCell}", ($item['road_money'] + $item['operationalexpense_sum_amount']));
    endforeach;


    $filename = 'Laporan Rekap Operasional';
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit();
  }

  private function dataJobOrder($request)
  {
    return JobOrder::with([
      'costumer',
      'anotherexpedition',
      'cargo',
      'transport',
      'routefrom',
      'routeto',
      'driver',
      'operationalexpense',
      'roadmoneydetail',
    ])
      ->when($request->filled('driver_id'), function ($query) use ($request) {
        return $query->where('driver_id', $request['driver_id']);
      })
      ->when($request->filled('date_begin'), function ($query) use ($request) {
        return $query->whereDate('date_begin', '>=', $request['date_begin']);
      })
      ->when($request->filled('date_end'), function ($query) use ($request) {
        return $query->whereDate('date_begin', '<=', $request['date_end']);
      })
      ->where('type', 'self')
      ->where('status_cargo', 'selesai')
      ->withSum('operationalexpense', 'amount')
      ->withSum('roadmoneydetail', 'amount')
      ->orderBy('date_begin', 'asc')
      ->get();
  }
}
