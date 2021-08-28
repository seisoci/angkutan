<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cooperation;
use App\Models\Costumer;
use App\Models\JobOrder;
use App\Models\RoadMoney;
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
use Yajra\DataTables\Facades\DataTables;

class ReportCustomerRoadMoneyController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:reportcustomerroadmoney-list|reportcustomerroadmoney-create|reportcustomerroadmoney-edit|reportcustomerroadmoney-delete', ['only' => ['index']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Data Uang Jalan Pelanggan";
    $config['page_description'] = "Data Uang Jalan Pelanggan";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Data Uang Jalan Pelanggan"],
    ];
    $config['excel_url'] = 'reportcustomerroadmoney/document?type=EXCEL';
    $config['pdf_url'] = 'reportcustomerroadmoney/document?type=PDF';
    $config['print_url'] = 'reportcustomerroadmoney/print';


    if ($request->ajax()) {
      $data = Costumer::orderBy('name', 'asc');
      return DataTables::of($data)
        ->addColumn('details_url', function (Costumer $costumer) {
          return route('backend.reportcustomerroadmoney.datatabledetail', $costumer->id);
        })
        ->make(true);
    }

    return view('backend.report.reportcustomerroadmoney.index', compact('config', 'page_breadcrumbs'));
  }

  public function print(Request $request)
  {
    $customer = Costumer::orderBy('name', 'asc')->get();

    $data = collect($customer)->map(function ($customer) {
      $roadMoneyResult = DB::table('road_money')
        ->select(DB::raw('road_money.id, routefrom.name as routefrom, routeto.name as routeto, cargos.name as cargo, road_money.tax_pph, road_money.fee_thanks'))
        ->leftJoin('routes as routefrom', 'road_money.route_from', '=', 'routefrom.id')
        ->leftJoin('routes as routeto', 'road_money.route_to', '=', 'routeto.id')
        ->leftJoin('cargos', 'road_money.cargo_id', '=', 'cargos.id')
        ->where('road_money.costumer_id', $customer->id)
        ->orderBy('routefrom.name', 'asc')
        ->orderBy('routeto.name', 'asc')
        ->get();

      return [
        'name' => $customer->name,
        'cooperation' => $customer->cooperation,
        'phone' => $customer->phone,
        'emergency_name' => $customer->emergency_name,
        'emergency_phone' => $customer->emergency_phone,
        'address' => $customer->address,
        'roadmoney' => collect($roadMoneyResult)->map(function ($roadMoney) {
          $typeCapacity = DB::table('roadmoney_typecapacity')
            ->leftJoin('type_capacities', 'roadmoney_typecapacity.type_capacity_id', '=', 'type_capacities.id')
            ->where('road_money_id', $roadMoney->id)
            ->get();
          return [
            'routefrom' => $roadMoney->routefrom,
            'routeto' => $roadMoney->routeto,
            'cargo' => $roadMoney->cargo,
            'tax_pph' => $roadMoney->tax_pph,
            'fee_thanks' => $roadMoney->fee_thanks,
            'typecapacities' => $typeCapacity
          ];
        }),
      ];
    });

    $config['page_title'] = "Laporan Data Uang Jalan Pelanggan";
    $config['page_description'] = "Laporan Data Uang Jalan Pelanggan";
    $config['current_time'] = $this->dateTimeNow();
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Invoice Ldo"],
    ];

    $cooperationDefault = Cooperation::where('default', '1')->first();

    return view('backend.report.reportcustomerroadmoney.print', compact('config', 'page_breadcrumbs', 'cooperationDefault', 'data'));
  }

  public function document(Request $request)
  {
    $cooperationDefault = Cooperation::where('default', '1')->first();

    $type = $request->type;
    $status_payment = $request->status_payment;
    $customer = Costumer::orderBy('name', 'asc')->get();

    $data = collect($customer)->map(function ($customer) {
      $roadMoneyResult = DB::table('road_money')
        ->select(DB::raw('road_money.id, routefrom.name as routefrom, routeto.name as routeto, cargos.name as cargo, road_money.tax_pph, road_money.fee_thanks'))
        ->leftJoin('routes as routefrom', 'road_money.route_from', '=', 'routefrom.id')
        ->leftJoin('routes as routeto', 'road_money.route_to', '=', 'routeto.id')
        ->leftJoin('cargos', 'road_money.cargo_id', '=', 'cargos.id')
        ->where('road_money.costumer_id', $customer->id)
        ->orderBy('routefrom.name', 'asc')
        ->orderBy('routeto.name', 'asc')
        ->get();

      return [
        'name' => $customer->name,
        'cooperation' => $customer->cooperation,
        'phone' => $customer->phone,
        'emergency_name' => $customer->emergency_name,
        'emergency_phone' => $customer->emergency_phone,
        'address' => $customer->address,
        'roadmoney' => collect($roadMoneyResult)->map(function ($roadMoney) {
          $typeCapacity = DB::table('roadmoney_typecapacity')
            ->leftJoin('type_capacities', 'roadmoney_typecapacity.type_capacity_id', '=', 'type_capacities.id')
            ->where('road_money_id', $roadMoney->id)
            ->get();
          return [
            'routefrom' => $roadMoney->routefrom,
            'routeto' => $roadMoney->routeto,
            'cargo' => $roadMoney->cargo,
            'tax_pph' => $roadMoney->tax_pph,
            'fee_thanks' => $roadMoney->fee_thanks,
            'typecapacities' => $typeCapacity
          ];
        }),
      ];
    });

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
    $sheet->setCellValue('A1', 'Laporan Data Uang Jalan Pelanggan');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A2', 'Printed: ' . $this->dateTimeNow());
//    $sheet->mergeCells('A3:C3');
//    $sheet->setCellValue('A3', 'Priode: ' . (!empty($date) ? $date : 'All Date'));
//    $sheet->mergeCells('A4:C4');
//    $sheet->setCellValue('A4', 'Status Pembayaran: ' . (!empty($status_pembayaran) ? ucwords($status_pembayaran) : 'All'));
//    $sheet->mergeCells('H1:J1');
//    $sheet->setCellValue('H1', $cooperationDefault['nickname']);
//    $sheet->mergeCells('H2:J2');
//    $sheet->setCellValue('H2', $cooperationDefault['address']);
//    $sheet->mergeCells('H3:J3');
//    $sheet->setCellValue('H3', 'Telp: ' . $cooperationDefault['phone']);
//    $sheet->mergeCells('H4:J4');
//    $sheet->setCellValue('H4', 'Fax: ' . $cooperationDefault['fax']);

    $sheet->getColumnDimension('A')->setWidth(3.55);
    $sheet->getColumnDimension('B')->setWidth(20);
    $sheet->getColumnDimension('C')->setWidth(20);
    $sheet->getColumnDimension('D')->setWidth(15);
    $sheet->getColumnDimension('E')->setWidth(15);
    $sheet->getColumnDimension('F')->setWidth(16.82);
    $sheet->getColumnDimension('G')->setWidth(16.82);

    $startCell = 2;

    foreach ($data as $item):
      $startCell++;
      $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
      $sheet->getStyle('A' . $startCell . ':F' . $startCell)->applyFromArray($borderTop);
      $sheet->getStyle('F' . $startCell)->applyFromArray($borderRight);
      $sheet->getStyle('A' . $startCell)->getAlignment()->setHorizontal('left');
      $sheet->mergeCells('A' . $startCell . ':B' . $startCell);
      $sheet->setCellValue('A' . $startCell, "Nama Pelanggan");
      $sheet->setCellValue('C' . $startCell, ': ' . $item['name']);
      $sheet->setCellValue('E' . $startCell, "Kerjasama");
      $sheet->setCellValue('F' . $startCell, ': ' . ucfirst($item['cooperation']));
      $startCell++;
      $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
      $sheet->getStyle('F' . $startCell)->applyFromArray($borderRight);
      $sheet->getStyle('A' . $startCell)->getAlignment()->setHorizontal('left');
      $sheet->mergeCells('A' . $startCell . ':B' . $startCell);
      $sheet->setCellValue('A' . $startCell, "No. Telp");
      $sheet->setCellValue('C' . $startCell, ': ' . $item['phone']);
      $sheet->setCellValue('E' . $startCell, "Nama Darurat");
      $sheet->setCellValue('F' . $startCell, ': ' . $item['emergency_name']);
      $startCell++;
      $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
      $sheet->getStyle('F' . $startCell)->applyFromArray($borderRight);
      $sheet->getStyle('A' . $startCell)->getAlignment()->setHorizontal('left');
      $sheet->mergeCells('A' . $startCell . ':B' . $startCell);
      $sheet->setCellValue('A' . $startCell, "Alamat");
      $sheet->setCellValue('C' . $startCell, ': ' . $item['address']);
      $sheet->setCellValue('E' . $startCell, "No. Telp Darurat");
      $sheet->setCellValue('F' . $startCell, ': ' . $item['emergency_phone']);

      $no = 1;
      $startCell++;
      $sheet->getStyle('A' . $startCell)->getAlignment()->setHorizontal('center');
      $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
      $sheet->getStyle('F' . $startCell)->applyFromArray($borderRight);
      $sheet->setCellValue('A' . $startCell, '#');
      $sheet->setCellValue('B' . $startCell, 'Rute Dari');
      $sheet->setCellValue('C' . $startCell, 'Rute Ke');
      $sheet->setCellValue('D' . $startCell, 'Muatan');
      $sheet->setCellValue('E' . $startCell, 'Tax PPH');
      $sheet->setCellValue('F' . $startCell, 'Fee Thanks');
      $startCell++;
      foreach ($item['roadmoney'] as $child):
        $sheet->getStyle('A' . $startCell)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
        $sheet->getStyle('F' . $startCell)->applyFromArray($borderRight);
        $sheet->getStyle('F')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->setCellValue('A' . $startCell, $no++);
        $sheet->setCellValue('B' . $startCell, $child['routefrom']);
        $sheet->setCellValue('C' . $startCell, $child['routeto']);
        $sheet->setCellValue('D' . $startCell, $child['cargo']);
        $sheet->setCellValue('E' . $startCell, ($child['tax_pph'] ?? 0) . ' %');
        $sheet->setCellValue('F' . $startCell, ($child['fee_thanks'] ?? 0));
        $startCell++;
        $sheet->getStyle('A' . $startCell)->getAlignment()->setHorizontal('right');
        $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
        $sheet->getStyle('F' . $startCell)->applyFromArray($borderRight);
        $sheet->setCellValue('A' . $startCell, '*');
        $sheet->setCellValue('B' . $startCell, 'Uang Jalan Engkel');
        $sheet->setCellValue('C' . $startCell, 'Uang Jalan Tronton');
        $sheet->setCellValue('D' . $startCell, 'Ongkosan');
        $sheet->setCellValue('E' . $startCell, 'Jenis Muatan');
        $sheet->setCellValue('F' . $startCell, 'Tipe');
        $startCell++;
        foreach ($child['typecapacities'] as $typeCapacities):
          $sheet->getStyle('A' . $startCell)->getAlignment()->setHorizontal('right');
          $sheet->getStyle('B' . $startCell . ':D' . $startCell)->getAlignment()->setHorizontal('left');
          $sheet->getStyle('A' . $startCell)->applyFromArray($borderLeft);
          $sheet->getStyle('F' . $startCell)->applyFromArray($borderRight);
          $sheet->getStyle('B' . $startCell . ':' . 'E' . $startCell)->getNumberFormat()->setFormatCode('#,##0.00');
          $sheet->setCellValue('A' . $startCell, "•");
          $sheet->setCellValue('B' . $startCell, ($typeCapacities->road_engkel ?? 0));
          $sheet->setCellValue('C' . $startCell, ($typeCapacities->road_tronton ?? 0));
          $sheet->setCellValue('D' . $startCell, ($typeCapacities->expense ?? 0));
          $sheet->setCellValue('E' . $startCell, $typeCapacities->name);
          $sheet->setCellValue('F' . $startCell, ($typeCapacities->type == 'fix' ? "Fix" : "Kalkulasi"));
          $startCell++;
        endforeach;
      endforeach;
      $sheet->getStyle('A' . $startCell . ':F' . $startCell)->applyFromArray($borderTop);
    endforeach;
    $filename = 'Laporan Data Uang Jalan Pelanggan ' . $this->dateTimeNow();
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
    $data = DB::table('road_money')
      ->select(DB::raw('road_money.id, routefrom.name as routefrom, routeto.name as routeto, cargos.name as cargo, road_money.tax_pph, road_money.fee_thanks'))
      ->leftJoin('routes as routefrom', 'road_money.route_from', '=', 'routefrom.id')
      ->leftJoin('routes as routeto', 'road_money.route_to', '=', 'routeto.id')
      ->leftJoin('cargos', 'road_money.cargo_id', '=', 'cargos.id')
      ->where('road_money.costumer_id', $id)
      ->orderBy('routefrom.name', 'asc')
      ->orderBy('routeto.name', 'asc');

    return Datatables::of($data)
      ->addColumn('action', function ($row) {
        $actionBtn = '
            <a href="#" data-toggle="modal" data-target="#modalDetail" data-id="' . $row->id . '" class="btn btn-primary btn-sm">Lihat Detail</a>';
        return $actionBtn;
      })
      ->make(true);
  }

  public function findbypk($id)
  {
    $roadMoney = DB::table('roadmoney_typecapacity')
      ->leftJoin('type_capacities', 'roadmoney_typecapacity.type_capacity_id', '=', 'type_capacities.id')
      ->where('road_money_id', $id)
      ->get();

    $data = collect($roadMoney)->map(function ($data) {
      return [
        'type_capacity' => $data->name,
        'road_engkel' => number_format(($data->road_engkel ?? 0), 2, '.', ','),
        'road_tronton' => number_format(($data->road_tronton ?? 0), 2, '.', ','),
        'type' => $data->type,
        'expense' => number_format(($data->expense ?? 0), 2, '.', ','),
      ];
    });

    $response = response()->json([
      'status' => 'success',
      'message' => $data ?? [],
    ]);

    return $response;
  }


}
