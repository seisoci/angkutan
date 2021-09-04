<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\InvoiceCostumer;
use App\Models\InvoiceLdo;
use App\Models\InvoicePurchase;
use App\Models\JobOrder;
use App\Models\Sparepart;
use App\Models\Transport;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
  public function __invoke()
  {

    $config['page_title'] = "Dashboard";
    $config['page_description'] = "Dashboard";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Dashboard"],
    ];
    $expired = Carbon::now()->setTimezone('Asia/Jakarta')->addDays(30)->format('Y-m-d');
    $expired7D = Carbon::now()->setTimezone('Asia/Jakarta')->addDays(7)->format('Y-m-d');
    $now = Carbon::now()->setTimezone('Asia/Jakarta')->format('Y-m-d');

    $expired_sim = Driver::select('name', DB::raw('DATEDIFF(expired_sim, "' . $now . '") AS `diff_days`'))->where('another_expedition_id', NULL)->where('expired_sim', '<', $expired)->get();

    $expired_kir = Transport::select('num_pol', DB::raw('DATEDIFF(expired_kir, "' . $now . '") AS `diff_days`'))->where('another_expedition_id', NULL)->where('expired_kir', '<', $expired)->get();

    $expired_stnk = Transport::select('num_pol', DB::raw('DATEDIFF(expired_stnk, "' . $now . '") AS `diff_days`'))->where('another_expedition_id', NULL)->where('expired_stnk', '<', $expired)->get();

    $job_order_document = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])->where('status_document', '0')->get();

    $invoicePurchase = InvoicePurchase::with('supplier')->where('due_date', '<', $expired7D)->where('rest_payment', '<', '0')->get();
    $invoiceCustomer = InvoiceCostumer::with('costumer')->where('due_date', '<', $expired7D)->where('rest_payment', '<', '0')->get();
    $invoiceLDO = InvoiceLdo::with('anotherexpedition')->where('due_date', '<', $expired7D)->where('rest_payment', '<', '0')->get();

    $driver_count = Driver::where('another_expedition_id', NULL)->where('status', 'active')->count();
    $transport_count = Transport::where('another_expedition_id', NULL)->count();
    $joborder_count = JobOrder::count();
    $sparepart_count = Sparepart::count();

    return view('backend.dashboard', compact('config', 'page_breadcrumbs', 'expired_sim', 'expired_kir', 'expired_stnk', 'job_order_document', 'driver_count', 'transport_count', 'joborder_count', 'invoiceLDO', 'invoicePurchase', 'sparepart_count', 'invoiceCustomer'));
  }
}
