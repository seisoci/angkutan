<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\InvoiceLdo;
use App\Models\JobOrder;
use App\Models\Prefix;
use App\Models\Setting;
use DataTables;
use DB;
use Illuminate\Http\Request;
use Validator;

class InvoiceLdoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $config['page_title']       = "List Invoice LDO";
      $config['page_description'] = "Daftar List Invoice LDO";
      $page_breadcrumbs = [
        ['page' => '#','title' => "List Invoice LDO"],
      ];
      if ($request->ajax()) {
        $data = InvoiceLdo::with(['anotherexpedition:id,name']);
        return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('details_url', function(InvoiceLdo $invoiceLdo) {
          return route('backend.invoiceldo.datatabledetail', $invoiceLdo->id);
        })
        ->addColumn('action', function($row){
          $actionBtn = '
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-eye"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                  <a href="invoiceldo/'.$row->id.'" class="dropdown-item">Invoice Detail</a>
                </div>
            </div>
          ';
          return $actionBtn;
        })
        ->make(true);

      }
      return view('backend.invoice.invoiceldo.index', compact('config', 'page_breadcrumbs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
      $config['page_title']       ="Create Invoice LDO";
      $config['page_description'] = "Create Invoice LDO";
      $page_breadcrumbs = [
        ['page' => '#','title' => "Create Invoice LDO"],
      ];
      $another_expedition_id  = $request->another_expedition;
      $costumer_id   = $request->costumer_id;
      $route_from   = $request->route_from;
      $route_to     = $request->route_to;
      $route_from   = $request->route_from;
      $cargo_id     = $request->cargo_id;
      if ($request->ajax()) {
        $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])->withSum('operationalexpense','amount')
        ->where('status_payment', '0')
        ->where('status_cargo', 'selesai')
        ->where('another_expedition_id', '!=', 'NULL')
        ->when($another_expedition_id, function ($query, $another_expedition_id) {
          return $query->where('another_expedition_id', $another_expedition_id);
        })
        ->when($costumer_id, function ($query, $costumer_id) {
          return $query->where('costumer_id', $costumer_id);
        })
        ->when($route_from, function ($query, $route_from) {
          return $query->where('route_from', $route_from);
        })
        ->when($route_to, function ($query, $route_to) {
          return $query->where('route_to', $route_to);
        })
        ->when($cargo_id, function ($query, $cargo_id) {
          return $query->where('cargo_id', $cargo_id);
        });
        return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
      }
      return view('backend.invoice.invoiceldo.create', compact('config', 'page_breadcrumbs'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $validator = Validator::make($request->all(), [
        'job_order_id'  => 'required|array',
        'job_order_id.*'=> 'required|integer',
        'prefix'        => 'required|integer',
        'num_bill'      => 'required|integer',
        'another_expedition_id' => 'required|integer',
      ]);

      if($validator->passes()){
        try {
          DB::beginTransaction();
          $prefix = Prefix::findOrFail($request->prefix);
          $data = InvoiceLdo::create([
            'prefix'       => $prefix->name,
            'num_bill'     => $request->input('num_bill'),
            'another_expedition_id'   => $request->input('another_expedition_id'),
            'grandtotal'   => $request->input('grand_total'),
            'description'  => $request->input('description'),
            'memo'         => $request->input('memo'),
          ]);
          foreach($request->job_order_id as $item):
            JobOrder::where('id', $item)->update(['invoice_ldo_id' => $data->id, 'status_payment_ldo' => '1']);
          endforeach;
          DB::commit();
          $response = response()->json([
            'status'  => 'success',
            'message' => 'Data has been saved',
            'redirect' => '/backend/invoiceldo',
          ]);
        } catch (\Throwable $throw) {
          DB::rollBack();
          $response = $throw;
        }
      }else{
        $response = response()->json(['error'=>$validator->errors()->all()]);
      }
      return $response;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InvoiceLdo  $invoiceLdo
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      $config['page_title'] = "Invoice Tagihan LDO";
      $config['print_url']  = "/backend/invoiceldo/$id/print";
      $page_breadcrumbs = [
        ['page' => '/backend/invoiceldo','title' => "List Invoice Gaji"],
        ['page' => '#','title' => "Invoice Tagihan LDO"],
      ];
      $collection = Setting::all();
      $profile = collect($collection)->mapWithKeys(function ($item) {
        return [$item['name'] => $item['value']];
      });
      $data = InvoiceLdo::with(['joborders.costumer:id,name', 'joborders.routefrom:id,name', 'joborders.routeto:id,name', 'anotherexpedition:id,name'])->findOrFail($id);

      return view('backend.invoice.invoiceldo.show', compact('config', 'page_breadcrumbs', 'data', 'profile'));
    }

    public function print($id)
    {
      $config['page_title'] = "Invoice Tagihan LDO";
      $page_breadcrumbs = [
        ['page' => '/backend/invoiceldo','title' => "List Invoice Gaji"],
        ['page' => '#','title' => "Invoice Tagihan LDO"],
      ];
      $collection = Setting::all();
      $profile = collect($collection)->mapWithKeys(function ($item) {
        return [$item['name'] => $item['value']];
      });
      $data = InvoiceLdo::with(['joborders.costumer:id,name', 'joborders.routefrom:id,name', 'joborders.routeto:id,name', 'anotherexpedition:id,name'])->findOrFail($id);

      return view('backend.invoice.invoiceldo.print', compact('config', 'page_breadcrumbs', 'data', 'profile'));
    }

    public function datatabledetail($id)
    {
        $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])->withSum('operationalexpense','amount')->where('invoice_ldo_id', $id);
        return Datatables::of($data)->make(true);
    }
}
