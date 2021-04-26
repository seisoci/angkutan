<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\JobOrder;
use App\Models\Setting;
use App\Models\Transport;
use Illuminate\Http\Request;
use Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class RecapitulationController extends Controller
{
    public function index(Request $request)
    {
      $validator = Validator::make($request->all(), [
        'transport_id'  => 'integer|nullable',
        'driver_id'     => 'integer|nullable',
        'date_begin'    => 'date_format:Y-m-d',
        'date_end'      => 'date_format:Y-m-d',
      ]);

      $config['page_title']       = "Laporan Rekapitulasi";
      $config['page_description'] = "Laporan Rekapitulasi";
      $page_breadcrumbs = [
        ['page' => '#','title' => "Laporan Rekapitulasi"],
      ];
      $collection = Setting::all();
      $profile = collect($collection)->mapWithKeys(function ($item) {
        return [$item['name'] => $item['value']];
      });
      $data         = array();
      $transport    = NULL;
      $driver       = NULL;
      if($validator->passes()){
        $driver_id    = $request->driver_id;
        $transport_id = $request->transport_id;
        $date_begin   = $request->date_begin;
        $date_end     = $request->date_end;

        if($request->all() != NULL){
          $transport = isset($transport_id) || !empty($transport_id) ? Transport::findOrFail($transport_id) : 'Semua Mobil';
          $driver = isset($driver_id) || !empty($driver_id) ? Driver::select('id', 'name')->findOrFail($driver_id) : 'Semua Supir';
          $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name', 'operationalexpense.expense'])
          ->withSum('operationalexpense','amount')
          ->where('type', 'self')
          ->when($driver_id, function ($query, $driver_id) {
            return isset($driver_id) ? $query->where('driver_id', $driver_id) : NULL;
          })
          ->when($transport_id, function ($query, $transport_id) {
            return isset($transport_id) ? $query->where('transport_id', $transport_id) : NULL;
          })
          ->whereBetween('date_begin', [$date_begin, $date_end])
          ->get();
        }
      }else{
        return redirect()->back()->withErrors($validator->errors());
      }

      return view('backend.operational.recapitulation.index', compact('config', 'page_breadcrumbs', 'data', 'profile', 'date_begin', 'date_end', 'transport_id', 'driver_id', 'driver', 'transport'));
    }

    public function excel(Request $request)
    {
      $validator = Validator::make($request->all(), [
        'transport_id'  => 'integer|required',
        'driver_id'     => 'integer|nullable',
        'date_begin'    => 'date_format:Y-m-d|required',
        'date_end'      => 'date_format:Y-m-d|required',
      ]);
      if($validator->passes()){
        $driver_id    = $request->driver_id;
        $transport_id = $request->transport_id;
        $date_begin   = $request->date_begin;
        $date_end     = $request->date_end;

        $collection = Setting::all();
        $profile = collect($collection)->mapWithKeys(function ($item) {
          return [$item['name'] => $item['value']];
        });
        $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name', 'operationalexpense.expense'])
        ->withSum('operationalexpense','amount')
        ->where('type', 'self')
        ->when($driver_id, function ($query, $driver_id) {
          return isset($driver_id) ? $query->where('driver_id', $driver_id) : NULL;
        })
        ->when($transport_id, function ($query, $transport_id) {
          return isset($transport_id) ? $query->where('transport_id', $transport_id) : NULL;
        })
        ->whereBetween('date_begin', [$date_begin, $date_end])
        ->get();
        $transport = isset($transport_id) || !empty($transport_id) ? Transport::findOrFail($transport_id) : 'Semua Mobil';
        $driver = isset($driver_id) || !empty($driver_id) ? Driver::select('id', 'name')->findOrFail($driver_id) : 'Semua Supir';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_FOLIO)->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

        $sheet->getColumnDimension('A')->setWidth(3.55);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(34);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(14);
        $sheet->getColumnDimension('I')->setWidth(8);
        $sheet->getColumnDimension('J')->setWidth(14);

        $sheet->setCellValue('A1', 'Laporan Pendapatan Mobil');
        $sheet->setCellValue('A2', 'No. Polisi:');
        $sheet->setCellValue('B2', $transport->num_pol);
        $sheet->setCellValue('J1', $profile['name']);
        $sheet->setCellValue('J2', $profile['address']);
        $sheet->setCellValue('J3', $profile['telp']);
        $sheet->setCellValue('J4', $profile['fax']);

        $sheet->setCellValue('A6', 'No.');
        $sheet->setCellValue('B6', 'Tanggal');
        $sheet->setCellValue('C6', 'S. Jalan');
        $sheet->setCellValue('D6', 'Pelanggan');
        $sheet->setCellValue('E6', 'Dari');
        $sheet->setCellValue('F6', 'Tujuan');
        $sheet->setCellValue('G6', 'Jenis Barang');
        $sheet->setCellValue('H6', 'Tarif(Rp.)');
        $sheet->setCellValue('I6', 'Qty(Unit)');
        $sheet->setCellValue('J6', 'Total(Rp.)');

        $startCell = 7;
        $no = 0;
        foreach($data as $item):
          $sheet->getStyle('H'.$startCell)->getNumberFormat()->setFormatCode('#,##0.00');
          $sheet->getStyle('I'.$startCell)->getNumberFormat()->setFormatCode('0.00');
          $sheet->getStyle('J'.$startCell)->getNumberFormat()->setFormatCode('#,##0.00');
          $sheet->setCellValue('A'.$startCell, $no++);
          $sheet->setCellValue('B'.$startCell, $item->date_begin);
          $sheet->setCellValue('C'.$startCell, $item->prefix.'-'.$item->num_bill);
          $sheet->setCellValue('D'.$startCell, $item->costumer->name);
          $sheet->setCellValue('E'.$startCell, $item->routefrom->name);
          $sheet->setCellValue('F'.$startCell, $item->routeto->name);
          $sheet->setCellValue('G'.$startCell, $item->cargo->name);
          $sheet->setCellValue('H'.$startCell, $item->basic_price);
          $sheet->setCellValue('I'.$startCell, $item->payload);
          $sheet->setCellValue('J'.$startCell, $item->total_basic_price);
          $startCell++;
        endforeach;


        $filename = 'lol';
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
      }else{
        return abort(404, 'Data Tidak ditemukan');
      }
    }
}
