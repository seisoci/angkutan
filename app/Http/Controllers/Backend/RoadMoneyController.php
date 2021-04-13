<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use App\Models\Costumer;
use App\Models\RoadMoney;
use App\Models\Transport;
use App\Models\TypeCapacity;
use Illuminate\Http\Request;
use DataTables;
use DB;
use Illuminate\Support\Facades\Validator;
use PDF;
use Route;

class RoadMoneyController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request)
  {
    //TODO: HAPUS FIELD INVOICE (TIDAK TERPAKAI)
    $config['page_title']       = "Uang Jalan";
    $config['page_description'] = "Daftar List Uang Jalan";
    $page_breadcrumbs = [
      ['page' => '#','title' => "List Uang Jalan"],
    ];
    if ($request->ajax()) {
      $costumer_id = $request->costumer_id;
      $data = RoadMoney::with(['costumers', 'routefrom', 'routeto', 'cargo', 'typecapacities'])
      ->when($costumer_id, function ($query, $id) {
        return $query->where('costumer_id', $id);
      })
      ->select('road_money.*');
      return Datatables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function($row){
            $actionBtn = '
            <a href="roadmonies/'.$row->id.'/edit" class="edit btn btn-warning btn-sm">Edit</a>
            <a href="#" data-toggle="modal" data-target="#modalDelete" data-id="'. $row->id.'" class="delete btn btn-danger btn-sm">Delete</a>';
            return $actionBtn;
        })->make(true);
    }
    return view('backend.masteroperational.roadmonies.index', compact('config', 'page_breadcrumbs'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    $config['page_title'] = "Create Uang Jalan";
    $page_breadcrumbs = [
      ['page' => '/backend/roadmonies','title' => "List Uang Jalan"],
      ['page' => '#','title' => "Create Uang Jalan"],
    ];
    return view('backend.masteroperational.roadmonies.create', compact('config', 'page_breadcrumbs'));
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
      'costumer_id'   => 'required|integer',
      'route_from'    => 'string|nullable',
      'route_to'      => 'string|nullable',
      'cargo_id'      => 'string|nullable',
      'amount'        => 'integer|nullable',
    ]);

    if($validator->passes()){
      $data = RoadMoney::create([
        'costumer_id'   => $request->input('costumer_id'),
        'route_from'    => $request->input('route_from'),
        'route_to'      => $request->input('route_to'),
        'cargo_id'      => $request->input('cargo_id'),
        'amount'        => $request->input('amount')
      ]);
      $type_capacities = TypeCapacity::all();
      $data->typecapacities()->attach($type_capacities);

      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been saved',
        'redirect' => '/backend/roadmonies'
      ]);

    }else{
      $response = response()->json(['error'=>$validator->errors()->all()]);
    }
    return $response;
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  \App\Models\RoadMoney  $roadMoney
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    $config['page_title'] = "Edit Uang Jalan";
    $page_breadcrumbs = [
      ['page' => '/backend/roadmonies','title' => "List Uang Jalan"],
      ['page' => '#','title' => "Edit Uang Jalan"],
    ];

    $data = RoadMoney::with(['costumers', 'routefrom', 'routeto', 'cargo'])->findOrFail($id);
    return view('backend.masteroperational.roadmonies.edit',compact('config', 'page_breadcrumbs', 'data'));
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \App\Models\RoadMoney  $roadMoney
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'costumer_id'   => 'required|integer',
      'route_from'    => 'string|nullable',
      'route_to'      => 'string|nullable',
      'cargo_id'      => 'string|nullable',
      'road_engkel'   => 'integer|nullable',
      'road_tronton'  => 'integer|nullable',
      'amount'        => 'integer|nullable',
    ]);

    if($validator->passes()){
      $data = RoadMoney::find($id);
      $data->update([
        'costumer_id'   => $request->input('costumer_id'),
        'route_from'    => $request->input('route_from'),
        'route_to'      => $request->input('route_to'),
        'cargo_id'      => $request->input('cargo_id'),
        'road_engkel'   => $request->input('road_engkel'),
        'road_tronton'  => $request->input('road_tronton'),
        'amount'        => $request->input('amount')
      ]);

      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been updated',
        'redirect' => '/backend/roadmonies'
      ]);

    }else{
      $response = response()->json(['error'=>$validator->errors()->all()]);
    }
    return $response;
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  \App\Models\RoadMoney  $roadMoney
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $response = response()->json([
        'status' => 'error',
        'message' => 'Data cannot be deleted',
    ]);
    $data = RoadMoney::find($id);

    if($data->delete()){
      $data->typecapacities()->detach();
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been deleted',
      ]);
    }
    return $response;
  }

  public function typecapacities(Request $request){
    $validator = Validator::make($request->all(), [
      'type_capacity_id'  => 'integer',
      'road_money_id'     => 'integer',
    ]);

    if($validator->passes()){
      $data = RoadMoney::firstOrFail('id', $request->road_money_id)->typecapacities()->where('type_capacity_id', $request->type_capacity_id)->where('type', $request->type)
      ->first();

      $response = response()->json([
        'data' => $data,
      ]);
    }
    return $response;
  }

  public function updatetypecapacities(Request $request, $id){
    $validator = Validator::make($request->all(), [
      'type_capacity_id'  => 'required|integer',
      'road_engkel'       => 'integer|nullable',
      'road_tronton'      => 'integer|nullable',
      'type'              => 'in:fix,calculate',
    ]);

    if($validator->passes()){
      $data = RoadMoney::firstOrFail('id', $request->road_money_id);
      if($data->typecapacities()->where('type_capacity_id', $request->type_capacity_id)->wherePivot('type', $request->type)->count() >= 1){
        $data->typecapacities()->where('type_capacity_id', $request->type_capacity_id)->wherePivot('type', $request->type)->updateExistingPivot($request->type_capacity_id, $request->except(['type_capacity_id', '_method']));
      }else{
        $data->typecapacities()->attach($request->type_capacity_id, $request->except(['type_capacity_id','_method']));
      }
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been updated',
      ]);
    }else{
      $response = response()->json(['error'=>$validator->errors()->all()]);
    }
    return $response;
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
    $costumer_id  = !empty($request->costumer_id) || isset($request->costumer_id) ? $request->costumer_id : NULL;
    $q    = $request->q;
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
    $costumer_id  = !empty($request->costumer_id) || isset($request->costumer_id) ? $request->costumer_id : NULL;
    $route_from  = !empty($request->route_from) || isset($request->route_from) ? $request->route_from : NULL;
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
    $costumer_id  = !empty($request->costumer_id) || isset($request->costumer_id) ? $request->costumer_id : NULL;
    $route_from  = !empty($request->route_from) || isset($request->route_from) ? $request->route_from : NULL;
    $route_to  = !empty($request->route_to) || isset($request->route_to) ? $request->route_to : NULL;
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
