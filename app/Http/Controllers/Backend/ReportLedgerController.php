<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\Setting;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportLedgerController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:ledger-list|ledger-create|ledger-edit|ledger-delete', ['only' => ['index']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Buku Besar";
    $config['page_description'] = "Laporan Buku Besar";
    $config['excel_url'] = 'ledger/document?type=EXCEL';
    $config['pdf_url'] = 'ledger/document?type=PDF';
    $config['print_url'] = 'ledger/print';
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Buku Besar"],
    ];
    $date = $request->input('date_begin') ?? NULL;
    $date_begin = $request->input('date_begin') ? $this->getStartOfMonthByMonthYear($request->input('date_begin')) : NULL;
    $date_end = $request->input('date_begin') ? $this->getEndOfMonthByMonthYear($request->input('date_begin')) : NULL;
    $restSaldo = DB::table('coas')
      ->select(DB::raw('
      `coas`.id, IF(`coas`.`normal_balance` = "Db", (SUM(`journals`.`debit`) - SUM(`journals`.`kredit`)), (SUM(`journals`.`kredit`) - SUM(`journals`.`debit`))) AS `saldo`
      '))
      ->leftJoin('journals', 'coas.id', '=', 'journals.coa_id')
      ->groupBy('coas.id')
      ->when($request->input('date_begin'), function ($query) use ($date_begin, $date_end) {
        return $query->where('journals.date_journal', '<', $date_begin);
      }, function ($query) {
        return $query->whereNull('journals.date_journal');
      })
      ->where('coas.type', 'harta')
      ->where('coas.normal_balance', 'Db')
      ->get();

    $data = Coa::with(['children.journal' => function ($query) use ($request, $date_begin, $date_end) {
      $query->when($request->input('date_begin'), function ($query) use ($date_begin, $date_end) {
        return $query->whereBetween('journals.date_journal', [$date_begin, $date_end]);
      }, function ($query) {
        return $query->whereNull('journals.date_journal');
      });
    }])
      ->whereNull('parent_id')
      ->orderBy('code', 'asc')
      ->get();

    foreach ($restSaldo as $rest):
      foreach ($data as $keyParent => $itemParent):
        foreach ($itemParent->children as $key => $itemChilderen):
          if ($rest->id == $itemChilderen->id) {
            $data[$keyParent]['children'][$key]['rest_balance'] = $rest->saldo;
            break;
          }
        endforeach;
      endforeach;
    endforeach;

    return view('backend.report.reportledger.index', compact('config', 'page_breadcrumbs', 'data', 'date'));
  }

  public function document(Request $request)
  {
    $collection = Setting::all();
    $profile = collect($collection)->mapWithKeys(function ($item) {
      return [$item['name'] => $item['value']];
    });

    $type = $request->input('type');
    $date = $request->input('date_begin') ?? NULL;
    $date_begin = $request->input('date_begin') ? $this->getStartOfMonthByMonthYear($request->input('date_begin')) : NULL;
    $date_end = $request->input('date_begin') ? $this->getEndOfMonthByMonthYear($request->input('date_begin')) : NULL;
    $restSaldo = DB::table('coas')
      ->select(DB::raw('
      `coas`.id, IF(`coas`.`normal_balance` = "Db", (SUM(`journals`.`debit`) - SUM(`journals`.`kredit`)), (SUM(`journals`.`kredit`) - SUM(`journals`.`debit`))) AS `saldo`
      '))
      ->leftJoin('journals', 'coas.id', '=', 'journals.coa_id')
      ->groupBy('coas.id')
      ->when($request->input('date_begin'), function ($query) use ($date_begin, $date_end) {
        return $query->where('journals.date_journal', '<', $date_begin);
      }, function ($query) {
        return $query->whereNull('journals.date_journal');
      })
      ->where('coas.type', 'harta')
      ->where('coas.normal_balance', 'Db')
      ->get();

    $data = Coa::with(['children.journal' => function ($query) use ($request, $date_begin, $date_end) {
      $query->when($request->input('date_begin'), function ($query) use ($date_begin, $date_end) {
        return $query->whereBetween('journals.date_journal', [$date_begin, $date_end]);
      }, function ($query) {
        return $query->whereNull('journals.date_journal');
      });
    }])
      ->whereNull('parent_id')
      ->orderBy('code', 'asc')
      ->get();

    foreach ($restSaldo as $rest):
      foreach ($data as $keyParent => $itemParent):
        foreach ($itemParent->children as $key => $itemChilderen):
          if ($rest->id == $itemChilderen->id) {
            $data[$keyParent]['children'][$key]['rest_balance'] = $rest->saldo;
            break;
          }
        endforeach;
      endforeach;
    endforeach;

    $spreadsheet = new Spreadsheet();
    $spreadsheet->getDefaultStyle()->getFont()->setSize(8);
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
    $sheet->setCellValue('A1', 'Laporan Buku Besar');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
    $sheet->mergeCells('A3:C3');
    $sheet->setCellValue('A3', 'Priode: ' . (!empty($date) ? $date : 'None'));
    $sheet->mergeCells('D1:F1');
    $sheet->setCellValue('D1', $profile['name']);
    $sheet->mergeCells('D2:F2');
    $sheet->setCellValue('D2', $profile['address']);
    $sheet->mergeCells('D3:F3');
    $sheet->setCellValue('D3', 'Telp: ' . $profile['telp']);
    $sheet->mergeCells('D4:F4');
    $sheet->setCellValue('D4', 'Fax: ' . $profile['fax']);

    $sheet->getColumnDimension('A')->setWidth(10);
    $sheet->getColumnDimension('B')->setWidth(45);
    $sheet->getColumnDimension('C')->setWidth(17);
    $sheet->getColumnDimension('D')->setWidth(17);
    $sheet->getColumnDimension('E')->setWidth(17);
    $sheet->getColumnDimension('F')->setWidth(17);


    $startCell = 6;
    $merge = 6;
    foreach ($data as $keyCollection => $itemCollection):
      foreach ($itemCollection->children as $itemParent):
        $saldo = 0;
        $saldo += $itemParent->rest_balance ?? 0;

        $startCell++;
        $merge++;
        $sheet->mergeCells('A' . $startCell . ':B' . $startCell);
        $sheet->setCellValue('A' . $startCell, 'Kode Akun : ' . $itemParent->code);

        $startCell++;
        $merge++;
        $sheet->mergeCells('A' . $startCell . ':B' . $startCell);
        $sheet->setCellValue('A' . $startCell, 'Nama Akun : ' . $itemParent->name);
        $sheet->getStyle('A' . $startCell . ':F' . $startCell)->applyFromArray($borderBottom);

        $startCell++;
        $merge += 2;
        $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
        $sheet->getStyle('F' . $startCell)->applyFromArray($borderRight);
        $sheet->getStyle('A' . $startCell . ':F' . $startCell)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A' . $startCell . ':D' . $startCell)->getAlignment()->setVertical('distributed');
        $sheet->mergeCells('E' . $startCell . ':F' . $startCell);
        $sheet->mergeCells('A' . $startCell . ':A' . $merge);
        $sheet->mergeCells('B' . $startCell . ':B' . $merge);
        $sheet->mergeCells('C' . $startCell . ':C' . $merge);
        $sheet->mergeCells('D' . $startCell . ':D' . $merge);
        $sheet->setCellValue('A' . $startCell, 'Tanggal');
        $sheet->setCellValue('B' . $startCell, 'Keterangan');
        $sheet->setCellValue('C' . $startCell, 'Debit');
        $sheet->setCellValue('D' . $startCell, 'Kredit');
        $sheet->setCellValue('E' . $startCell, 'Saldo');

        $startCell++;
        $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
        $sheet->getStyle('F' . $startCell)->applyFromArray($borderRight);
        $sheet->getStyle('A' . $startCell . ':F' . $startCell)->applyFromArray($borderBottom);
        $sheet->getStyle('E' . $startCell . ':F' . $startCell)->getAlignment()->setHorizontal('center');
        $sheet->setCellValue('E' . $startCell, 'Debit');
        $sheet->setCellValue('F' . $startCell, 'Kredit');

        if ($itemParent->rest_balance) {
          $startCell++;
          $merge++;
          $sheet->getStyle('C' . $startCell . ':F' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
          $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
          $sheet->getStyle('F' . $startCell)->applyFromArray($borderRight);
          $sheet->setCellValue('A' . $startCell, '-');
          $sheet->setCellValue('B' . $startCell, 'Sisa saldo bulan sebelumnya');
          $sheet->setCellValue('C' . $startCell, ($itemParent->rest_balance >= 0 ? $itemParent->rest_balance : NULL));
          $sheet->setCellValue('D' . $startCell, ($itemParent->rest_balance < 0 ? $itemParent->rest_balance : NULL));
          $sheet->setCellValue('E' . $startCell, ($itemParent->rest_balance >= 0 ? $saldo : NULL));
          $sheet->setCellValue('F' . $startCell, ($itemParent->rest_balance < 0 ? $saldo : NULL));
        } else {
          foreach ($itemParent->journal as $itemChildren):
            $startCell++;
            $merge++;
            if ($itemParent->normal_balance == 'Db') {
              if ($itemChildren->debit != 0) {
                $saldo += $itemChildren->debit;
              } else {
                $saldo -= $itemChildren->kredit;
              }
            } else {
              if ($itemChildren->debit != 0) {
                $saldo -= $itemChildren->debit;
              } else {
                $saldo += $itemChildren->kredit;
              }
            }
            $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
            $sheet->getStyle('F' . $startCell)->applyFromArray($borderRight);
            $sheet->getStyle('C' . $startCell . ':F' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->setCellValue('A' . $startCell, $itemChildren->date);
            $sheet->setCellValue('B' . $startCell, $itemChildren->description);
            $sheet->setCellValue('C' . $startCell, ($itemChildren->debit != 0 ? $itemChildren->debit : NULL));
            $sheet->setCellValue('D' . $startCell, ($itemChildren->kredit != 0 ? $itemChildren->kredit : NULL));
            $sheet->setCellValue('E' . $startCell, ($itemParent->normal_balance == 'Db' && $saldo >= 0 ? $saldo : NULL));
            $sheet->setCellValue('F' . $startCell, ($itemParent->normal_balance == 'Kr' && $saldo >= 0 ? $saldo : NULL));
          endforeach;
          $sheet->getStyle('A' . $startCell . ':F' . $startCell)->applyFromArray($borderBottom);
        }
        $startCell += 2;
        $merge += 2;
      endforeach;
    endforeach;
    $filename = 'Laporan Buku Besar ' . $this->dateTimeNow();
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
