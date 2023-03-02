<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Kasbon;
use App\Models\PaymentKasbon;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ReportRecapKasbonDriverController extends Controller
{
  use CarbonTrait;

  function __construct()
  {
    $this->middleware('permission:reportrecapkasbondrivers-list|reportrecapkasbondrivers-create|reportrecapkasbondrivers-edit|reportrecapkasbondrivers-delete', ['only' => ['index']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Data Rekap Kasbon Supir";
    $config['page_description'] = "Laporan Data Rekap Kasbon Supir";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Data Rekap Kasbon Supir"],
    ];
    $config['excel_url'] = 'reportrecapkasbondrivers/document?type=EXCEL';
    $config['pdf_url'] = 'reportrecapkasbondrivers/document?type=PDF';
    $config['print_url'] = 'reportrecapkasbondrivers/print';

    if ($request->ajax()) {
      $data = Kasbon::selectRaw("
      `kasbons`.*,
      `drivers`.`name` as `nama_supir`,
      `drivers`.`id` as `driver_id`
      ")
        ->leftJoin('drivers', 'drivers.id', '=', 'kasbons.driver_id');

      return DataTables::of($data)
        ->addColumn('action', function ($row) {
          return '
              <div class="dropdown">
                  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fas fa-eye"></i>
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a href="kasbon/' . $row->driver_id . '" class="dropdown-item">Detail Seluruh Kasbon</a>
                  </div>
              </div>
            ';
        })
        ->addIndexColumn()
        ->make(true);
    }

    return view('backend.report.reportrecapkasbondrivers.index', compact('config', 'page_breadcrumbs'));
  }

}
