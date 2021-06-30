<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\InvoiceLdo;
use App\Models\Setting;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportProfitLossController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:profitloss-list|profitloss-create|profitloss-edit|profitloss-delete', ['only' => ['index']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Laba Rugi";
    $config['page_description'] = "Laporan Laba Rugi";
    $config['excel_url'] = 'profitloss/document?type=EXCEL';
    $config['pdf_url'] = 'profitloss/document?type=PDF';
    $config['print_url'] = 'profitloss/print';

    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Laba Rugi"],
    ];

    $date = $request->input('date_begin') ?? NULL;
    $date_begin = $request->input('date_begin') ? $this->getStartOfMonthByMonthYear($request->input('date_begin')) : NULL;
    $date_end = $request->input('date_begin') ? $this->getEndOfMonthByMonthYear($request->input('date_begin')) : NULL;

    $pendapatan = DB::table('coas')
      ->selectRaw('coas.name, IFNULL(jur.saldo, 0) as saldo')
      ->leftJoin(DB::raw("(SELECT `coas`.`id`, `coas`.`name`,
       IF(`coas`.`normal_balance` = 'Kr', (SUM(IFNULL(`journals`.`kredit`, 0)) - SUM(IFNULL(`journals`.`debit`, 0))),
       (-1 * (SUM(IFNULL(`journals`.`debit`, 0)) - SUM(IFNULL(`journals`.`kredit`, 0))))) AS `saldo`
       FROM `journals`
       LEFT JOIN `coas` ON `journals`.`coa_id` = `coas`.`id`
       WHERE (`date_journal` BETWEEN '" . $date_begin . "' AND '" . $date_end . "')
       AND (`coas`.`type` = 'pendapatan')
       GROUP BY `coa_id`) `jur`"), 'jur.id', '=', 'coas.id')
      ->where('coas.type', '=', 'pendapatan')
      ->whereNotNull('parent_id')
      ->orderBy('coas.code')
      ->get();

    $beban = DB::table('coas')
      ->selectRaw('coas.name, IFNULL(jur.saldo, 0) as saldo')
      ->leftJoin(DB::raw("(SELECT `coas`.`id`, `coas`.`name`,
       IF(`coas`.`normal_balance` = 'Db', (SUM(IFNULL(`journals`.`debit`, 0)) - SUM(IFNULL(`journals`.`kredit`, 0))),
       (-1 * (SUM(IFNULL(`journals`.`kredit`, 0)) - SUM(IFNULL(`journals`.`debit`, 0))))) AS `saldo`
       FROM `journals`
       LEFT JOIN `coas` ON `journals`.`coa_id` = `coas`.`id`
       WHERE (`date_journal` BETWEEN '" . $date_begin . "' AND '" . $date_end . "')
       AND (`coas`.`type` = 'beban')
       GROUP BY `coa_id`) `jur`"), 'jur.id', '=', 'coas.id')
      ->where('coas.type', '=', 'beban')
      ->whereNotNull('parent_id')
      ->orderBy('coas.code')
      ->get();

    return view('backend.report.reportprofitloss.index', compact('config', 'page_breadcrumbs', 'pendapatan', 'beban', 'date'));
  }

  public function document(Request $request)
  {
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });

    $type = $request->type;
    $date = $request->input('date_begin') ?? NULL;
    $date_begin = $request->input('date_begin') ? $this->getStartOfMonthByMonthYear($request->input('date_begin')) : NULL;
    $date_end = $request->input('date_begin') ? $this->getEndOfMonthByMonthYear($request->input('date_begin')) : NULL;

    $pendapatan = DB::table('coas')
      ->selectRaw('coas.name, IFNULL(jur.saldo, 0) as saldo')
      ->leftJoin(DB::raw("(SELECT `coas`.`id`, `coas`.`name`,
       IF(`coas`.`normal_balance` = 'Kr', (SUM(IFNULL(`journals`.`kredit`, 0)) - SUM(IFNULL(`journals`.`debit`, 0))),
       (-1 * (SUM(IFNULL(`journals`.`debit`, 0)) - SUM(IFNULL(`journals`.`kredit`, 0))))) AS `saldo`
       FROM `journals`
       LEFT JOIN `coas` ON `journals`.`coa_id` = `coas`.`id`
       WHERE (`date_journal` BETWEEN '" . $date_begin . "' AND '" . $date_end . "')
       AND (`coas`.`type` = 'pendapatan')
       GROUP BY `coa_id`) `jur`"), 'jur.id', '=', 'coas.id')
      ->where('coas.type', '=', 'pendapatan')
      ->whereNotNull('parent_id')
      ->orderBy('coas.code')
      ->get();

    $beban = DB::table('coas')
      ->selectRaw('coas.name, IFNULL(jur.saldo, 0) as saldo')
      ->leftJoin(DB::raw("(SELECT `coas`.`id`, `coas`.`name`,
       IF(`coas`.`normal_balance` = 'Db', (SUM(IFNULL(`journals`.`debit`, 0)) - SUM(IFNULL(`journals`.`kredit`, 0))),
       (-1 * (SUM(IFNULL(`journals`.`kredit`, 0)) - SUM(IFNULL(`journals`.`debit`, 0))))) AS `saldo`
       FROM `journals`
       LEFT JOIN `coas` ON `journals`.`coa_id` = `coas`.`id`
       WHERE (`date_journal` BETWEEN '" . $date_begin . "' AND '" . $date_end . "')
       AND (`coas`.`type` = 'beban')
       GROUP BY `coa_id`) `jur`"), 'jur.id', '=', 'coas.id')
      ->where('coas.type', '=', 'beban')
      ->whereNotNull('parent_id')
      ->orderBy('coas.code')
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

    $sheet->mergeCells('A1:B1');
    $sheet->setCellValue('A1', $profile['name']);
    $sheet->mergeCells('A2:B2');
    $sheet->setCellValue('A2', $profile['address']);
    $sheet->mergeCells('A3:B3');
    $sheet->setCellValue('A3', 'Telp: ' . $profile['telp']);
    $sheet->mergeCells('A4:B4');
    $sheet->setCellValue('A4', 'Fax: ' . $profile['fax']);

    $sheet->getColumnDimension('A')->setWidth(50);
    $sheet->getColumnDimension('B')->setWidth(20);

    $startCell = 6;

    $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
    $sheet->getStyle('B' . $startCell)->applyFromArray($borderRight);
    $sheet->getStyle('A' . $startCell . ':B' . $startCell)->applyFromArray($borderTop);
    $sheet->getStyle('A' . $startCell)->getAlignment()->setHorizontal('center');
    $sheet->mergeCells('A' . $startCell . ':B' . $startCell);
    $sheet->setCellValue('A' . $startCell, 'Laporan Laba Rugi');
    $startCell++;

    $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
    $sheet->getStyle('B' . $startCell)->applyFromArray($borderRight);
    $sheet->getStyle('A' . $startCell)->getAlignment()->setHorizontal('center');
    $sheet->mergeCells('A' . $startCell . ':B' . $startCell);
    $sheet->setCellValue('A' . $startCell, 'Priode ' . $request->input('date_begin'));
    $startCell++;
    $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
    $sheet->getStyle('B' . $startCell)->applyFromArray($borderRight);

    foreach ($pendapatan as $item):
      $startCell++;
      $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
      $sheet->getStyle('B' . $startCell)->applyFromArray($borderRight);
      $sheet->getStyle('A' . $startCell)->getAlignment()->setHorizontal('left');
      $sheet->getStyle('B' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->setCellValue('A' . $startCell, $item->name);
      $sheet->setCellValue('B' . $startCell, $item->saldo);
    endforeach;
    $startCell++;
    $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
    $sheet->getStyle('B' . $startCell)->applyFromArray($borderRight);
    $sheet->setCellValue('A' . $startCell, 'Total Pendapatan');
    $sheet->getStyle('B' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->setCellValue('B' . $startCell, $pendapatan->sum('saldo'));
    $startCell++;
    $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
    $sheet->getStyle('B' . $startCell)->applyFromArray($borderRight);

    foreach ($beban as $item):
      $startCell++;
      $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
      $sheet->getStyle('B' . $startCell)->applyFromArray($borderRight);
      $sheet->getStyle('A' . $startCell)->getAlignment()->setHorizontal('left');
      $sheet->getStyle('B' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
      $sheet->setCellValue('A' . $startCell, $item->name);
      $sheet->setCellValue('B' . $startCell, $item->saldo);
    endforeach;
    $startCell++;
    $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
    $sheet->getStyle('B' . $startCell)->applyFromArray($borderRight);
    $sheet->setCellValue('A' . $startCell, 'Total Beban');
    $sheet->getStyle('B' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->setCellValue('B' . $startCell, $beban->sum('saldo'));
    $startCell++;
    $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
    $sheet->getStyle('B' . $startCell)->applyFromArray($borderRight);
    $startCell++;
    $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
    $sheet->getStyle('B' . $startCell)->applyFromArray($borderRight);
    $sheet->setCellValue('A' . $startCell, 'Pendapatan Bersih');
    $sheet->getStyle('B' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
    $sheet->setCellValue('B' . $startCell, $pendapatan->sum('saldo') - $beban->sum('saldo'));
    $sheet->getStyle('A' . $startCell . ':B' . $startCell)->applyFromArray($borderBottom);

    $filename = 'Laporan Laba Rugi ' . $this->dateTimeNow();
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


}
