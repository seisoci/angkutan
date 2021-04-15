<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\JobOrder;
use App\Models\OperationalExpense;
use App\Models\Prefix;
use App\Models\RoadMoney;
use App\Models\Setting;
use App\Models\Transport;
use App\Models\TypeCapacity;
use Carbon\Carbon;
use DataTables;
use DB;
use Illuminate\Http\Request;
use Validator;

class JobOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $config['page_title']       = "Job Order";
      $config['page_description'] = "Daftar List Job Order";
      $page_breadcrumbs = [
        ['page' => '#','title' => "List Job Order"],
      ];

      $another_expedition_id = $request->another_expedition_id;
      $driver_id    = $request->driver_id;
      $transport_id = $request->transport_id;
      $costumer_id  = $request->costumer_id;
      $cargo_id     = $request->cargo_id;
      $route_from   = $request->route_from;
      $route_to     = $request->route_to;
      $date_begin   = $request->date_begin;
      $date_end     = $request->date_end;
      $status_cargo = $request->status_cargo;
      if ($request->ajax()) {
        $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,name', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name'])
        ->when($another_expedition_id, function ($query, $another_expedition_id) {
          return $query->where('another_expedition_id', $another_expedition_id);
        })
        ->when($driver_id, function ($query, $driver_id) {
          return $query->where('driver_id', $driver_id);
        })
        ->when($transport_id, function ($query, $transport_id) {
          return $query->where('transport_id', $transport_id);
        })
        ->when($costumer_id, function ($query, $costumer_id) {
          return $query->where('costumer_id', $costumer_id);
        })
        ->when($cargo_id, function ($query, $cargo_id) {
          return $query->where('cargo_id', $cargo_id);
        })
        ->when($route_from, function ($query, $route_from) {
          return $query->where('route_from', $route_from);
        })
        ->when($route_to, function ($query, $route_to) {
          return $query->where('route_to', $route_to);
        })
        ->when($date_begin, function ($query, $date_begin) {
          return $query->where('date_begin', $date_begin);
        })
        ->when($date_end, function ($query, $date_end) {
          return $query->where('date_end', $date_end);
        })
        ->when($status_cargo, function ($query, $status_cargo) {
          return $query->where('status_cargo', $status_cargo);
        })
        ;
        return DataTables::of($data)
          ->addIndexColumn()
          ->addColumn('action', function($row){
              $actionBtn = '
              <a href="joborders/'.$row->id.'" class="btn btn-info btn-sm">Show Detail</a>
              <a href="#" data-toggle="modal" data-target="#modalEdit" data-id="'. $row->id.'" data-status_cargo="'.$row->status_cargo.'" data-date_end="'.$row->date_end.'" class="edit btn btn-warning btn-sm">Edit</a>';
              return $actionBtn;
          })
          ->make(true);
      }
      return view('backend.operational.joborders.index', compact('config', 'page_breadcrumbs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $config['page_title'] = "Create Job Order";
      $page_breadcrumbs = [
        ['page' => '/backend/driver','title' => "List Job Order"],
        ['page' => '#','title' => "Create Job Order"],
      ];
      $sparepart = Setting::where('name', 'potongan sparepart')->first();
      $gaji = Setting::where('name', 'gaji supir')->first();
      return view('backend.operational.joborders.create', compact('config', 'page_breadcrumbs','sparepart', 'gaji'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $jo_date    = Carbon::parse()->timezone('Asia/Jakarta')->format('Ymd');
      $invoice_db = JobOrder::select(DB::raw('MAX(SUBSTRING_INDEX(num_bill, "-", -1)+1) AS `num`'))->first();
      $jo_num     = $invoice_db['num'] != NULL ? $invoice_db['num'] : 1;
      $type_capacity = TypeCapacity::findOrFail($request->type_capacity);
      $qsparepart = Setting::where('name', 'potongan sparepart')->first();
      $qsalary    = Setting::where('name', 'gaji supir')->first();
      $prefix     = Prefix::findOrFail($request->prefix);
      $data       = new JobOrder();
      if($request->type == 'self'){
        //CALCULATE
        $basicPrice = $request->basic_price;
        $payload    = $request->payload ?? 1;
        $roadMoney  = $request->road_money;
        $sumPayload = $basicPrice * $payload;
        $totalGross = $sumPayload - $roadMoney;
        $pecentSparePart = $qsparepart->value / 100;
        $pecentSalary = $qsalary->value / 100;
        $sparepart  = $totalGross * $pecentSparePart;
        $salary     = ($totalGross - $sparepart) * $pecentSalary;
        $totalNetto = $totalGross - $sparepart - $salary;
        //MODEL DB
        $data->date_begin             = $request->date_begin;
        $data->type                   = $request->type;
        $data->num_bill               = $jo_date ."-". $jo_num;
        $data->prefix                 = $prefix->name;
        $data->another_expedition_id  = $request->another_expedition_id ?? NULL;
        $data->driver_id              = $request->driver_id;
        $data->transport_id           = $request->transport_id;
        $data->costumer_id            = $request->costumer_id;
        $data->cargo_id               = $request->cargo_id;
        $data->route_from             = $request->route_from;
        $data->route_to               = $request->route_to;
        $data->type_capacity          = $type_capacity->name;
        $data->type_payload           = $request->type_payload;
        $data->payload                = $request->payload ?? 1;
        $data->basic_price            = $request->basic_price;
        $data->road_money             = $request->road_money;
        $data->cut_sparepart_percent  = $qsparepart->value;
        // $data->cut_sparepart          = $sparepart;
        // $data->salary                 = $salary;
        $data->salary_percent         = $qsalary->value;
        // $data->grandtotalgross        = $totalGross;
        // $data->grandtotalnetto        = $totalNetto;
        $data->invoice_bill           = $sumPayload;
        if($data->save()){
          $response = response()->json([
            'status'    => 'success',
            'message'   => 'Data has been saved',
            'redirect'  => '/backend/joborders'
          ]);
        }
      }elseif($request->type == 'ldo'){
        //CALCULATE
        $basicPrice = $request->basic_price;
        $basicPriceLDO = $request->basic_price_ldo;
        $payload    = $request->payload ?? 1;
        $roadMoney  = $request->road_money;
        $sumPayload = $basicPrice * $payload;
        $sumPayloadLDO = $basicPriceLDO * $payload;
        $totalNettoLDO = $sumPayloadLDO - $roadMoney;
        $totalNetto = $sumPayload - $sumPayloadLDO;
        //MODEL DB
        $data->date_begin             = $request->date_begin;
        $data->type                   = $request->type;
        $data->num_bill               = $jo_date ."-". $jo_num;
        $data->prefix                 = $prefix->name;
        $data->another_expedition_id  = $request->another_expedition_id ?? NULL;
        $data->driver_id              = $request->driver_id;
        $data->transport_id           = $request->transport_id;
        $data->costumer_id            = $request->costumer_id;
        $data->cargo_id               = $request->cargo_id;
        $data->route_from             = $request->route_from;
        $data->route_to               = $request->route_to;
        $data->type_capacity          = $type_capacity->name;
        $data->type_payload           = $request->type_payload;
        $data->payload                = $request->payload ?? 1;
        $data->basic_price            = $request->basic_price;
        $data->basic_price_ldo        = $request->basic_price_ldo;
        $data->road_money             = $request->road_money;
        // $data->grandtotalgross        = $sumPayloadLDO;
        // $data->grandtotalnetto        = $totalNetto;
        // $data->grandtotalnettoldo     = $totalNettoLDO;
        $data->invoice_bill           = $sumPayload;
        if($data->save()){
          $response = response()->json([
            'status'    => 'success',
            'message'   => 'Data has been saved',
            'redirect'  => '/backend/joborders'
          ]);
        }
      }
      return $response;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\JobOrder  $jobOrder
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      $config['page_title'] = "Detail Job Order";
      $page_breadcrumbs = [
        ['page' => '/backend/drivers','title' => "List Job Order"],
        ['page' => '#','title' => "Detail Job Order"],
      ];
      $collection = Setting::all();
      $profile = collect($collection)->mapWithKeys(function ($item) {
        return [$item['name'] => $item['value']];
      });
      $data = JobOrder::with(['anotherexpedition', 'driver', 'costumer', 'cargo', 'transport', 'routefrom', 'routeto', 'operationalexpense.expense'])->findOrFail($id);
      // dd($data->toArray());
      return view('backend.operational.joborders.show', compact('config', 'page_breadcrumbs', 'data', 'profile'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\JobOrder  $jobOrder
     * @return \Illuminate\Http\Response
     */
    public function edit(JobOrder $jobOrder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\JobOrder  $jobOrder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      $validator = Validator::make($request->all(), [
          'status_cargo'    => 'required|string',
        ]);
        // dd($request->all());

        if($validator->passes()){
          $data = JobOrder::find($id);
          $data->update($request->except('_method'));
          $response = response()->json([
            'status'  => 'success',
            'message' => 'Data has been saved',
          ]);
        }else{
          $response = response()->json(['error'=>$validator->errors()->all()]);
        }
        return $response;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\JobOrder  $jobOrder
     * @return \Illuminate\Http\Response
     */
    public function destroy(JobOrder $jobOrder)
    {
        //
    }

    public function select2costumers(Request $request){
      $page = $request->page;
      $resultCount = 10;
      $offset = ($page - 1) * $resultCount;
      $q    = $request->q;
      $data = DB::table('road_money')
          ->rightJoin('costumers', 'costumers.id', '=', 'road_money.costumer_id')
          ->where('costumers.name', 'LIKE', '%'.$q.'%')
          ->orderBy('costumers.name')
          ->skip($offset)
          ->take($resultCount)
          ->selectRaw('costumers.id AS id, costumers.name as text')
          ->groupBy('road_money.costumer_id')
          ->get();

      $count = DB::table('road_money')
          ->rightJoin('costumers', 'costumers.id', '=', 'road_money.costumer_id')
          ->where('costumers.name', 'LIKE', '%'.$q.'%')
          ->groupBy('road_money.costumer_id')
          ->get()
          ->count();

      $endCount = $offset + $resultCount;
      $morePages = $count > $endCount;

      $results = array(
        "results" => $data,
        "pagination" => array(
            "more" => $morePages
        )
      );

      return response()->json($results);
    }

    public function select2routefrom(Request $request){
      $page         = $request->page;
      $resultCount  = 10;
      $offset       = ($page - 1) * $resultCount;
      $costumer_id  = !empty($request->costumer_id) || isset($request->costumer_id) ? $request->costumer_id : 0;
      $q            = !empty($request->q) || isset($request->q) ? $request->q : NULL;
      $data = DB::table('road_money')
          ->rightJoin('routes', 'routes.id', '=', 'road_money.route_from')
          ->where('routes.name', 'LIKE', '%'.$q.'%')
          ->where('road_money.costumer_id', $costumer_id)
          ->orderBy('routes.name')
          ->skip($offset)
          ->take($resultCount)
          ->selectRaw('routes.id AS id, routes.name as text')
          ->groupBy('road_money.route_from')
          ->get();
      // dd($data);
      $count = DB::table('road_money')
          ->rightJoin('routes', 'routes.id', '=', 'road_money.route_from')
          ->where('routes.name', 'LIKE', '%'.$q.'%')
          ->where('road_money.costumer_id', $costumer_id)
          ->groupBy('road_money.route_from')
          ->get()
          ->count();

      $endCount = $offset + $resultCount;
      $morePages = $count > $endCount;

      $results = array(
        "results" => $data,
        "pagination" => array(
            "more" => $morePages
        )
      );

      return response()->json($results);
    }

    public function select2routeto(Request $request){
      $page   = $request->page;
      $resultCount = 10;
      $offset = ($page - 1) * $resultCount;
      $q      = $request->q;
      $costumer_id  = !empty($request->costumer_id) || isset($request->costumer_id) ? $request->costumer_id : 0;
      $route_from  = !empty($request->route_from) || isset($request->route_from) ? $request->route_from : 0;
      $data = DB::table('road_money')
          ->rightJoin('routes', 'routes.id', '=', 'road_money.route_to')
          ->where('routes.name', 'LIKE', '%'.$q.'%')
          ->where('road_money.costumer_id', $costumer_id)
          ->where('road_money.route_from', $route_from)
          ->orderBy('routes.name')
          ->skip($offset)
          ->take($resultCount)
          ->selectRaw('routes.id AS id, routes.name as text')
          ->groupBy('road_money.route_to')
          ->get();

      $count = DB::table('road_money')
          ->rightJoin('routes', 'routes.id', '=', 'road_money.route_to')
          ->where('routes.name', 'LIKE', '%'.$q.'%')
          ->where('road_money.costumer_id', $costumer_id)
          ->where('road_money.route_from', $route_from)
          ->groupBy('road_money.route_to')
          ->get()
          ->count();

      $endCount = $offset + $resultCount;
      $morePages = $count > $endCount;

      $results = array(
        "results" => $data,
        "pagination" => array(
            "more" => $morePages
        )
      );

      return response()->json($results);
    }

    public function select2cargos(Request $request){
      $page         = $request->page;
      $resultCount = 10;
      $offset   = ($page - 1) * $resultCount;
      $q        = $request->q;
      $costumer_id  = !empty($request->costumer_id) || isset($request->costumer_id) ? $request->costumer_id : 0;
      $route_from  = !empty($request->route_from) || isset($request->route_from) ? $request->route_from : 0;
      $route_to  = !empty($request->route_to) || isset($request->route_to) ? $request->route_to : 0;
      $data = DB::table('road_money')
          ->rightJoin('cargos', 'cargos.id', '=', 'road_money.cargo_id')
          ->where('cargos.name', 'LIKE', '%'.$q.'%')
          ->where('road_money.costumer_id', $costumer_id)
          ->where('road_money.route_from', $route_from)
          ->where('road_money.route_to', $route_to)
          ->orderBy('cargos.name')
          ->skip($offset)
          ->take($resultCount)
          ->selectRaw('cargos.id AS id, cargos.name as text')
          ->groupBy('road_money.cargo_id')
          ->get();

      $count = DB::table('road_money')
          ->rightJoin('cargos', 'cargos.id', '=', 'road_money.cargo_id')
          ->where('cargos.name', 'LIKE', '%'.$q.'%')
          ->where('road_money.costumer_id', $costumer_id)
          ->where('road_money.route_from', $route_from)
          ->where('road_money.route_to', $route_to)
          ->groupBy('road_money.cargo_id')
          ->get()
          ->count();

      $endCount = $offset + $resultCount;
      $morePages = $count > $endCount;

      $results = array(
        "results" => $data,
        "pagination" => array(
            "more" => $morePages
        )
      );

      return response()->json($results);
    }

    public function roadmoney(Request $request){
      $validator = Validator::make($request->all(), [
        'costumer_id'  => 'integer',
        'route_from'   => 'integer',
        'route_to'     => 'integer',
        'cargo_id'     => 'integer',
        'type_capacity_id' => 'integer',
        'transport_id' => 'integer',
      ]);
      if($validator->passes()){
        $transport = Transport::select('type_car')->firstOrFail($request->transport_id);
        $data = RoadMoney::where('costumer_id', $request->costumer_id)
        ->where('route_from', $request->route_from)
        ->where('route_to', $request->route_to)
        ->where('cargo_id', $request->cargo_id)
        ->first()->typecapacities()->where('type_capacity_id', $request->type_capacity_id)->where('type', $request->type)
        ->first();

        $response = response()->json([
          'data' => $data,
          'type' => $transport
        ]);
      }
      return $response;
    }


}
