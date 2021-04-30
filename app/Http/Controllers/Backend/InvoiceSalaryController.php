<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\InvoiceSalary;
use App\Models\JobOrder;
use App\Models\Prefix;
use App\Models\Setting;
use App\Models\Transport;
use Illuminate\Http\Request;
use DataTables;
use DB;
use Validator;

class InvoiceSalaryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $config['page_title']       = "List Invoice Gaji Supir";
      $config['page_description'] = "Daftar List Invoice Gaji Supir";
      $page_breadcrumbs = [
        ['page' => '#','title' => "List Invoice Gaji Supir"],
      ];
      if ($request->ajax()) {
        $data = InvoiceSalary::with(['transport:id,num_pol', 'driver:id,name']);
        return Datatables::of($data)
        ->addIndexColumn()
        ->addColumn('details_url', function(InvoiceSalary $invoiceSalary) {
          return route('backend.invoicesalaries.datatabledetail', $invoiceSalary->id);
        })
        ->addColumn('action', function($row){
          $actionBtn = '
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-eye"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                  <a href="invoicesalaries/'.$row->id.'" class="dropdown-item">Invoice Detail</a>
                </div>
            </div>
          ';
          return $actionBtn;
        })
        ->make(true);

      }
      return view('backend.invoice.invoicesalaries.index', compact('config', 'page_breadcrumbs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
      $config['page_title']       ="Create Invoice Gaji Supir";
      $config['page_description'] = "Create Invoice Gaji Supir";
      $page_breadcrumbs = [
        ['page' => '#','title' => "Create Invoice Gaji Supir"],
      ];
      $driver_id    = $request->driver_id;
      $transport_id = $request->transport_id;
      if ($request->ajax()) {
        $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])
        ->where('type', 'self')
        ->where('status_salary', '0')
        ->when($driver_id, function ($query, $driver_id) {
          return $query->where('driver_id', $driver_id);
        })
        ->when($transport_id, function ($query, $transport_id) {
          return $query->where('transport_id', $transport_id);
        });
        return DataTables::of($data)
          ->addIndexColumn()
          ->make(true);
      }
      return view('backend.invoice.invoicesalaries.create', compact('config', 'page_breadcrumbs'));
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
        'job_order_id'      => 'required|array',
        'job_order_id.*'      => 'required|integer',
        'prefix'        => 'required|integer',
        'num_bill'      => 'required|integer',
        'driver_id'     => 'required|integer',
        'transport_id'  => 'required|integer',
      ]);

      if($validator->passes()){
        try {
          DB::beginTransaction();
          $prefix = Prefix::findOrFail($request->prefix);
          $data = InvoiceSalary::create([
            'prefix'       => $prefix->name,
            'num_bill'     => $request->input('num_bill'),
            'driver_id'    => $request->input('driver_id'),
            'transport_id' => $request->input('transport_id'),
            'grandtotal'   => $request->input('grand_total'),
            'description'  => $request->input('description'),
            'memo'         => $request->input('memo'),
          ]);
          foreach($request->job_order_id as $item):
            JobOrder::where('id', $item)->update(['invoice_salary_id' => $data->id, 'status_salary' => '1']);
          endforeach;
          DB::commit();
          $response = response()->json([
            'status'  => 'success',
            'message' => 'Data has been saved',
            'redirect' => '/backend/invoicesalaries',
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
     * @param  \App\Models\InvoiceSalary  $invoiceSalary
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      $config['page_title'] = "Invoice Gaji Supir";
      $config['print_url']  = "/backend/invoicesalaries/$id/print";
      $page_breadcrumbs = [
        ['page' => '/backend/invoicesalaries','title' => "List Invoice Gaji"],
        ['page' => '#','title' => "Invoice Gaji Supir"],
      ];
      $collection = Setting::all();
      $profile = collect($collection)->mapWithKeys(function ($item) {
        return [$item['name'] => $item['value']];
      });
      $data = InvoiceSalary::with(['joborders.costumer:id,name', 'joborders.routefrom:id,name', 'joborders.routeto:id,name', 'transport:id,num_pol', 'driver:id,name'])->findOrFail($id);

      return view('backend.invoice.invoicesalaries.show', compact('config', 'page_breadcrumbs', 'data', 'profile'));
    }

    public function print($id)
    {
      $config['page_title'] = "Invoice Gaji Supir";
      $config['print_url']  = "/backend/invoicesalaries/$id/print";
      $page_breadcrumbs = [
        ['page' => '/backend/invoicesalaries','title' => "List Invoice Gaji"],
        ['page' => '#','title' => "Invoice Gaji Supir"],
      ];
      $collection = Setting::all();
      $profile = collect($collection)->mapWithKeys(function ($item) {
        return [$item['name'] => $item['value']];
      });
      $data = InvoiceSalary::with(['joborders.costumer:id,name', 'joborders.routefrom:id,name', 'joborders.routeto:id,name', 'transport:id,num_pol', 'driver:id,name'])->findOrFail($id);

      return view('backend.invoice.invoicesalaries.print', compact('config', 'page_breadcrumbs', 'data', 'profile'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\InvoiceSalary  $invoiceSalary
     * @return \Illuminate\Http\Response
     */
    public function edit(InvoiceSalary $invoiceSalary)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\InvoiceSalary  $invoiceSalary
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, InvoiceSalary $invoiceSalary)
    {
        //
    }

    public function findbypk(Request $request){
      $data = json_decode($request->data);
      $response = NULL;
      if($request->data){
        $result = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])->whereIn('id', $data)->get();

        $response = response()->json([
          'data'    => $result,
        ]);
      }
      return $response;
    }

    public function datatabledetail($id)
    {
        $data = JobOrder::where('invoice_salary_id', $id);

        return Datatables::of($data)->make(true);
    }

}
